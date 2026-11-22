<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\GeneratesUniqueSlug;
use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Episode;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EpisodeController extends Controller
{
    use GeneratesUniqueSlug;

    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $animeId = $request->query('anime_id');
        $status = $request->query('status');

        $query = Episode::query()
            ->with('anime')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($nested) use ($search) {
                    $nested->where('title', 'like', '%' . $search . '%')
                        ->orWhereHas('anime', fn ($animeQuery) => $animeQuery->where('title', 'like', '%' . $search . '%'));
                });
            })
            ->when($animeId, fn ($q) => $q->where('anime_id', $animeId))
            ->when($status !== null && $status !== '', fn ($q) => $q->where('is_published', $status === 'published'))
            ->latest('id');

        $episodes = $query->paginate(15)->withQueryString();

        return view('admin.episodes.index', [
            'episodes' => $episodes,
            'search' => $search,
            'selectedAnimeId' => $animeId,
            'selectedStatus' => $status,
            'animes' => Anime::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function show(Episode $episode): View
    {
        $episode->load(['anime', 'comments.user']);

        return view('admin.episodes.show', [
            'episode' => $episode,
        ]);
    }

    public function create(Request $request): View
    {
        $preselectedAnimeId = $request->query('anime_id');
        $nextEpisodeNumber = 1;

        if ($preselectedAnimeId) {
            $lastEp = Episode::where('anime_id', $preselectedAnimeId)->max('episode_number');
            $nextEpisodeNumber = ($lastEp ?: 0) + 1;
        }

        return view('admin.episodes.create', [
            'episode' => new Episode([
                'anime_id' => $preselectedAnimeId,
                'episode_number' => $nextEpisodeNumber,
                'is_published' => true,
            ]),
            'animes' => Anime::query()->orderBy('title')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $anime = Anime::findOrFail($data['anime_id']);

        $data['slug'] = $this->generateUniqueSlug($anime->slug . '-episode-' . $data['episode_number'], Episode::class);
        $data['is_published'] = $request->boolean('is_published', true);
        $data['servers'] = $this->parseServers($request);
        $data['subtitles'] = $this->parseSubtitles($request);
        $data['qualities'] = $this->parseQualities($request);

        $episode = Episode::create($data);

        ActivityLogger::log('Episode', 'created', "Menambahkan Episode {$episode->episode_number} untuk anime {$anime->title}", [
            'episode_id' => $episode->id,
            'anime_id' => $anime->id,
            'title' => $episode->title,
        ]);

        return redirect()->route('admin.episodes.index')->with('success', 'Episode berhasil ditambahkan.');
    }

    public function edit(Episode $episode): View
    {
        return view('admin.episodes.edit', [
            'episode' => $episode,
            'animes' => Anime::query()->orderBy('title')->get(),
        ]);
    }

    public function update(Request $request, Episode $episode): RedirectResponse
    {
        $data = $this->validatedData($request);
        $anime = Anime::findOrFail($data['anime_id']);

        $data['slug'] = $this->generateUniqueSlug($anime->slug . '-episode-' . $data['episode_number'], Episode::class, $episode);
        $data['is_published'] = $request->boolean('is_published');
        $data['servers'] = $this->parseServers($request);
        $data['subtitles'] = $this->parseSubtitles($request);
        $data['qualities'] = $this->parseQualities($request);

        $episode->update($data);

        ActivityLogger::log('Episode', 'updated', "Memperbarui Episode {$episode->episode_number} untuk anime {$anime->title}", [
            'episode_id' => $episode->id,
            'anime_id' => $anime->id,
            'title' => $episode->title,
        ]);

        return redirect()->route('admin.episodes.index')->with('success', 'Episode berhasil diperbarui.');
    }

    public function destroy(Episode $episode): RedirectResponse
    {
        $number = $episode->episode_number;
        $title = $episode->anime->title ?? 'Anime';
        $id = $episode->id;

        $episode->delete();

        ActivityLogger::log('Episode', 'deleted', "Menghapus Episode {$number} dari anime {$title}", [
            'episode_id' => $id,
        ]);

        return redirect()->route('admin.episodes.index')->with('success', 'Episode berhasil dihapus.');
    }

    private function parseServers(Request $request): array
    {
        $names = $request->input('server_names', []);
        $urls = $request->input('server_urls', []);
        $qualities = $request->input('server_qualities', []);
        $servers = [];

        if (is_array($names)) {
            foreach ($names as $i => $name) {
                if (!empty($name) && !empty($urls[$i])) {
                    $servers[] = [
                        'name' => trim((string) $name),
                        'url' => trim((string) $urls[$i]),
                        'quality' => trim((string) ($qualities[$i] ?? '720p')),
                    ];
                }
            }
        }

        return $servers;
    }

    private function parseSubtitles(Request $request): array
    {
        $languages = $request->input('subtitle_languages', []);
        $urls = $request->input('subtitle_urls', []);
        $subtitles = [];

        if (is_array($languages)) {
            foreach ($languages as $i => $lang) {
                if (!empty($lang) && !empty($urls[$i])) {
                    $subtitles[] = [
                        'language' => trim((string) $lang),
                        'url' => trim((string) $urls[$i]),
                    ];
                }
            }
        }

        return $subtitles;
    }

    private function parseQualities(Request $request): array
    {
        $labels = $request->input('quality_labels', []);
        $sizes = $request->input('quality_sizes', []);
        $urls = $request->input('quality_urls', []);
        $qualities = [];

        if (is_array($labels)) {
            foreach ($labels as $i => $label) {
                if (!empty($label) && !empty($urls[$i])) {
                    $qualities[] = [
                        'quality' => trim((string) $label),
                        'size' => trim((string) ($sizes[$i] ?? '')),
                        'url' => trim((string) $urls[$i]),
                    ];
                }
            }
        }

        return $qualities;
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'anime_id' => ['required', 'integer', 'exists:animes,id'],
            'title' => ['required', 'string', 'max:255'],
            'episode_number' => ['required', 'integer', 'min:1'],
            'synopsis' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url', 'max:2048'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'aired_at' => ['nullable', 'date'],
            'is_published' => ['nullable', 'boolean'],
            'minimum_plan' => ['nullable', 'in:free,premium,vip'],
            'early_access_until' => ['nullable', 'date'],
        ]);

        $data['minimum_plan'] = $data['minimum_plan'] ?? null;
        $data['early_access_until'] = $data['early_access_until'] ?? null;

        return $data;
    }
}
