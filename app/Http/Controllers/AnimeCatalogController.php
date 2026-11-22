<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Comment;
use App\Models\Review;
use App\Services\OtakudesuApiService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use Illuminate\View\View;
use Throwable;

class AnimeCatalogController extends Controller
{
    public function __construct(private readonly OtakudesuApiService $api)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'genre', 'year', 'status', 'rating']);
        $page = max(1, (int) $request->integer('page', 1));

        try {
            $catalog = $this->api->catalog($filters, $page);
        } catch (Throwable) {
            $catalog = new Fluent([
                'items' => collect(),
                'pagination' => new Fluent([
                    'paginator' => new LengthAwarePaginator([], 0, 48, $page, [
                        'path' => route('anime.index'),
                        'query' => $request->query(),
                    ]),
                    'current_page' => $page,
                    'previous_page' => $page > 1 ? $page - 1 : null,
                    'next_page' => null,
                ]),
            ]);
        }

        try {
            $genres = $this->api->getGenres();
        } catch (Throwable) {
            $genres = collect();
        }

        // Merge Local DB Animes created/managed by Admin
        $dbAnimes = Anime::query()
            ->with(['genres', 'studioModel'])
            ->filter($filters)
            ->latest()
            ->get()
            ->map->toCardFluent();

        $mergedItems = $page === 1
            ? $dbAnimes->concat($catalog->items)->unique('id')->values()
            : $catalog->items;

        // Filter out anime explicitly deleted / blacklisted by Admin.
        $blacklisted = \App\Models\DeletedAnime::blacklistedIdentifiers();
        $hiddenCount = 0;

        if (! empty($blacklisted)) {
            $beforeFilter = $mergedItems->count();

            $mergedItems = $mergedItems->reject(function ($item) use ($blacklisted) {
                $id = strtolower(trim((string) ($item->id ?? '')));
                $routeId = strtolower(trim((string) ($item->route_id ?? '')));
                $title = strtolower(trim((string) ($item->title ?? '')));
                $titleSlug = \Illuminate\Support\Str::slug($item->title ?? '');

                return in_array($id, $blacklisted, true)
                    || in_array($routeId, $blacklisted, true)
                    || in_array($title, $blacklisted, true)
                    || in_array($titleSlug, $blacklisted, true);
            })->values();

            $hiddenCount = $beforeFilter - $mergedItems->count();
        }

        // count($blacklisted) counts identifier variants (slug/title/db_ forms), not anime.
        $totalCount = max(0, $catalog->pagination->paginator->total() + $dbAnimes->count() - $hiddenCount);

        return view('anime.index', [
            'animes' => $mergedItems,
            'pagination' => $catalog->pagination,
            'catalogTotal' => $totalCount,
            'selectedFilters' => $filters,
            'genres' => $genres,
            'statuses' => [
                'ongoing' => 'Ongoing',
                'completed' => 'Completed',
            ],
            'ratings' => $this->api->scoreOptions(),
            'years' => $this->api->yearOptions(),
        ]);
    }

    public function show(string $animeId): View
    {
        $decodedId = $this->api->decodeId($animeId);

        // Abort if this anime was deleted by Admin
        if (\App\Models\DeletedAnime::isBlacklisted($animeId) || \App\Models\DeletedAnime::isBlacklisted($decodedId)) {
            abort(404, 'Anime has been deleted or is no longer available.');
        }

        // Check if this anime exists in local database
        $dbId = str_starts_with($decodedId, 'db_') ? substr($decodedId, 3) : (is_numeric($decodedId) ? $decodedId : null);
        $dbAnime = Anime::query()
            ->with(['genres', 'studioModel', 'episodes'])
            ->when($dbId, fn ($q) => $q->where('id', $dbId))
            ->orWhere('slug', $decodedId)
            ->orWhere('slug', $animeId)
            ->first();

        if ($dbAnime) {
            $anime = $dbAnime->toDetailFluent();
            $dbAnime->increment('views');
        } else {
            try {
                $anime = $this->api->animeDetail($decodedId);
            } catch (Throwable) {
                $anime = $this->fallbackAnime($animeId);
            }
        }

        try {
            $relatedAnimes = $this->api->relatedAnime($anime);
        } catch (Throwable) {
            $relatedAnimes = collect();
        }

        $isInWatchlist = auth()->check()
            ? auth()->user()->watchlists()->where('anime_api_id', $anime->id)->exists()
            : false;

        // Fetch published reviews and comments
        $reviews = Review::query()
            ->with('user')
            ->where('status', 'published')
            ->when($dbAnime, fn ($q) => $q->where('anime_id', $dbAnime->id))
            ->latest()
            ->take(10)
            ->get();

        $comments = Comment::query()
            ->with(['user', 'replies.user'])
            ->where('status', 'published')
            ->whereNull('parent_id')
            ->when($dbAnime, fn ($q) => $q->where('anime_id', $dbAnime->id))
            ->latest()
            ->take(20)
            ->get();

        return view('anime.show', [
            'anime' => $anime,
            'dbAnime' => $dbAnime,
            'relatedAnimes' => $relatedAnimes,
            'isInWatchlist' => $isInWatchlist,
            'reviews' => $reviews,
            'comments' => $comments,
        ]);
    }

    private function fallbackAnime(string $animeId): Fluent
    {
        $decodedId = $this->api->decodeId($animeId);

        $title = (string) \Illuminate\Support\Str::of($decodedId)
            ->replace(['-sub-indo', '-subtitle-indonesia', '-'], [' ', ' ', ' '])
            ->trim()
            ->title();

        return new Fluent([
            'id' => $decodedId,
            'route_id' => $this->api->encodeId($decodedId),
            'title' => $title ?: 'Anime',
            'poster_image' => asset('images/poster-placeholder.svg'),
            'banner_image' => asset('images/poster-placeholder.svg'),
            'synopsis' => 'Data anime ini belum berhasil dimuat dari source saat ini. Coba refresh lagi sebentar.',
            'japanese' => null,
            'score' => 0,
            'producer' => null,
            'type' => null,
            'status' => 'unknown',
            'total_episode' => null,
            'duration' => null,
            'release_date' => null,
            'year' => null,
            'studio' => null,
            'views' => 0,
            'genres' => collect(),
            'episodes' => collect(),
        ]);
    }
}
