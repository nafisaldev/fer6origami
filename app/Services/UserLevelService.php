<?php

namespace App\Services;

use App\Models\User;

class UserLevelService
{
    /**
     * Calculate user level from total XP.
     * Formula: 50 * L * (L - 1) <= XP
     */
    public static function getLevel(int $xp): int
    {
        if ($xp <= 0) {
            return 1;
        }

        $calc = (1 + sqrt(1 + 4 * ($xp / 50))) / 2;
        $level = (int) floor($calc);

        return max(1, $level);
    }

    /**
     * Minimum XP required to reach a specific level.
     */
    public static function getXpForLevel(int $level): int
    {
        if ($level <= 1) {
            return 0;
        }

        return 50 * $level * ($level - 1);
    }

    /**
     * Get complete level metadata and progress for a given XP.
     *
     * @return array{
     *     level: int,
     *     title: string,
     *     rank_name: string,
     *     current_xp: int,
     *     level_min_xp: int,
     *     next_level_xp: int,
     *     xp_in_level: int,
     *     xp_for_next: int,
     *     progress_percent: int,
     *     color: string,
     *     badge_class: string,
     *     bar_class: string
     * }
     */
    public static function getLevelDetails(int $xp): array
    {
        $xp = max(0, $xp);
        $level = self::getLevel($xp);
        $currentMin = self::getXpForLevel($level);
        $nextMin = self::getXpForLevel($level + 1);

        $span = max(1, $nextMin - $currentMin);
        $currentInLevel = max(0, $xp - $currentMin);
        $progress = (int) min(100, max(0, round(($currentInLevel / $span) * 100)));

        $rank = self::getRankInfo($level);

        return [
            'level' => $level,
            'title' => "Lv. {$level} {$rank['name']}",
            'rank_name' => $rank['name'],
            'current_xp' => $xp,
            'level_min_xp' => $currentMin,
            'next_level_xp' => $nextMin,
            'xp_in_level' => $currentInLevel,
            'xp_for_next' => max(0, $nextMin - $xp),
            'progress_percent' => $progress,
            'color' => $rank['color'],
            'badge_class' => $rank['badge_class'],
            'bar_class' => $rank['bar_class'],
        ];
    }

    /**
     * Get rank name and badge colors based on level tier.
     */
    public static function getRankInfo(int $level): array
    {
        return match (true) {
            $level >= 50 => [
                'name' => 'Legendary Sensei',
                'color' => 'rose',
                'badge_class' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                'bar_class' => 'bg-gradient-to-r from-rose-500 to-red-500',
            ],
            $level >= 40 => [
                'name' => 'Anime Master',
                'color' => 'amber',
                'badge_class' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                'bar_class' => 'bg-gradient-to-r from-amber-500 to-yellow-500',
            ],
            $level >= 30 => [
                'name' => 'Senpai Streamer',
                'color' => 'purple',
                'badge_class' => 'bg-purple-500/20 text-purple-300 border-purple-500/30',
                'bar_class' => 'bg-gradient-to-r from-purple-500 to-violet-500',
            ],
            $level >= 20 => [
                'name' => 'Otaku Elite',
                'color' => 'blue',
                'badge_class' => 'bg-blue-500/20 text-blue-300 border-blue-500/30',
                'bar_class' => 'bg-gradient-to-r from-blue-500 to-cyan-500',
            ],
            $level >= 10 => [
                'name' => 'Anime Enthusiast',
                'color' => 'cyan',
                'badge_class' => 'bg-cyan-500/20 text-cyan-300 border-cyan-500/30',
                'bar_class' => 'bg-gradient-to-r from-cyan-500 to-teal-500',
            ],
            $level >= 5 => [
                'name' => 'Apprentice Watcher',
                'color' => 'emerald',
                'badge_class' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                'bar_class' => 'bg-gradient-to-r from-emerald-500 to-green-500',
            ],
            default => [
                'name' => 'Novice Otaku',
                'color' => 'slate',
                'badge_class' => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
                'bar_class' => 'bg-gradient-to-r from-slate-500 to-slate-400',
            ],
        };
    }

    private static function badgeIcon(string $paths): string
    {
        return '<svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';
    }

