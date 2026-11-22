<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

class UserEngagementService
{
    public const WEEKLY_TARGET = 5;
    public const WEEKLY_REWARD_XP = 100;

    public function streak(User $user): array
    {
        $dates = $user->watchHistories()
            ->selectRaw('DATE(created_at) as watched_date')
            ->pluck('watched_date')
            ->map(fn ($date) => (string) $date)
            ->unique()
            ->sort()
            ->values()
            ->all();

        $watchedDates = array_flip($dates);
        $today = now()->toDateString();
        $cursor = isset($watchedDates[$today])
            ? now()->startOfDay()
            : now()->subDay()->startOfDay();

        $current = 0;
        while (isset($watchedDates[$cursor->toDateString()])) {
            $current++;
            $cursor->subDay();
        }

        $best = 0;
        $run = 0;
        $previous = null;

        foreach ($dates as $date) {
            $run = $previous && Carbon::parse($previous)->addDay()->toDateString() === $date
                ? $run + 1
                : 1;
            $best = max($best, $run);
            $previous = $date;
        }

        $days = collect(range(0, 6))->map(function (int $offset) use ($watchedDates) {
            $day = now()->subDays(6 - $offset);

            return [
                'date' => $day->toDateString(),
                'label' => $day->translatedFormat('D'),
                'active' => isset($watchedDates[$day->toDateString()]),
                'is_today' => $day->isToday(),
            ];
        });

        return [
            'current' => $current,
            'best' => max($best, $current),
            'watched_today' => isset($watchedDates[$today]),
            'days' => $days,
        ];
    }

    public function weeklyQuest(User $user): array
    {
        $weekStart = now()->startOfWeek();
        $progress = $user->watchHistories()
            ->where('created_at', '>=', $weekStart)
            ->count();

        $claim = $user->weeklyQuests()
            ->where('week_start', $weekStart->toDateString())
            ->first();

        $claimed = (bool) $claim?->claimed_at;

        return [
            'target' => self::WEEKLY_TARGET,
            'progress' => min($progress, self::WEEKLY_TARGET),
            'reward_xp' => self::WEEKLY_REWARD_XP,
            'claimed' => $claimed,
            'claimable' => ! $claimed && $progress >= self::WEEKLY_TARGET,
            'week_start' => $weekStart->toDateString(),
            'days_left' => 8 - now()->dayOfWeekIso,
            'percent' => (int) min(100, round(($progress / self::WEEKLY_TARGET) * 100)),
        ];
    }

    public function continueWatching(User $user, int $limit = 3): Collection
    {
        return $user->watchHistories()
            ->with(['anime', 'episode'])
            ->latest()
            ->get()
            ->unique(fn ($history) => $history->anime_slug ?: ($history->anime_id ?: $history->anime_title))
            ->take($limit)
            ->map(function ($history) {
                $episode = $history->episode;
                $slug = $history->anime_slug ?: ($history->anime?->slug ?? $history->anime_id);
                $anime = $history->anime ?? new Fluent([
                    'title' => $history->anime_title ?: 'Anime',
                    'poster_image' => $history->anime_poster,
                    'slug' => $slug,
                    'route_id' => $slug,
                ]);
                $episodeId = $episode?->route_id ?: $history->episode_route_id;

                return [
                    'anime' => $anime,
                    'episode' => $episode,
                    'label' => $episode
                        ? 'Episode ' . $episode->episode_number
                        : ($history->episode_title ?: 'Lanjut menonton'),
                    'url' => $episodeId
                        ? route('watch.show', ['animeId' => $slug, 'episodeId' => $episodeId])
                        : route('anime.show', $slug),
                    'watched_at' => $history->created_at,
                ];
            })
            ->values();
    }
}
