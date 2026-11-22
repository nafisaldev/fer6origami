<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\MembershipPlanPrice;
use App\Services\PaymentService;
use App\Services\VoucherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly PaymentService $payments,
        private readonly VoucherService $vouchers,
    ) {
    }

    public function create(MembershipPlanPrice $membershipPrice): View
    {
        abort_unless($membershipPrice->is_active && $membershipPrice->plan?->is_active, 404, 'Paket tidak tersedia.');

        $options = $this->payments->checkoutOptions();

        return view('membership.checkout', array_merge($options, [
            'price' => $membershipPrice->load('plan'),
        ]));
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $result = $this->payments->createCheckout(auth()->user(), $request->validated());
        $payment = $result['payment'];

        if ($payment->checkout_url) {
            return redirect()->away($payment->checkout_url);
        }

        return redirect()->route('payments.show', $payment->invoice_number)
            ->with($result['fallbackReason'] ? ['warning' => $result['fallbackReason']] : ['success' => 'Invoice dibuat. Selesaikan pembayaran Anda.']);
    }

    public function checkVoucher(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'plan_price_id' => ['required', 'integer', 'exists:membership_plan_prices,id'],
        ]);

        $price = MembershipPlanPrice::with('plan')->findOrFail($validated['plan_price_id']);
        $result = $this->vouchers->validate($validated['code'], $price, auth()->user());

        if (isset($result['error'])) {
            return response()->json([
                'valid' => false,
                'message' => $result['error'],
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'discount' => (float) $result['discount'],
            'code' => $result['voucher']?->code,
        ]);
    }
}
