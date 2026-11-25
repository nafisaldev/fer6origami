<?php

namespace App\Http\Controllers;

use App\Models\MembershipPlan;
use App\Models\User;
use App\Models\UserAvatar;
use App\Models\UserMembership;
use App\Services\MembershipService;
use App\Services\UserEngagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private readonly MembershipService $membership)
    {
    }

    public function show(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();

        // Sync XP from user's platform history
        $user->syncXp();

        return $this->renderProfileView($user, true);
    }

    public function publicProfile(User $user): View|RedirectResponse
    {
        if (Auth::id() === $user->id) {
            return redirect()->route('profile.show');
        }

        $user->syncXp();

        return $this->renderProfileView($user, false);
    }

    public function edit(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $levelInfo = $user->level_details;

        return view('profile.edit', [
            'user' => $user,
            'levelInfo' => $levelInfo,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:500'],
            'favorite_anime' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $binary = (string) file_get_contents($file->getRealPath());

            DB::transaction(function () use ($user, $file, $binary, &$validated): void {
                UserAvatar::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'mime' => $file->getMimeType() ?: 'application/octet-stream',
                        'data' => base64_encode($binary),
                        'size' => strlen($binary),
                    ]
                );

                $validated['avatar_path'] = 'db://avatars/' . $file->hashName();
            });
        }

        $user->update($validated);

        return redirect()->route('profile.show')->with('success', 'Profil Anda berhasil diperbarui!');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password Anda berhasil diubah!');
    }

    public function claimWeeklyQuest(): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $engagement = new UserEngagementService();
        $quest = $engagement->weeklyQuest($user);

        abort_unless($quest['claimable'], 403, 'Misi mingguan belum selesai atau hadiahnya sudah diklaim.');

        $claim = $user->weeklyQuests()->firstOrCreate(
            ['week_start' => $quest['week_start']],
            ['claimed_at' => now()],
        );

        abort_unless($claim->wasRecentlyCreated, 403, 'Hadiah misi mingguan sudah diklaim.');

        $user->awardXp($quest['reward_xp'], 'weekly_quest');

        return back()->with('success', "Misi mingguan selesai! +{$quest['reward_xp']} XP sudah masuk ke profil Anda.");
    }

    private function renderProfileView(User $user, bool $isOwnProfile): View
    {
        $user->load([
            'roleModel',
            'watchlists.anime',
            'watchHistories' => fn ($q) => $q->with(['anime', 'episode'])->latest()->take(15),
            'comments' => fn ($q) => $q->with(['anime', 'episode'])->latest()->take(10),
            'reviews.anime' => fn ($q) => $q->latest()->take(10),
        ]);

        $watchCount = $user->watchHistories()->count();
        $watchDurationSeconds = $user->watchHistories()->sum('duration_seconds');
        // If duration is 0, estimate 24 mins per watched episode
        $estimatedHours = $watchDurationSeconds > 0
            ? round($watchDurationSeconds / 3600, 1)
            : round(($watchCount * 24) / 60, 1);

        $stats = [
            'total_watched' => $watchCount,
            'watch_hours' => $estimatedHours,
            'watchlist_count' => $user->watchlists()->count(),
            'comments_count' => $user->comments()->count(),
            'reviews_count' => $user->reviews()->count(),
        ];

        $engagement = new UserEngagementService();
        $currentMembership = $user->membership;

        if (! $currentMembership && MembershipPlan::query()->exists()) {
            $currentMembership = $this->membership->currentMembership($user);
        }

        if (! $currentMembership) {
            $currentMembership = new UserMembership([
                'user_id' => $user->id,
                'status' => UserMembership::STATUS_ACTIVE,
                'expires_at' => null,
            ]);
            $currentMembership->setRelation('plan', new MembershipPlan([
                'slug' => MembershipPlan::FREE,
                'name' => 'Free',
            ]));
        } else {
            $currentMembership->loadMissing('plan');
        }

        $membershipTier = $currentMembership->plan->slug ?? MembershipPlan::FREE;
        $membershipXpMultiplier = MembershipPlan::query()->exists()
            ? $this->membership->xpMultiplier($user)
            : 1.0;

        return view('profile.show', [
            'user' => $user,
            'isOwnProfile' => $isOwnProfile,
            'stats' => $stats,
            'levelInfo' => $user->level_details,
            'badges' => $user->badges_list,
            'streak' => $engagement->streak($user),
            'weeklyQuest' => $engagement->weeklyQuest($user),
            'continueWatching' => $engagement->continueWatching($user),
            'membership' => $currentMembership,
            'membershipTier' => $membershipTier,
            'membershipXpMultiplier' => $membershipXpMultiplier,
            'membershipVisual' => $this->membership->visual($user),
        ]);
    }
}