    /**
     * Badge definitions shared by the profile and the admin badge panel.
     *
     * @return array<string, array{name: string, description: string, icon: string}>
     */
    public static function badgeCatalog(): array
    {
        return [
            'first_stream' => [
                'name' => 'First Stream',
                'description' => 'Menonton episode anime pertama di Fer6origami.',
                'icon' => self::badgeIcon('<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 4v16M17 4v16M3 9h4M3 15h4M17 9h4M17 15h4"/>'),
            ],
            'binge_watcher' => [
                'name' => 'Binge Watcher',
                'description' => 'Menonton lebih dari 10 episode anime.',
                'icon' => self::badgeIcon('<rect x="2" y="6" width="20" height="14" rx="2"/><path d="M8 3l4 3 4-3"/>'),
            ],
            'commentator' => [
                'name' => 'Active Commentator',
                'description' => 'Menulis komentar dan berdiskusi pada anime.',
                'icon' => self::badgeIcon('<path d="M21 15a4 4 0 01-4 4H8l-5 3V7a4 4 0 014-4h10a4 4 0 014 4z"/>'),
            ],
            'critic' => [
                'name' => 'Anime Critic',
                'description' => 'Memberikan review dan rating anime.',
                'icon' => self::badgeIcon('<path d="M12 3l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5-5.8-3-5.8 3 1.1-6.5L2.6 9.8l6.5-.9z"/>'),
            ],
            'collector' => [
                'name' => 'Anime Collector',
                'description' => 'Menyimpan minimal 3 judul anime ke daftar Watchlist.',
                'icon' => self::badgeIcon('<path d="M12 17v5M9 3h6l-1 6 3 3v2H7v-2l3-3z"/>'),
            ],
            'verified' => [
                'name' => 'Verified Otaku',
                'description' => 'Akun pengguna terverifikasi secara resmi.',
                'icon' => self::badgeIcon('<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/>'),
            ],
            'senpai' => [
                'name' => 'Senpai Rank',
                'description' => 'Mencapai Level 10 (Anime Enthusiast).',
                'icon' => self::badgeIcon('<path d="M3 7l4.5 4L12 4l4.5 7L21 7l-2 11H5z"/>'),
            ],
            'master' => [
                'name' => 'Anime Master',
                'description' => 'Mencapai Level 40 atau lebih tinggi.',
                'icon' => self::badgeIcon('<path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 01-10 0zM7 6H4v2a4 4 0 004 4M17 6h3v2a4 4 0 01-4 4"/>'),
            ],
        ];
    }

    /**
     * Keep only badge slugs that exist in the catalog.
     *
     * @return array<int, string>
     */
    public static function normalizeManualBadges(mixed $badges): array
    {
        if (! is_array($badges)) {
            return [];
        }

        $valid = array_keys(self::badgeCatalog());

        return array_values(array_unique(array_filter(
            $badges,
            fn ($badge) => is_string($badge) && in_array($badge, $valid, true)
        )));
    }

    /**
     * Compute unlocked badges for a user. Manual awards are stored in users.badges.
     *
     * @return array<int, array{id: string, name: string, description: string, icon: string, unlocked: bool, auto: bool, manual: bool}>
     */
    public static function getBadges(User $user): array
    {
        $manual = self::normalizeManualBadges($user->badges);
        $watchCount = $user->watch_histories_count ?? $user->watchHistories()->count();
        $commentCount = $user->comments_count ?? $user->comments()->count();
        $reviewCount = $user->reviews_count ?? $user->reviews()->count();
        $watchlistCount = $user->watchlists_count ?? $user->watchlists()->count();
        $level = self::getLevel($user->xp ?? 0);

        $autoUnlocked = [
            'first_stream' => $watchCount >= 1,
            'binge_watcher' => $watchCount >= 10,
            'commentator' => $commentCount >= 1,
            'critic' => $reviewCount >= 1,
            'collector' => $watchlistCount >= 3,
            'verified' => ! is_null($user->email_verified_at),
            'senpai' => $level >= 10,
            'master' => $level >= 40,
        ];

        return collect(self::badgeCatalog())
            ->map(function (array $badge, string $id) use ($autoUnlocked, $manual) {
                $isAuto = (bool) ($autoUnlocked[$id] ?? false);
                $isManual = in_array($id, $manual, true);

                return array_merge($badge, [
                    'id' => $id,
                    'unlocked' => $isAuto || $isManual,
                    'auto' => $isAuto,
                    'manual' => $isManual,
                ]);
            })
            ->values()
            ->all();
    }

    /**
     * Recalculate and update user's XP based on their actual platform activity.
     */
    public static function syncUserXp(User $user): int
    {
        if ($user->xp_manual) {
            return (int) $user->xp;
        }

        $watchHistories = $user->watchHistories()->count();
        $completedWatches = $user->watchHistories()->where('is_completed', true)->count();
        $comments = $user->comments()->count();
        $reviews = $user->reviews()->count();
        $watchlists = $user->watchlists()->count();
        $verifiedXp = $user->email_verified_at ? 100 : 0;

        $computedXp = ($watchHistories * 25)
            + ($completedWatches * 25)
            + ($comments * 15)
            + ($reviews * 35)
            + ($watchlists * 10)
            + $verifiedXp;

        if ($user->xp < $computedXp) {
            $user->update(['xp' => $computedXp]);
        }

        return $user->xp;
    }
}
