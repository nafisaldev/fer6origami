<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\GeneratesUniqueSlug;
use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Genre;
use App\Models\Studio;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AnimeController extends Controller
{
    use GeneratesUniqueSlug;

    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $status = $request->query('status');
        $genreSlug = $request->query('genre');
        $sort = $request->query('sort', 'latest');

        $query = Anime::query()
            ->with(['genres', 'studioModel'])
            ->withCount('episodes')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($nested) use ($search) {
                    $nested->where('title', 'like', '%' . $search . '%')
                        ->orWhere('studio', 'like', '%' . $search . '%')
                        ->orWhere('synopsis', 'like', '%' . $search . '%');
                });
            })
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($genreSlug, fn ($q) => $q->whereHas('genres', fn ($g) => $g->where('slug', $genreSlug)));

        match ($sort) {
            'oldest' => $query->oldest(),
            'score' => $query->orderByDesc('score'),
            'views' => $query->orderByDesc('views'),
            'title' => $query->orderBy('title'),
            default => $query->latest(),
        };

        $animes = $query->paginate(15)->withQueryString();

        return view('admin.animes.index', [
            'animes' => $animes,
            'search' => $search,
            'status' => $status,
            'selectedGenre' => $genreSlug,
            'sort' => $sort,
            'genres' => Genre::orderBy('name')->get(),
            'statuses' => Anime::STATUSES,
        ]);
    }

    public function show(Anime $anime): View
    {
        $anime->load(['genres', 'studioModel', 'episodes' => fn ($q) => $q->orderBy('episode_number')]);

        return view('admin.animes.show', [
            'anime' => $anime,
        ]);
    }

    public function create(): View
    {
        return view('admin.animes.create', [
            'anime' => new Anime(),
            'genres' => Genre::query()->orderBy('name')->get(),
            'studios' => Studio::query()->orderBy('name')->get(),
            'statuses' => Anime::STATUSES,
            'ratings' => Anime::RATINGS,
            'days' => Anime::DAYS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['slug'] = $this->generateUniqueSlug($data['title'], Anime::class);
        $data['poster_path'] = $this->storeImage($request, 'poster_upload', 'animes/posters', $data['poster_path'] ?? null);
        $data['banner_path'] = $this->storeImage($request, 'banner_upload', 'animes/banners', $data['banner_path'] ?? null);
        $data['is_trending'] = $request->boolean('is_trending');
        $data['is_popular'] = $request->boolean('is_popular');
        $data['is_featured'] = $request->boolean('is_featured');

        if (!empty($data['studio_id'])) {
            $studio = Studio::find($data['studio_id']);
            if ($studio) {
                $data['studio'] = $studio->name;
            }
        }

        $anime = Anime::create($data);
        $anime->genres()->sync($request->input('genre_ids', []));

        ActivityLogger::log('Anime', 'created', "Menambahkan anime: {$anime->title}", [
            'anime_id' => $anime->id,
            'title' => $anime->title,
        ]);

        return redirect()->route('admin.animes.index')->with('success', 'Anime berhasil ditambahkan.');
    }

    public function edit(Anime $anime): View
    {
        $anime->load('genres');

        return view('admin.animes.edit', [
            'anime' => $anime,
            'genres' => Genre::query()->orderBy('name')->get(),
            'studios' => Studio::query()->orderBy('name')->get(),
            'statuses' => Anime::STATUSES,
            'ratings' => Anime::RATINGS,
            'days' => Anime::DAYS,
        ]);
    }

    public function update(Request $request, Anime $anime): RedirectResponse
    {
        $data = $this->validatedData($request, $anime->id);
        $data['slug'] = $this->generateUniqueSlug($data['title'], Anime::class, $anime);
        $data['poster_path'] = $this->storeImage($request, 'poster_upload', 'animes/posters', $data['poster_path'] ?? null, $anime->poster_path);
        $data['banner_path'] = $this->storeImage($request, 'banner_upload', 'animes/banners', $data['banner_path'] ?? null, $anime->banner_path);
        $data['is_trending'] = $request->boolean('is_trending');
        $data['is_popular'] = $request->boolean('is_popular');
        $data['is_featured'] = $request->boolean('is_featured');

        if (!empty($data['studio_id'])) {
            $studio = Studio::find($data['studio_id']);
            if ($studio) {
                $data['studio'] = $studio->name;
            }
        }

        $oldData = $anime->only(['title', 'status', 'score', 'is_featured', 'is_trending', 'is_popular']);
        $anime->update($data);
        $anime->genres()->sync($request->input('genre_ids', []));

        ActivityLogger::log('Anime', 'updated', "Memperbarui anime: {$anime->title}", [
            'anime_id' => $anime->id,
            'old' => $oldData,
            'new' => $anime->only(['title', 'status', 'score', 'is_featured', 'is_trending', 'is_popular']),
        ]);

        return redirect()->route('admin.animes.index')->with('success', 'Anime berhasil diperbarui.');
    }

    public function destroy(Anime $anime): RedirectResponse
    {
        $title = $anime->title;
        $id = $anime->id;
        $slug = $anime->slug;

        // 1. Blacklist anime so it never appears anywhere on the user frontend (neither from DB nor API)
        \App\Models\DeletedAnime::blacklistAnime($title, $slug, Auth::id());

        // 2. Delete media files
        $this->deleteStoredFile($anime->poster_path);
        $this->deleteStoredFile($anime->banner_path);

        // 3. Delete related watchlists and scraper snapshots if any
        \App\Models\Watchlist::where('anime_api_id', $slug)->orWhere('anime_title', $title)->delete();
        if (\Illuminate\Support\Facades\Schema::hasTable('scraper_snapshots')) {
            \App\Models\ScraperSnapshot::where('snapshot_key', 'like', "%{$slug}%")
                ->orWhere('path', 'like', "%{$slug}%")
                ->delete();
        }

        // 4. Delete anime and cascaded records
        $anime->delete();

        cache()->flush();

        ActivityLogger::log('Anime', 'deleted', "Menghapus anime: {$title}", [
            'anime_id' => $id,
            'title' => $title,
            'slug' => $slug,
        ]);

        return redirect()->route('admin.animes.index')->with('success', 'Anime berhasil dihapus dan dibersihkan dari tampilan user.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $action = $request->input('bulk_action');
        $selectedIds = $request->input('selected_ids', []);

        if (empty($selectedIds) || !is_array($selectedIds)) {
            return back()->withErrors(['bulk' => 'Pilih setidaknya satu anime untuk aksi massal.']);
        }

        $animes = Anime::whereIn('id', $selectedIds)->get();

        switch ($action) {
            case 'delete':
                foreach ($animes as $anime) {
                    \App\Models\DeletedAnime::blacklistAnime($anime->title, $anime->slug, Auth::id());
                    $this->deleteStoredFile($anime->poster_path);
                    $this->deleteStoredFile($anime->banner_path);
                    \App\Models\Watchlist::where('anime_api_id', $anime->slug)->orWhere('anime_title', $anime->title)->delete();
                    if (\Illuminate\Support\Facades\Schema::hasTable('scraper_snapshots')) {
                        \App\Models\ScraperSnapshot::where('snapshot_key', 'like', "%{$anime->slug}%")
                            ->orWhere('path', 'like', "%{$anime->slug}%")
                            ->delete();
                    }
                    $anime->delete();
                }
                cache()->flush();
                ActivityLogger::log('Anime', 'bulk_deleted', "Menghapus massal " . count($selectedIds) . " anime.", ['ids' => $selectedIds]);
                return back()->with('success', count($selectedIds) . ' anime berhasil dihapus dan dibersihkan dari tampilan user.');

            case 'status_ongoing':
                Anime::whereIn('id', $selectedIds)->update(['status' => 'ongoing']);
                ActivityLogger::log('Anime', 'bulk_updated', "Mengubah status " . count($selectedIds) . " anime ke Ongoing.", ['ids' => $selectedIds]);
                return back()->with('success', 'Status ' . count($selectedIds) . ' anime diubah ke Ongoing.');

            case 'status_completed':
                Anime::whereIn('id', $selectedIds)->update(['status' => 'completed']);
                ActivityLogger::log('Anime', 'bulk_updated', "Mengubah status " . count($selectedIds) . " anime ke Completed.", ['ids' => $selectedIds]);
                return back()->with('success', 'Status ' . count($selectedIds) . ' anime diubah ke Completed.');

            case 'feature':
                Anime::whereIn('id', $selectedIds)->update(['is_featured' => true]);
                ActivityLogger::log('Anime', 'bulk_updated', "Menjadikan " . count($selectedIds) . " anime sebagai Featured.", ['ids' => $selectedIds]);
                return back()->with('success', count($selectedIds) . ' anime dijadikan Featured di homepage.');

            case 'unfeature':
                Anime::whereIn('id', $selectedIds)->update(['is_featured' => false]);
                ActivityLogger::log('Anime', 'bulk_updated', "Menghapus status Featured " . count($selectedIds) . " anime.", ['ids' => $selectedIds]);
                return back()->with('success', 'Status Featured ' . count($selectedIds) . ' anime telah dinonaktifkan.');

            default:
                return back()->withErrors(['bulk' => 'Aksi massal tidak valid.']);
        }
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'synopsis' => ['required', 'string'],
            'studio' => ['nullable', 'string', 'max:255'],
            'studio_id' => ['nullable', 'integer', 'exists:studios,id'],
            'year' => ['nullable', 'integer', 'min:1970', 'max:' . (date('Y') + 2)],
            'status' => ['required', 'in:' . implode(',', array_keys(Anime::STATUSES))],
            'release_day' => ['nullable', 'string', 'in:' . implode(',', array_keys(Anime::DAYS))],
            'rating' => ['required', 'in:' . implode(',', array_keys(Anime::RATINGS))],
            'score' => ['required', 'numeric', 'min:0', 'max:10'],
            'type' => ['required', 'string', 'max:50'],
            'views' => ['nullable', 'integer', 'min:0'],
            'poster_path' => ['nullable', 'string', 'max:2048'],
            'banner_path' => ['nullable', 'string', 'max:2048'],
            'poster_upload' => ['nullable', 'image', 'max:3072'],
            'banner_upload' => ['nullable', 'image', 'max:5120'],
            'genre_ids' => ['nullable', 'array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
            'is_trending' => ['nullable', 'boolean'],
            'is_popular' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
        ]);
    }

    private function storeImage(Request $request, string $field, string $directory, ?string $fallback = null, ?string $oldValue = null): ?string
    {
        if (! $request->hasFile($field)) {
            return $fallback;
        }

        $this->deleteStoredFile($oldValue);

        return $request->file($field)->store($directory, 'public');
    }

    private function deleteStoredFile(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'http') && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
