<?php

namespace App\Http\Controllers;

use App\Models\MembershipHistory;
use App\Models\MembershipNotification;
use App\Models\MembershipPlan;
use App\Models\MembershipSubscription;
use App\Models\User;
use App\Models\UserMembership;
use App\Models\WatchHistory;
use App\Services\MembershipService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MembershipController extends Controller
{
    public function __construct(private readonly MembershipService $membership)
    {
    }

    public function pricing(): View
    {
        $plans = MembershipPlan::query()
            ->where('is_active', true)
            ->with(['activePrices'])
            ->orderBy('sort_order')
            ->get();

        $user = Auth::user();
        $hasPlans = $plans->isNotEmpty();
        $current = $user && $hasPlans ? $this->membership->currentMembership($user) : null;

        return view('membership.pricing', [
            'plans' => $plans,
            'current' => $current,
            'currentTier' => $user && $hasPlans ? $this->membership->tier($user) : null,
        ]);
    }

    public function show(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $membership = $user->membership;

        if (! $membership && MembershipPlan::query()->exists()) {
            $membership = $this->membership->currentMembership($user);
        }

        if (! $membership) {
            $membership = new UserMembership([
                'user_id' => $user->id,
                'status' => UserMembership::STATUS_ACTIVE,
                'expires_at' => null,
            ]);
            $membership->setRelation('plan', new MembershipPlan([
                'slug' => MembershipPlan::FREE,
                'name' => 'Free',
            ]));
        }

        return view('membership.show', [
            'membership' => $membership->load('plan'),
            'subscriptions' => MembershipSubscription::query()
                ->with(['plan', 'price'])
                ->where('user_id', $user->id)
                ->latest()
                ->take(10)
                ->get(),
            'payments' => $user->payments()->with(['plan', 'price'])->latest()->take(10)->get(),
            'notifications' => $user->membershipNotifications()->latest()->take(5)->get(),
        ]);
    }

    public function history(): View
    {
        /** @var User $user */
        $user = Auth::user();

        return view('membership.history', [
            'subscriptions' => MembershipSubscription::query()
                ->with(['plan', 'price', 'payment'])
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(15),
            'changes' => MembershipHistory::query()
                ->with(['fromPlan', 'toPlan', 'actor'])
                ->where('user_id', $user->id)
                ->latest('created_at')
                ->paginate(15, ['*'], 'changes_page'),
        ]);
    }

    public function notifications(): View
    {
        /** @var User $user */
        $user = Auth::user();

        return view('membership.notifications', [
            'notifications' => $user->membershipNotifications()->latest()->paginate(15),
        ]);
    }

    public function markNotificationRead(MembershipNotification $notification)
    {
        abort_unless($notification->user_id === Auth::id(), 403);

        $notification->update(['read_at' => now()]);

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    public function exportHistory(): Response
    {
        /** @var User $user */
        $user = Auth::user();

        $rows = WatchHistory::query()
            ->where('user_id', $user->id)
            ->latest()
            ->get(['anime_title', 'episode_title', 'created_at', 'is_completed']);

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Anime', 'Episode', 'Ditonton', 'Selesai']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->anime_title,
                    $row->episode_title,
                    optional($row->created_at)->format('Y-m-d H:i:s'),
                    $row->is_completed ? 'Ya' : 'Tidak',
                ]);
            }

            fclose($handle);
        }, 'riwayat-tonton-animestream.csv', ['Content-Type' => 'text/csv; charset=utf-8']);
    }
}
