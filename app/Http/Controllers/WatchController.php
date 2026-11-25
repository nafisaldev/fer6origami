<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Comment;
use App\Models\Episode;
use App\Models\MembershipPlan;
use App\Models\User;
use App\Models\WatchHistory;
use App\Services\MembershipService;
use App\Services\OtakudesuApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;
use Illuminate\View\View;
use Throwable;

class WatchController extends Controller
{
    public function __construct(
        private readonly OtakudesuApiService $api,
        private readonly MembershipService $membership,
    ) {
    }

    public function show(Request $request, string $animeId, string $episodeId): View|RedirectResponse
    {
        $decodedAnimeId = $this->api->decodeId($animeId);
        $decodedEpisodeId = $this->api->decodeId($episodeId);

        // Abort if this anime was deleted by Admin
        if (\App\Models\DeletedAnime::isBlacklisted($animeId) || \App\Models\DeletedAnime::isBlacklisted($decodedAnimeId)) {
            abort(404, 'Episode has been deleted or is no longer available.');
        }

        // 1. Check if this is an episode from local database
        $epIdNum = str_starts_with($decodedEpisodeId, 'db_ep_') ? (int) substr($decodedEpisodeId, 6) : (is_numeric($decodedEpisodeId) ? (int) $decodedEpisodeId : null);
        $dbEpisode = $epIdNum ? Episode::with('anime.genres', 'anime.episodes')->find($epIdNum) : null;

        if (!$dbEpisode) {
            $dbAnime = Anime::with(['genres', 'episodes'])->where('slug', $decodedAnimeId)->orWhere('slug', $animeId)->first();
            if ($dbAnime) {
                $epNum = (int) filter_var($decodedEpisodeId, FILTER_SANITIZE_NUMBER_INT);
                $dbEpisode = $dbAnime->episodes()->where('episode_number', $epNum > 0 ? $epNum : 1)->first();
            }
        }

        $viewer = auth()->user();
        $maxQualityRank = $this->membership->maxQuality($viewer);
        $rawQuality = $request->query('quality');
        $requestedQuality = $this->membership->normalizeRequestedQuality($rawQuality, $viewer);
        $qualityLimited = $rawQuality !== null && $this->membership->qualityRank($rawQuality) > $maxQualityRank;
        $qualityBlocked = false;

        if ($dbEpisode) {
            if (!$dbEpisode->is_published && (!auth()->check() || !auth()->user()->isAdmin())) {
                abort(404, 'Episode is currently unpublished or draft.');
            }

            $requiredPlan = $dbEpisode->minimum_plan;
            $earlyAccess = $dbEpisode->early_access_until && $dbEpisode->early_access_until->isFuture();

            if ($requiredPlan && $requiredPlan !== 'free') {
                if (! auth()->check()) {
                    return redirect()->route('login')->with('error', 'Masuk dengan akun AnimeStream untuk menonton episode ini.');
                }

                if (! $this->membership->hasAtLeast(auth()->user(), $requiredPlan)) {
                    return redirect()->route('membership.pricing')
                        ->with('error', 'Episode ini memerlukan paket ' . strtoupper($requiredPlan) . '.');
                }
            }

            if ($earlyAccess && (! auth()->check() || ! $this->membership->hasAtLeast(auth()->user(), 'premium'))) {
                return redirect()->route('membership.pricing')
                    ->with('error', 'Episode ini sedang dalam masa akses awal untuk paket Premium dan VIP.');
            }

            $anime = $dbEpisode->anime->toDetailFluent();

            // Resolve streaming options
            $servers = is_array($dbEpisode->servers) ? $dbEpisode->servers : [];
            $requestedServer = $request->query('server');
            $streamOptions = collect();

            if (! empty($servers)) {
                foreach ($servers as $idx => $srv) {
                    $srvUrl = $srv['url'] ?? '';
                    $srvLabel = $srv['name'] ?? $srv['label'] ?? ('Server ' . ($idx + 1));
                    $srvQuality = $srv['quality'] ?? '720p';
                    $qualityRank = $this->membership->qualityRank($srvQuality);

                    $streamOptions->push([
                        'url' => $srvUrl,
                        'label' => $srvLabel,
                        'quality' => $srvQuality,
                        'allowed' => $qualityRank <= $maxQualityRank,
                        'required_tier' => $qualityRank > 1080 ? 'vip' : ($qualityRank > 720 ? 'premium' : 'free'),
                    ]);
                }
            }

            if ($streamOptions->isEmpty() && ! empty($dbEpisode->video_url)) {
                $streamOptions->push([
                    'url' => $dbEpisode->video_url,
                    'label' => 'Standard Player',
                    'quality' => '720p',
                    'allowed' => true,
                    'required_tier' => 'free',
                ]);
            }

            $allowedOptions = $streamOptions->where('allowed', true)->values();
            $selected = null;

            if ($requestedQuality) {
                $requestedRank = $this->membership->qualityRank($requestedQuality);
                $selected = $allowedOptions->first(
                    fn ($option) => $this->membership->qualityRank($option['quality'] ?? null) === $requestedRank
                );
            }

            if (! $selected && $requestedServer) {
                $selected = $allowedOptions->first(fn ($option) => ($option['label'] ?? '') === $requestedServer);
            }

            if (! $selected) {
                $selected = $allowedOptions->first(fn ($option) => ! empty($option['url']));
            }

            if ($selected) {
                $selectedUrl = $selected['url'];
                $selectedLabel = $selected['label'];
                $selectedQuality = $selected['quality'];
            } else {
                $selectedUrl = null;
                $selectedLabel = null;
                $selectedQuality = null;
                $qualityBlocked = true;
            }

            $qualities = collect(is_array($dbEpisode->qualities) ? $dbEpisode->qualities : []);

            $episode = new Fluent([
                'id' => 'db_ep_' . $dbEpisode->id,
                'route_id' => 'db_ep_' . $dbEpisode->id,
                'title' => $dbEpisode->title ?: ('Episode ' . $dbEpisode->episode_number),
                'stream_url' => $selectedUrl,
                'stream_quality' => $selectedQuality,
                'stream_label' => $selectedLabel,
                'stream_options' => $streamOptions,
                'qualities' => $qualities,
                'mirror_groups' => collect(),
                'anime' => $anime,
                'episode_number' => $dbEpisode->episode_number,
                'synopsis' => 'Enjoy watching this episode in high quality on Fer6origami.',
            ]);
        } else {
            // 2. Fallback to API
            try {
                $anime = $this->api->animeDetail($decodedAnimeId);
                $episode = $this->api->episodeDetail(
                    $decodedEpisodeId,
                    $anime->id,
                    $requestedQuality,
                    $anime,
                    $request->query('server')
                );
            } catch (Throwable) {
                [$anime, $episode] = $this->fallbackWatchPayload($animeId, $episodeId);
            }

            $episode->stream_options = collect($episode->stream_options ?? [])
                ->map(function ($option) use ($maxQualityRank) {
                    $qualityRank = $this->membership->qualityRank($option['quality'] ?? null);
                    $option['allowed'] = $qualityRank <= $maxQualityRank;
                    $option['required_tier'] = $qualityRank > 1080 ? 'vip' : ($qualityRank > 720 ? 'premium' : 'free');

                    return $option;
                })
                ->values();

            if ($this->membership->qualityRank($episode->stream_quality ?? null) > $maxQualityRank) {
                $fallbackOption = $episode->stream_options->first(
                    fn ($option) => ($option['allowed'] ?? false) && ! empty($option['url'])
                );

                if ($fallbackOption) {
                    $episode->stream_url = $fallbackOption['url'] ?? null;
                    $episode->stream_label = $fallbackOption['label'] ?? null;
                    $episode->stream_quality = $fallbackOption['quality'] ?? null;
                    $qualityLimited = true;
                } else {
                    $episode->stream_url = null;
                    $episode->stream_label = null;
                    $episode->stream_quality = null;
                    $qualityBlocked = true;
                }
            }
        }

        // 3. Record watch activity without creating admin-managed Anime records.
        // Anime from the external API is stored as a snapshot on the history row.
        try {
            $targetSlug = $anime->route_id ?: ($anime->id ?: $decodedAnimeId);
            $localAnime = $dbEpisode?->anime;

            if (!$localAnime) {
                $localAnime = Anime::where('slug', $targetSlug)
                    ->orWhere('slug', $decodedAnimeId)
                    ->orWhere('slug', $animeId)
                    ->first();
            }

            $localAnime?->increment('views');

            $ip = $request->ip() ?: '127.0.0.1';
            $userId = auth()->id();

            $recentWatch = WatchHistory::where('anime_slug', $targetSlug)
                ->when($localAnime, fn ($q) => $q->where('anime_id', $localAnime->id), fn ($q) => $q->whereNull('anime_id'))
                ->where(function ($q) use ($userId, $ip) {
                    if ($userId) {
                        $q->where('user_id', $userId);
                    } else {
                        $q->where('ip_address', $ip)->whereNull('user_id');
                    }
                })
                ->where('created_at', '>=', now()->subMinutes(3))
                ->exists();

            if (!$recentWatch) {
                WatchHistory::create([
                    'user_id' => $userId,
                    'anime_id' => $localAnime?->id,
                    'anime_slug' => $targetSlug,
                    'anime_title' => $anime->title ?: ('Anime ' . $targetSlug),
                    'anime_poster' => $anime->poster_image ?? null,
                    'episode_id' => $dbEpisode?->id,
                    'episode_route_id' => $episode->route_id ?? null,
                    'episode_title' => $episode->title ?? ('Episode ' . ($episode->episode_number ?? '1')),
                    'ip_address' => $ip,
                    'duration_seconds' => 0,
                    'is_completed' => false,
                ]);
            }
        } catch (Throwable) {
            // Ignore history recording failures
        }

        $currentIndex = collect($anime->episodes)->search(fn ($item) => $item->id === $episode->id);

        $episodes = collect($anime->episodes);

        $previousEpisode = $currentIndex !== false && $currentIndex < $episodes->count() - 1
            ? $episodes->get($currentIndex + 1)
            : null;
        $nextEpisode = $currentIndex !== false && $currentIndex > 0
            ? $episodes->get($currentIndex - 1)
            : null;

        try {
            $recommendations = $this->api->relatedAnime($anime);
        } catch (Throwable) {
            $recommendations = collect();
        }

        // 4. Fetch Published Comments for Episode
        $comments = Comment::query()
            ->with(['user', 'replies.user'])
            ->where('status', 'published')
            ->whereNull('parent_id')
            ->when($dbEpisode, fn ($q) => $q->where('episode_id', $dbEpisode->id))
            ->latest()
            ->take(30)
            ->get();

        $defaultCommentStyle = $this->membership->visual(new User());
        $commentStyles = $comments
            ->filter(fn ($comment) => $comment->user !== null)
            ->flatMap(fn ($comment) => collect([$comment])->merge($comment->replies))
            ->filter(fn ($comment) => $comment->user !== null)
            ->unique('user_id')
            ->mapWithKeys(fn ($comment) => [
                $comment->user_id => $this->membership->visual($comment->user),
            ]);

        return view('watch.show', [
            'episode' => $episode,
            'dbEpisode' => $dbEpisode,
            'nextEpisode' => $nextEpisode,
            'previousEpisode' => $previousEpisode,
            'recommendations' => $recommendations,
            'comments' => $comments,
            'viewerTier' => $viewer ? $this->membership->activeTier($viewer) : MembershipPlan::FREE,
            'viewerVisual' => $this->membership->visual($viewer ?? new User()),
            'maxQualityRank' => $maxQualityRank,
            'qualityLimited' => $qualityLimited,
            'qualityBlocked' => $qualityBlocked,
            'commentStyles' => $commentStyles,
            'defaultCommentStyle' => $defaultCommentStyle,
        ]);
    }

