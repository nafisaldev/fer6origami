<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MembershipPriceRequest;
use App\Models\MembershipPlan;
use App\Models\MembershipPlanPrice;
use App\Models\MembershipSubscription;
use App\Models\Payment;
use App\Models\UserMembership;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;

class MembershipPriceController extends Controller
{
    public function store(MembershipPriceRequest $request, MembershipPlan $membershipPlan): RedirectResponse
    {
        $this->authorize('managePlans', UserMembership::class);

        $data = $request->validated();
        $data['plan_id'] = $membershipPlan->id;
        $data['is_active'] = $request->boolean('is_active');
        $data['currency'] = 'IDR';

        $price = MembershipPlanPrice::create($data);

        ActivityLogger::log('Membership', 'price_created', "Menambahkan harga {$membershipPlan->name} {$price->label}", ['price_id' => $price->id]);

        return $this->redirect($membershipPlan);
    }

    public function update(
        MembershipPriceRequest $request,
        MembershipPlan $membershipPlan,
        MembershipPlanPrice $membershipPrice
    ): RedirectResponse {
        $this->authorize('managePlans', UserMembership::class);
        abort_unless($membershipPrice->plan_id === $membershipPlan->id, 404);

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $membershipPrice->update($data);

        ActivityLogger::log('Membership', 'price_updated', "Memperbarui harga {$membershipPlan->name} {$membershipPrice->label}", ['price_id' => $membershipPrice->id]);

        return $this->redirect($membershipPlan);
    }

    public function destroy(
        MembershipPlan $membershipPlan,
        MembershipPlanPrice $membershipPrice
    ): RedirectResponse {
        $this->authorize('managePlans', UserMembership::class);
        abort_unless($membershipPrice->plan_id === $membershipPlan->id, 404);

        abort_if(Payment::query()->where('plan_price_id', $membershipPrice->id)->exists(), 422, 'Harga memiliki riwayat pembayaran.');
        abort_if(MembershipSubscription::query()->where('plan_price_id', $membershipPrice->id)->exists(), 422, 'Harga memiliki riwayat langganan.');

        $label = $membershipPrice->label;
        $membershipPrice->delete();

        ActivityLogger::log('Membership', 'price_deleted', "Menghapus harga {$membershipPlan->name} {$label}");

        return $this->redirect($membershipPlan);
    }

    private function redirect(MembershipPlan $plan): RedirectResponse
    {
        return redirect()->route('admin.membership-plans.edit', $plan)->with('success', 'Harga paket berhasil disimpan.');
    }
}
