<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MembershipActionRequest;
use App\Models\MembershipHistory;
use App\Models\MembershipPlan;
use App\Models\MembershipSubscription;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserMembership;
use App\Services\MembershipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class MembershipController extends Controller
{
    public function __construct(private readonly MembershipService $membership)
    {
    }

    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $planId = $request->integer('plan_id') ?: null;

        $query = UserMembership::query()
            ->with(['user', 'plan'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($planId, fn ($query) => $query->where('plan_id', $planId))
            ->latest();

        return view('admin.membership.index', [
            'memberships' => $query->paginate(20)->withQueryString(),
            'stats' => $this->membership->statistics(),
            'plans' => MembershipPlan::orderBy('sort_order')->get(),
            'search' => $search,
            'selectedStatus' => $status,
            'selectedPlan' => $planId,
        ]);
    }

    public function show(User $user): View
    {
        $membership = $this->membership->currentMembership($user);

        return view('admin.membership.show', [
            'member' => $user,
            'membership' => $membership->load(['plan', 'creator', 'updater']),
            'subscriptions' => MembershipSubscription::query()
                ->with(['plan', 'price', 'creator'])
                ->where('user_id', $user->id)
                ->latest()
                ->take(20)
                ->get(),
            'histories' => MembershipHistory::query()
                ->with(['fromPlan', 'toPlan', 'actor'])
                ->where('user_id', $user->id)
                ->latest('created_at')
                ->take(20)
                ->get(),
            'payments' => Payment::query()
                ->with(['plan', 'price'])
                ->where('user_id', $user->id)
                ->latest()
                ->take(20)
                ->get(),
            'plans' => MembershipPlan::orderBy('sort_order')->get(),
        ]);
    }

    public function grant(MembershipActionRequest $request, User $user): RedirectResponse
    {
        $this->authorize('manage', UserMembership::class);

        $data = $request->validated();
        $plan = MembershipPlan::findOrFail($data['plan_id']);
        $days = (int) ($data['duration_days'] ?? 0);

        abort_if($plan->slug !== MembershipPlan::FREE && $days < 1, 422, 'Durasi wajib diisi untuk paket berbayar.');

        $this->membership->grant($user, $plan, $days, auth()->user(), $data['reason'] ?? null);

        return $this->redirectAfterAction($user, 'Membership berhasil diberikan.');
    }

    public function renew(MembershipActionRequest $request, User $user): RedirectResponse
    {
        $this->authorize('manage', UserMembership::class);

        $data = $request->validated();
        $plan = MembershipPlan::findOrFail($data['plan_id']);
        $days = (int) ($data['duration_days'] ?? 0);

        abort_if($days < 1, 422, 'Durasi perpanjangan wajib diisi.');

        $this->membership->renew($user, $plan, $days, auth()->user());

        return $this->redirectAfterAction($user, 'Membership berhasil diperpanjang.');
    }

    public function changePlan(MembershipActionRequest $request, User $user): RedirectResponse
    {
        $this->authorize('manage', UserMembership::class);

        $data = $request->validated();
        $plan = MembershipPlan::findOrFail($data['plan_id']);
        $days = (int) ($data['duration_days'] ?? 0) ?: null;

        $this->membership->changePlan($user, $plan, auth()->user(), $data['reason'] ?? null, $days);

        return $this->redirectAfterAction($user, 'Paket membership berhasil diubah.');
    }

    public function revoke(MembershipActionRequest $request, User $user): RedirectResponse
    {
        $this->authorize('manage', UserMembership::class);

        $data = $request->validated();

        $this->membership->revoke($user, auth()->user(), $data['reason'] ?? null);

        return $this->redirectAfterAction($user, 'Membership dicabut. Akun kembali ke paket Free.');
    }

    public function runSync(): RedirectResponse
    {
        $this->authorize('manage', UserMembership::class);

        Artisan::call('membership:sync');
        $output = trim(Artisan::output());

        return back()->with('success', 'Sinkronisasi membership selesai. ' . $output);
    }

    private function redirectAfterAction(User $user, string $message): RedirectResponse
    {
        return redirect()->route('admin.membership.show', $user)->with('success', $message);
    }
}