    private function fallbackWatchPayload(string $animeId, string $episodeId): array
    {
        $decodedAnimeId = $this->api->decodeId($animeId);
        $decodedEpisodeId = $this->api->decodeId($episodeId);

        $anime = new Fluent([
            'id' => $decodedAnimeId,
            'route_id' => $this->api->encodeId($decodedAnimeId),
            'title' => 'Episode belum tersedia',
            'poster_image' => asset('images/poster-placeholder.svg'),
            'banner_image' => asset('images/poster-placeholder.svg'),
            'synopsis' => 'Data anime belum berhasil dimuat dari source saat ini.',
            'genres' => collect(),
            'episodes' => collect(),
        ]);

        $episode = new Fluent([
            'id' => $decodedEpisodeId,
            'route_id' => $this->api->encodeId($decodedEpisodeId),
            'title' => 'Episode sementara belum tersedia',
            'stream_url' => null,
            'stream_quality' => null,
            'stream_label' => null,
            'stream_options' => collect(),
            'qualities' => collect(),
            'mirror_groups' => collect(),
            'anime' => $anime,
            'episode_number' => 1,
            'synopsis' => 'Coba refresh lagi beberapa saat. Source streaming sedang belum mengembalikan data lengkap.',
        ]);

        return [$anime, $episode];
    }
}
