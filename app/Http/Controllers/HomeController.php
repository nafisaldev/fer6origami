<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Announcement;
use App\Models\Banner;
use App\Models\Episode;
use App\Models\Review;
use App\Models\WatchHistory;
use App\Models\WebsiteSetting;
use App\Services\OtakudesuApiService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Fluent;
use Illuminate\View\View;
use Throwable;

class HomeController extends Controller
{
    public function __construct(private readonly OtakudesuApiService $api)
    {
    }

    public function index(): View
    {
        try {
            $home = $this->api->homeData();
        } catch (Throwable) {
            $home = new Fluent([
                'trending' => collect(),
                'latestEpisodes' => collect(),
                'popular' => collect(),
                'ongoing' => collect(),
                'complete' => collect(),
            ]);
        }

        try {
            $catalog = $this->api->catalog([], 1);
        } catch (Throwable) {
            $catalog = new Fluent([
                'items' => collect(),
                'pagination' => new Fluent([
                    'paginator' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 48, 1),
                ]),
            ]);
        }

        try {
            $genres = $this->api->getGenres();
        } catch (Throwable) {
            $genres = collect();
        }

        // 1. Merge Local DB Anime created by Admin
        $dbTrending = Anime::query()
            ->with(['genres', 'studioModel'])
            ->where(fn ($q) => $q->where('is_trending', true)->orWhere('is_featured', true))
            ->latest()
            ->take(8)
            ->get()
            ->map->toCardFluent();

        $mergedTrending = $dbTrending->concat($home->trending)->unique('id')->take(8);

        $dbPopular = Anime::query()
            ->with(['genres', 'studioModel'])
            ->where('is_popular', true)
            ->latest()
            ->take(8)
            ->get()
            ->map->toCardFluent();

        $mergedPopular = $dbPopular->concat($home->popular)->unique('id')->take(8);

        // 2. Merge Local DB Episodes created by Admin
        $dbEpisodes = Episode::query()
            ->with('anime.genres')
            ->where('is_published', true)
            ->whereHas('anime')
            ->latest('aired_at')
            ->latest('id')
            ->take(8)
            ->get()
            ->map(function ($ep) {
                return new Fluent([
                    'title' => $ep->anime->title . ' Ep ' . $ep->episode_number,
                    'episode_number' => $ep->episode_number,
                    'uploaded_on' => $ep->aired_at ? $ep->aired_at->diffForHumans() : 'Just now',
                    'route_id' => 'db_ep_' . $ep->id,
                    'anime_route_id' => $ep->anime->route_id,
                    'anime' => $ep->anime->toCardFluent(),
                    'synopsis' => 'Stream online in high quality on Fer6origami.',
                ]);
            });

        $mergedLatestEpisodes = $dbEpisodes->concat($home->latestEpisodes)->unique('route_id')->take(8);

        // 3. Merge Local Ongoing & Completed Anime
        $dbOngoing = Anime::query()
            ->with(['genres', 'studioModel'])
            ->where('status', 'ongoing')
            ->latest()
            ->take(10)
            ->get()
            ->map->toCardFluent();

        $mergedOngoing = $dbOngoing->concat($home->ongoing)->unique('id')->take(10);

        $dbCompleted = Anime::query()
            ->with(['genres', 'studioModel'])
            ->where('status', 'completed')
            ->latest()
            ->take(10)
            ->get()
            ->map->toCardFluent();

        $mergedComplete = $dbCompleted->concat($home->complete)->unique('id')->take(10);

        // 4. Featured Spotlight Anime from DB
        $dbFeatured = Anime::query()
            ->with(['genres', 'studioModel'])
            ->where('is_featured', true)
            ->latest()
            ->take(6)
            ->get()
            ->map->toCardFluent();

        $heroSlides = $dbFeatured->isNotEmpty()
            ? $dbFeatured
            : ($this->banners()->isNotEmpty()
                ? $this->banners()
                : $mergedTrending->concat($home->popular)->unique('id')->take(5)->values());

