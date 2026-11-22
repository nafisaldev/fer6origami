<?php

namespace App\Services;

use App\Models\MembershipHistory;
use App\Models\MembershipPlan;
use App\Models\MembershipSubscription;
use App\Models\User;
use App\Models\UserMembership;
use App\Models\WebsiteSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class MembershipService
{
    public const TIERS = [
        MembershipPlan::FREE => 0,
        MembershipPlan::PREMIUM => 1,
        MembershipPlan::VIP => 2,
    ];

    public function tiers(): array
    {
        return self::TIERS;
    }

    public function rank(?string $slug): int
    {
        return self::TIERS[$slug] ?? 0;
    }

    public function defaultPlan(): ?MembershipPlan
    {
        return MembershipPlan::query()
            ->where('is_default', true)
            ->where('is_active', true)
            ->first()
            ?? MembershipPlan::query()->where('slug', MembershipPlan::FREE)->first()
            ?? MembershipPlan::query()->orderBy('sort_order')->first();
    }

    public function planBySlug(string $slug): ?MembershipPlan
    {
        return MembershipPlan::query()->where('slug', $slug)->where('is_active', true)->first();
    }

    public function currentMembership(User $user): UserMembership
    {
        $membership = $user->membership;

        if ($membership) {
            return $membership;
        }

        $plan = $this->defaultPlan();

        if (! $plan) {
            $membership = new UserMembership([
                'user_id' => $user->id,
                'status' => UserMembership::STATUS_ACTIVE,
                'starts_at' => now(),
                'expires_at' => null,
                'source' => 'registration',
            ]);
            $membership->setRelation('plan', new MembershipPlan([
                'slug' => MembershipPlan::FREE,
                'name' => 'Free',
            ]));

            return $membership;
        }

        return DB::transaction(function () use ($user, $plan) {
            $membership = UserMembership::query()->where('user_id', $user->id)->lockForUpdate()->first();

            if ($membership) {
                return $membership;
            }

            $membership = UserMembership::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => UserMembership::STATUS_ACTIVE,
                'starts_at' => now(),
                'expires_at' => null,
                'source' => 'registration',
            ]);

            MembershipSubscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'action' => 'grant',
                'status' => 'active',
                'starts_at' => $membership->starts_at,
                'expires_at' => null,
                'notes' => 'Paket Free awal',
            ]);

            return $membership;
        });
    }

    public function tier(User $user): string
    {
        return $this->currentMembership($user)->plan->slug ?? MembershipPlan::FREE;
    }

    public function hasAtLeast(User $user, string $minimum): bool
    {
        $membership = $this->currentMembership($user);

        if ($membership->status !== UserMembership::STATUS_ACTIVE) {
            return $minimum === MembershipPlan::FREE;
        }

        if ($membership->expires_at && $membership->expires_at->isPast()) {
            return $minimum === MembershipPlan::FREE;
        }

        return $this->rank($membership->plan->slug ?? MembershipPlan::FREE) >= $this->rank($minimum);
    }

    public function xpMultiplier(User $user): float
    {
        if ($user->xp_manual) {
            return 1.0;
        }

        $slug = $this->tier($user);

        if ($slug === MembershipPlan::VIP) {
            return (float) (WebsiteSetting::get('membership_xp_boost_vip', config('membership.xp_boost.vip', 1.5)));
        }

        if ($slug === MembershipPlan::PREMIUM) {
            return (float) (WebsiteSetting::get('membership_xp_boost_premium', config('membership.xp_boost.premium', 1.25)));
        }

        return 1.0;
    }

    public function activeTier(User $user): string
    {
        $membership = $user->membership;

        if (! $membership || $membership->status !== UserMembership::STATUS_ACTIVE) {
            return MembershipPlan::FREE;
        }

        if ($membership->expires_at && $membership->expires_at->isPast()) {
            return MembershipPlan::FREE;
        }

        return $membership->plan->slug ?? MembershipPlan::FREE;
    }

    public function qualityRank(mixed $quality): int
    {
        if (is_int($quality)) {
            return $quality;
        }

        $value = strtolower(trim((string) $quality));

        if ($value === '' || $value === 'default') {
            return 0;
        }

        if (preg_match('/(2160|4k|uhd)/i', $value)) {
            return 2160;
        }

        if (preg_match('/(1440|2k|qhd)/i', $value)) {
            return 1440;
        }

        if (preg_match('/(\d{3,4})/i', $value, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }

    public function maxQuality(?User $user): int
    {
        if (! $user) {
            return 720;
        }

        return match ($this->activeTier($user)) {
            MembershipPlan::VIP => 2160,
            MembershipPlan::PREMIUM => 1080,
            default => 720,
        };
    }

    public function normalizeRequestedQuality(mixed $quality, ?User $user = null): ?string
    {
        if ($quality === null || trim((string) $quality) === '') {
            return null;
        }

        $requestedRank = $this->qualityRank($quality);
        $maxRank = $this->maxQuality($user);

        if ($requestedRank <= $maxRank) {
            return trim((string) $quality);
        }

        return $maxRank . 'p';
    }

    public function visual(User $user): array
    {
        return match ($this->activeTier($user)) {
            MembershipPlan::VIP => [
                'tier' => MembershipPlan::VIP,
                'label' => 'VIP',
                'comment_card' => 'border-amber-500/30 bg-amber-500/10',
                'comment_avatar' => 'ring-2 ring-amber-400/70 bg-amber-500/20 text-amber-200',
                'comment_name' => 'text-amber-300',
                'comment_badge' => 'border-amber-500/40 bg-amber-500/15 text-amber-300',
                'profile_frame' => 'bg-amber-500/20 ring-2 ring-amber-300/80 shadow-[0_0_24px_rgba(251,191,36,0.22)]',
                'max_quality' => 2160,
                'max_quality_label' => '4K UHD',
            ],
            MembershipPlan::PREMIUM => [
                'tier' => MembershipPlan::PREMIUM,
                'label' => 'Premium',
                'comment_card' => 'border-brand-500/30 bg-brand-500/10',
                'comment_avatar' => 'ring-2 ring-brand-400/60 bg-brand-500/20 text-brand-200',
                'comment_name' => 'text-brand-300',
                'comment_badge' => 'border-brand-500/40 bg-brand-500/15 text-brand-300',
                'profile_frame' => 'bg-brand-500/15 ring-2 ring-brand-400/70 shadow-glow',
                'max_quality' => 1080,
                'max_quality_label' => '1080p',
            ],
            default => [
                'tier' => MembershipPlan::FREE,
                'label' => 'Free',
                'comment_card' => 'border-white/5 bg-[#11131b]',
                'comment_avatar' => 'bg-brand-500/20 text-brand-400',
                'comment_name' => 'text-white',
                'comment_badge' => 'border-white/10 bg-white/5 text-slate-300',
                'profile_frame' => 'bg-white/5 ring-1 ring-white/10',
                'max_quality' => 720,
                'max_quality_label' => '720p',
            ],
        };
    }

    public function grant(User $user, MembershipPlan $plan, int $days, ?User $actor = null, ?string $reason = null): UserMembership
    {
        return $this->activate($user, $plan, [
            'duration_days' => $days,
            'source' => 'admin_grant',
            'action' => 'grant',
            'created_by' => $actor?->id,
            'notes' => $reason,
        ]);
    }

    public function renew(User $user, MembershipPlan $plan, int $days, ?User $actor = null): UserMembership
    {
        return $this->activate($user, $plan, [
            'duration_days' => $days,
            'source' => 'renew',
            'action' => 'renew',
            'created_by' => $actor?->id,
        ]);
    }

    public function changePlan(User $user, MembershipPlan $plan, ?User $actor = null, ?string $reason = null, ?int $days = null): UserMembership
    {
        $current = $this->currentMembership($user);
        $action = $this->rank($plan->slug) > $this->rank($current->plan->slug ?? MembershipPlan::FREE) ? 'upgrade' : 'downgrade';
        $preserved = $current->expires_at && $current->expires_at->isFuture() ? $current->expires_at->copy() : null;

        $options = [
            'duration_days' => $plan->slug === MembershipPlan::FREE ? null : $days,
            'source' => 'admin_' . $action,
            'action' => $action,
            'created_by' => $actor?->id,
            'notes' => $reason,
        ];

        if ($plan->slug !== MembershipPlan::FREE && $days === null && $preserved) {
            $options['expires_at'] = $preserved;
        }

        return $this->activate($user, $plan, $options);
    }

    public function revoke(User $user, ?User $actor = null, ?string $reason = null): UserMembership
    {
        $plan = $this->defaultPlan();

        if (! $plan) {
            return $this->currentMembership($user);
        }

        return $this->activate($user, $plan, [
            'duration_days' => null,
            'source' => 'admin_revoke',
            'action' => 'revoke',
            'created_by' => $actor?->id,
            'notes' => $reason ?? 'Membership dicabut administrator',
        ]);
    }

    public function expire(User $user): UserMembership
    {
        $plan = $this->defaultPlan();

        if (! $plan) {
            return $this->currentMembership($user);
        }

        return $this->activate($user, $plan, [
            'duration_days' => null,
            'source' => 'system_expiry',
            'action' => 'expiry',
            'notes' => 'Masa aktif berakhir',
        ]);
    }

    public function activate(User $user, MembershipPlan $plan, array $options = []): UserMembership
    {
        return DB::transaction(function () use ($user, $plan, $options) {
            $membership = UserMembership::query()->where('user_id', $user->id)->lockForUpdate()->first();

            if (! $membership) {
                $membership = UserMembership::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'status' => UserMembership::STATUS_ACTIVE,
                    'starts_at' => now(),
                    'source' => 'registration',
                ]);
            }

            $fromPlanId = $membership->plan_id;
            $action = $options['action'] ?? 'grant';
            $duration = $options['duration_days'] ?? null;
            $isFree = $plan->slug === MembershipPlan::FREE;
            $startsAt = $options['starts_at'] ?? now();

            if (array_key_exists('expires_at', $options)) {
                $expiresAt = $options['expires_at'];
            } elseif ($isFree || ! $duration) {
                $expiresAt = null;
            } else {
                $base = $action === 'renew' && $membership->expires_at && $membership->expires_at->isFuture()
                    ? $membership->expires_at->copy()
                    : $startsAt->copy();
                $expiresAt = $base->addDays((int) $duration);
            }

            $before = [
                'plan_id' => $membership->plan_id,
                'status' => $membership->status,
                'starts_at' => optional($membership->starts_at)->toDateTimeString(),
                'expires_at' => optional($membership->expires_at)->toDateTimeString(),
            ];

            $membership->update([
                'plan_id' => $plan->id,
                'status' => $options['status'] ?? UserMembership::STATUS_ACTIVE,
                'starts_at' => $startsAt,
                'expires_at' => $isFree ? null : $expiresAt,
                'source' => $options['source'] ?? 'admin_grant',
                'created_by' => $options['created_by'] ?? null,
                'updated_by' => $options['created_by'] ?? null,
                'notes' => $options['notes'] ?? null,
            ]);

            $subscription = MembershipSubscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'plan_price_id' => $options['plan_price_id'] ?? null,
                'payment_id' => $options['payment_id'] ?? null,
                'parent_subscription_id' => $options['parent_subscription_id'] ?? null,
                'action' => $action,
                'status' => $options['status'] ?? 'active',
                'duration_days' => $duration,
                'starts_at' => $startsAt,
                'expires_at' => $isFree ? null : $expiresAt,
                'created_by' => $options['created_by'] ?? null,
                'notes' => $options['notes'] ?? null,
            ]);

            MembershipHistory::create([
                'user_id' => $user->id,
                'from_plan_id' => $fromPlanId,
                'to_plan_id' => $plan->id,
                'action' => $action,
                'duration_days' => $duration,
                'reason' => $options['notes'] ?? null,
                'changes' => [
                    'before' => $before,
                    'after' => [
                        'plan_id' => $membership->plan_id,
                        'status' => $membership->status,
                        'starts_at' => optional($membership->starts_at)->toDateTimeString(),
                        'expires_at' => optional($membership->expires_at)->toDateTimeString(),
                    ],
                    'subscription_id' => $subscription->id,
                ],
                'actor_id' => $options['created_by'] ?? Auth::id(),
                'ip_address' => Request::ip(),
                'user_agent' => substr((string) Request::userAgent(), 0, 255),
            ]);

            ActivityLogger::log('Membership', $action, "Membership {$user->name} diubah ke {$plan->name}", [
                'user_id' => $user->id,
                'from_plan_id' => $fromPlanId,
                'to_plan_id' => $plan->id,
                'expires_at' => optional($membership->expires_at)->toDateTimeString(),
            ], $options['created_by'] ?? null);

            return $membership->load('plan');
        });
    }

    public function expiringMemberships(int $withinDays = 7): Collection
    {
        return UserMembership::query()
            ->with(['user', 'plan'])
            ->where('status', UserMembership::STATUS_ACTIVE)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays($withinDays)])
            ->get();
    }

    public function expiredMemberships(): Collection
    {
        return UserMembership::query()
            ->with('user')
            ->where('status', UserMembership::STATUS_ACTIVE)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();
    }

    public function statistics(): array
    {
        $counts = UserMembership::query()
            ->join('membership_plans', 'membership_plans.id', '=', 'user_memberships.plan_id')
            ->where('user_memberships.status', UserMembership::STATUS_ACTIVE)
            ->selectRaw('membership_plans.slug as tier, COUNT(*) as total')
            ->groupBy('membership_plans.slug')
            ->pluck('total', 'tier');

        $revenue = \App\Models\Payment::query()
            ->where('status', \App\Models\Payment::STATUS_SUCCESS)
            ->sum('total_amount');

        $refunds = \App\Models\Payment::query()
            ->where('status', \App\Models\Payment::STATUS_REFUNDED)
            ->sum('total_amount');

        return [
            'free' => (int) ($counts[MembershipPlan::FREE] ?? 0),
            'premium' => (int) ($counts[MembershipPlan::PREMIUM] ?? 0),
            'vip' => (int) ($counts[MembershipPlan::VIP] ?? 0),
            'revenue' => (float) $revenue - (float) $refunds,
            'pending' => (int) \App\Models\Payment::query()->where('status', \App\Models\Payment::STATUS_PENDING)->count(),
            'expiring' => $this->expiringMemberships(7)->count(),
        ];
    }
}
