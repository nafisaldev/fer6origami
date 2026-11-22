<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentProofRequest;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments)
    {
    }

    public function show(string $invoice): View
    {
        $payment = Payment::query()
            ->with(['plan', 'price', 'user'])
            ->where('invoice_number', $invoice)
            ->firstOrFail();

        abort_unless($payment->user_id === auth()->id(), 403);

        return view('payments.show', [
            'payment' => $payment,
            'bankName' => config('membership.manual_payment.bank_name'),
            'bankAccount' => config('membership.manual_payment.bank_account'),
            'bankHolder' => config('membership.manual_payment.bank_holder'),
        ]);
    }

    public function proof(PaymentProofRequest $request, string $invoice): RedirectResponse
    {
        $payment = Payment::query()
            ->where('invoice_number', $invoice)
            ->firstOrFail();

        abort_unless($payment->user_id === auth()->id(), 403);

        $this->payments->attachProof($payment, $request->file('proof'));

        return back()->with('success', 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi administrator.');
    }

    public function callback(Request $request): View|RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('login')->with('error', 'Masuk untuk melihat status pembayaran Anda.');
        }

        $payment = Payment::query()
            ->with('plan')
            ->where('invoice_number', $request->string('invoice')->toString())
            ->firstOrFail();

        abort_unless($payment->user_id === auth()->id(), 403);

        return view('payments.callback', [
            'payment' => $payment,
            'data' => $this->payments->callbackData($payment),
        ]);
    }

    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->json()->all();

        if (! is_array($payload) || $payload === []) {
            return response()->json(['status' => 'invalid_payload'], 400);
        }

        try {
            $result = $this->payments->handleWebhook($payload, $request->getContent());
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['status' => 'error'], 500);
        }

        if ($result['status'] === 'invalid_signature') {
            return response()->json(['status' => 'invalid_signature'], 403);
        }

        return response()->json(['status' => $result['status'], 'duplicate' => $result['duplicate']]);
    }
}