        // 5. Filter out ANY anime deleted / blacklisted by Admin
        $blacklisted = \App\Models\DeletedAnime::blacklistedIdentifiers();
        $filterDeleted = function ($collection) use ($blacklisted) {
            if (empty($blacklisted)) {
                return $collection;
            }
            return $collection->reject(function ($item) use ($blacklisted) {
                $id = strtolower(trim((string) ($item->id ?? '')));
                $routeId = strtolower(trim((string) ($item->route_id ?? '')));
                $title = strtolower(trim((string) ($item->title ?? '')));
                $titleSlug = \Illuminate\Support\Str::slug($item->title ?? '');

                return in_array($id, $blacklisted, true)
                    || in_array($routeId, $blacklisted, true)
                    || in_array($title, $blacklisted, true)
                    || in_array($titleSlug, $blacklisted, true);
            })->values();
        };

        $finalTrending = $filterDeleted($mergedTrending)->take(8);
        $finalLatestEpisodes = $filterDeleted($mergedLatestEpisodes)->take(8);
        $finalPopular = $filterDeleted($mergedPopular)->take(6);
        $finalOngoing = $filterDeleted($mergedOngoing)->take(10);
        $finalComplete = $filterDeleted($mergedComplete)->take(10);
        $finalHighlights = $filterDeleted($dbTrending->concat($catalog->items)->unique('id'))->take(12);
        $finalHeroSlides = $filterDeleted($heroSlides);

        // 6. Website Settings for Sections from Admin Appearance
        $sectionSettings = [
            'hero_style' => WebsiteSetting::get('hero_style', 'carousel'),
            'section_trending' => WebsiteSetting::get('show_trending', WebsiteSetting::get('section_trending', '1')) === '1',
            'section_latest' => WebsiteSetting::get('section_latest', '1') === '1',
            'show_ongoing' => WebsiteSetting::get('show_ongoing', '1') === '1',
            'show_completed' => WebsiteSetting::get('show_completed', '1') === '1',
            'show_schedule' => WebsiteSetting::get('show_schedule', '1') === '1',
        ];

        // 7. Weekly Release Schedule from Admin Schedule Management
        $weeklySchedule = [];
        $hasScheduledAnime = false;
        foreach (array_keys(Anime::DAYS) as $dayKey) {
            $dayAnimes = Anime::where('release_day', $dayKey)
                ->latest()
                ->take(6)
                ->get()
                ->map->toCardFluent();
            $weeklySchedule[$dayKey] = $filterDeleted($dayAnimes);
            if ($weeklySchedule[$dayKey]->isNotEmpty()) {
                $hasScheduledAnime = true;
            }
        }

        $continueWatching = collect();
        if (Auth::check()) {
            $continueWatching = WatchHistory::query()
                ->where('user_id', Auth::id())
                ->with('anime')
                ->latest()
                ->get()
                ->filter(fn (WatchHistory $history) => filled($history->anime_slug) || filled($history->anime?->slug))
                ->unique(fn (WatchHistory $history) => $history->anime_slug ?: $history->anime?->slug)
                ->take(6)
                ->values();
        }

        $announcements = Announcement::query()
            ->active()
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->latest('id')
            ->take(3)
            ->get();

        $recentReviews = Review::query()
            ->where('status', 'published')
            ->with(['user', 'anime'])
            ->latest()
            ->take(4)
            ->get()
            ->filter(fn (Review $review) => $review->anime !== null)
            ->values();

        return view('home', [
            'banners' => $this->banners(),
            'heroSlides' => $finalHeroSlides,
            'trendingAnimes' => $finalTrending,
            'latestEpisodes' => $finalLatestEpisodes,
            'popularAnimes' => $finalPopular,
            'ongoingAnimes' => $finalOngoing,
            'completeAnimes' => $finalComplete,
            'continueWatching' => $continueWatching,
            'announcements' => $announcements,
            'recentReviews' => $recentReviews,
            'catalogHighlights' => $finalHighlights,
            'catalogTotal' => max(0, $catalog->pagination->paginator->total() + Anime::count() - count($blacklisted)),
            'sectionSettings' => $sectionSettings,
            'weeklySchedule' => $weeklySchedule,
            'hasScheduledAnime' => $hasScheduledAnime,
            'scheduleDays' => Anime::DAYS,
            'filters' => [
                'genres' => $genres,
                'statuses' => [
                    'ongoing' => 'Ongoing',
                    'completed' => 'Completed',
                ],
                'ratings' => $this->api->scoreOptions(),
                'years' => $this->api->yearOptions(),
            ],
        ]);
    }

    private function banners(): Collection
    {
        try {
            return Banner::query()->active()->orderBy('sort_order')->get();
        } catch (Throwable) {
            return collect();
        }
    }
}
