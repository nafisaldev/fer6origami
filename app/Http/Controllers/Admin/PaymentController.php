<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Payment::class);

        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $gateway = $request->string('gateway')->toString();

        $payments = Payment::query()
            ->with(['user', 'plan', 'price'])
            ->when($search, function ($query) use ($search) {
                $query->where('invoice_number', 'like', '%' . $search . '%')
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%'));
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($gateway !== '', fn ($query) => $query->where('gateway', $gateway))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $revenue = Payment::query()->where('status', Payment::STATUS_SUCCESS)->sum('total_amount');
        $refunds = Payment::query()->where('status', Payment::STATUS_REFUNDED)->sum('total_amount');

        return view('admin.payments.index', [
            'payments' => $payments,
            'search' => $search,
            'selectedStatus' => $status,
            'selectedGateway' => $gateway,
            'revenue' => (float) $revenue - (float) $refunds,
            'pendingCount' => Payment::query()->where('status', Payment::STATUS_PENDING)->count(),
        ]);
    }

    public function show(Payment $payment): View
    {
        $this->authorize('view', $payment);

        return view('admin.payments.show', [
            'payment' => $payment->load(['user', 'plan', 'price', 'verifier', 'voucherRedemptions.voucher']),
        ]);
    }

    public function verify(Payment $payment): RedirectResponse
    {
        $this->authorize('verify', $payment);

        $this->payments->verifyManual($payment, auth()->user());

        return back()->with('success', 'Pembayaran manual disetujui dan membership aktif.');
    }

    public function markFailed(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorize('verify', $payment);

        $reason = $request->string('reason')->toString() ?: 'Ditolak administrator';
        $this->payments->markFailed($payment, $reason);

        return back()->with('success', 'Pembayaran ditandai gagal.');
    }

    public function markRefunded(Payment $payment): RedirectResponse
    {
        $this->authorize('verify', $payment);

        $this->payments->markRefunded($payment);

        return back()->with('success', 'Pembayaran ditandai refund.');
    }

    public function markExpired(Payment $payment): RedirectResponse
    {
        $this->authorize('verify', $payment);

        $this->payments->markExpired($payment);

        return back()->with('success', 'Invoice ditandai kedaluwarsa.');
    }
}
