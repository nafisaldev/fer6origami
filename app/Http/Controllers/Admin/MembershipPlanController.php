<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MembershipPlanRequest;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\UserMembership;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MembershipPlanController extends Controller
{
    public function index(): View
    {
        $this->authorize('managePlans', UserMembership::class);

        return view('admin.membership-plans.index', [
            'plans' => MembershipPlan::with('prices')->orderBy('sort_order')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('managePlans', UserMembership::class);

        return view('admin.membership-plans.create', [
            'plan' => new MembershipPlan(['is_active' => true]),
        ]);
    }

    public function store(MembershipPlanRequest $request): RedirectResponse
    {
        $this->authorize('managePlans', UserMembership::class);

        $data = $this->payload($request);
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        if (! empty($data['is_default'])) {
            MembershipPlan::query()->update(['is_default' => false]);
        }

        $plan = MembershipPlan::create($data);

        ActivityLogger::log('Membership', 'plan_created', "Menambahkan paket membership {$plan->name}", ['plan_id' => $plan->id]);

        return redirect()->route('admin.membership-plans.index')->with('success', 'Paket membership berhasil ditambahkan.');
    }

    public function edit(MembershipPlan $membershipPlan): View
    {
        $this->authorize('managePlans', UserMembership::class);

        return view('admin.membership-plans.edit', [
            'plan' => $membershipPlan->load('prices'),
        ]);
    }

    public function update(MembershipPlanRequest $request, MembershipPlan $membershipPlan): RedirectResponse
    {
        $this->authorize('managePlans', UserMembership::class);

        $data = $this->payload($request);
        $data['updated_by'] = auth()->id();

        if (! empty($data['is_default'])) {
            MembershipPlan::query()->whereKeyNot($membershipPlan->id)->update(['is_default' => false]);
        }

        $membershipPlan->update($data);

        ActivityLogger::log('Membership', 'plan_updated', "Memperbarui paket membership {$membershipPlan->name}", ['plan_id' => $membershipPlan->id]);

        return redirect()->route('admin.membership-plans.index')->with('success', 'Paket membership berhasil diperbarui.');
    }

    public function destroy(MembershipPlan $membershipPlan): RedirectResponse
    {
        $this->authorize('managePlans', UserMembership::class);

        abort_if($membershipPlan->is_default, 422, 'Paket default tidak dapat dihapus.');
        abort_if($membershipPlan->userMemberships()->exists(), 422, 'Paket masih dipakai pengguna.');
        abort_if(Payment::query()->where('plan_id', $membershipPlan->id)->exists(), 422, 'Paket memiliki riwayat pembayaran.');

        $name = $membershipPlan->name;
        $membershipPlan->delete();

        ActivityLogger::log('Membership', 'plan_deleted', "Menghapus paket membership {$name}");

        return redirect()->route('admin.membership-plans.index')->with('success', 'Paket membership dihapus.');
    }

    private function payload(Request $request): array
    {
        $data = $request->validated();
        $benefits = array_values(array_filter(array_map('trim', (array) ($data['benefits'] ?? [])), 'strlen'));

        return [
            'slug' => $data['slug'],
            'name' => $data['name'],
            'tagline' => $data['tagline'] ?? null,
            'description' => $data['description'] ?? null,
            'benefits' => $benefits,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
            'is_default' => $request->boolean('is_default'),
        ];
    }
}
