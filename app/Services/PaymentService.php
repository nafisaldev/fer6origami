<?php

namespace App\Services;

use App\Models\MembershipPlan;
use App\Models\MembershipPlanPrice;
use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
use App\Models\User;
use App\Models\UserMembership;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        private readonly MembershipService $membership,
        private readonly VoucherService $voucher,
        private readonly MembershipNotificationService $notifications,
    ) {
    }

    public function checkoutOptions(): array
    {
        $plans = MembershipPlan::query()
            ->where('is_active', true)
            ->with(['activePrices'])
            ->orderBy('sort_order')
            ->get();

        return [
            'plans' => $plans,
            'manualEnabled' => (bool) config('membership.manual_payment.enabled'),
            'bankName' => config('membership.manual_payment.bank_name'),
            'bankAccount' => config('membership.manual_payment.bank_account'),
            'bankHolder' => config('membership.manual_payment.bank_holder'),
            'midtransEnabled' => filled(config('services.midtrans.server_key')) && filled(config('services.midtrans.client_key')),
        ];
    }

    public function createCheckout(User $user, array $data): array
    {
        $price = MembershipPlanPrice::query()
            ->with('plan')
            ->whereKey($data['plan_price_id'])
            ->where('is_active', true)
            ->firstOrFail();

        abort_unless($price->plan && $price->plan->is_active, 404, 'Paket tidak tersedia.');

        $voucherResult = $this->voucher->validate($data['voucher_code'] ?? null, $price, $user);

        if (isset($voucherResult['error'])) {
            abort(422, $voucherResult['error']);
        }

        $discount = (float) ($voucherResult['discount'] ?? 0);
        $total = max(0, (float) $price->price - $discount);

        $gateway = $data['gateway'] === 'midtrans' ? Payment::GATEWAY_MIDTRANS : Payment::GATEWAY_MANUAL;
        $fallbackReason = null;

        if ($gateway === Payment::GATEWAY_MIDTRANS && ! filled(config('services.midtrans.server_key'))) {
            $gateway = Payment::GATEWAY_MANUAL;
            $fallbackReason = 'Midtrans belum dikonfigurasi. Pembayaran dialihkan ke transfer manual.';
        }

        $payment = DB::transaction(function () use ($user, $price, $discount, $total, $gateway, $voucherResult) {
            $payment = Payment::create([
                'invoice_number' => $this->invoiceNumber(),
                'user_id' => $user->id,
                'plan_id' => $price->plan_id,
                'plan_price_id' => $price->id,
                'amount' => $price->price,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'currency' => $price->currency ?: 'IDR',
                'status' => Payment::STATUS_PENDING,
                'gateway' => $gateway,
                'gateway_order_id' => null,
                'expired_at' => now()->addHours(24),
            ]);

            $payment->update(['gateway_order_id' => $payment->invoice_number]);

            if ($discount > 0 && $voucher = ($voucherResult['voucher'] ?? null)) {
                $this->voucher->redeem($voucher, $user, $payment, $discount);
            }

            return $payment;
        });

        if ($gateway === Payment::GATEWAY_MIDTRANS) {
            $this->createSnapTransaction($payment, $user);
        }

        return [
            'payment' => $payment->fresh(),
            'fallbackReason' => $fallbackReason,
        ];
    }

    public function attachProof(Payment $payment, $file): void
    {
        abort_unless($payment->gateway === Payment::GATEWAY_MANUAL, 422, 'Bukti pembayaran hanya untuk pembayaran manual.');
        abort_unless($payment->status === Payment::STATUS_PENDING, 422, 'Invoice ini tidak lagi menunggu pembayaran.');

        $path = $file->store('payment-proofs', 'public');

        $payment->update(['proof_path' => $path]);
    }

    public function verifyManual(Payment $payment, User $actor): UserMembership
    {
        abort_unless($payment->gateway === Payment::GATEWAY_MANUAL, 422, 'Invoice ini bukan pembayaran manual.');
        abort_unless($payment->status === Payment::STATUS_PENDING, 422, 'Invoice ini sudah diproses.');

        return $this->markSuccess($payment, [
            'verified_by' => $actor->id,
            'verified_at' => now(),
        ]);
    }

    public function markFailed(Payment $payment, ?string $reason = null): Payment
    {
        if ($payment->status === Payment::STATUS_SUCCESS) {
            return $payment;
        }

        $payment->update([
            'status' => Payment::STATUS_FAILED,
            'failure_reason' => $reason,
        ]);

        return $payment;
    }

    public function markExpired(Payment $payment): Payment
    {
        if ($payment->status !== Payment::STATUS_PENDING) {
            return $payment;
        }

        $payment->update([
            'status' => Payment::STATUS_EXPIRED,
            'failure_reason' => 'Invoice kedaluwarsa',
        ]);

        return $payment;
    }

    public function markRefunded(Payment $payment): Payment
    {
        abort_unless($payment->status === Payment::STATUS_SUCCESS, 422, 'Hanya pembayaran lunas yang dapat ditandai refund.');

        $payment->update(['status' => Payment::STATUS_REFUNDED]);

        return $payment;
    }

    public function expirePending(): int
    {
        $payments = Payment::query()
            ->where('status', Payment::STATUS_PENDING)
            ->whereNotNull('expired_at')
            ->where('expired_at', '<=', now())
            ->get();

        foreach ($payments as $payment) {
            $this->markExpired($payment);
        }

        return $payments->count();
    }

    public function handleWebhook(array $payload, string $rawPayload = ''): array
    {
        $gateway = 'midtrans';
        $hashSource = $rawPayload !== '' ? $rawPayload : json_encode($payload);
        $hash = hash('sha256', $hashSource ?: '');

        if (PaymentWebhookEvent::query()->where('gateway', $gateway)->where('payload_hash', $hash)->exists()) {
            return ['status' => 'duplicate', 'duplicate' => true];
        }

        if (! $this->validSignature($payload)) {
            PaymentWebhookEvent::create([
                'gateway' => $gateway,
                'event_id' => (string) ($payload['transaction_id'] ?? ''),
                'order_id' => (string) ($payload['order_id'] ?? ''),
                'payload_hash' => $hash,
                'status' => 'invalid_signature',
                'payload' => $payload,
                'error' => 'Signature tidak cocok',
            ]);

            return ['status' => 'invalid_signature', 'duplicate' => false];
        }

        if (PaymentWebhookEvent::query()->where('gateway', $gateway)->where('payload_hash', $hash)->exists()) {
            return ['status' => 'duplicate', 'duplicate' => true];
        }

        try {
            $event = PaymentWebhookEvent::create([
                'gateway' => $gateway,
                'event_id' => (string) ($payload['transaction_id'] ?? ''),
                'order_id' => (string) ($payload['order_id'] ?? ''),
                'payload_hash' => $hash,
                'status' => 'processing',
                'payload' => $payload,
            ]);
        } catch (\Illuminate\Database\QueryException $exception) {
            if (str_contains(strtolower($exception->getMessage()), 'duplicate')) {
                return ['status' => 'duplicate', 'duplicate' => true];
            }

            throw $exception;
        }

        try {
            $result = $this->processWebhook($payload);
            $event->update([
                'status' => $result['status'],
                'processed_at' => now(),
            ]);

            return $result;
        } catch (\Throwable $exception) {
            $event->update([
                'status' => 'failed',
                'error' => substr($exception->getMessage(), 0, 500),
                'processed_at' => now(),
            ]);

            Log::warning('Midtrans webhook processing failed', [
                'order_id' => $payload['order_id'] ?? null,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function callbackData(Payment $payment): array
    {
        return [
            'invoice' => $payment->invoice_number,
            'status' => $payment->status,
            'plan' => $payment->plan->name ?? '-',
            'total' => $payment->total_amount,
            'gateway' => $payment->gateway,
        ];
    }

    private function createSnapTransaction(Payment $payment, User $user): void
    {
        $config = config('services.midtrans');
        $url = rtrim((string) $config['base_url'], '/') . '/v1/snap/transactions';

        $payload = [
            'transaction_details' => [
                'order_id' => $payment->invoice_number,
                'gross_amount' => (int) round((float) $payment->total_amount),
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'item_details' => [
                [
                    'id' => $payment->plan->slug ?? 'membership',
                    'name' => 'Membership ' . ($payment->plan->name ?? '') . ' ' . optional($payment->price)->label,
                    'price' => (int) round((float) $payment->total_amount),
                    'quantity' => 1,
                ],
            ],
            'callbacks' => [
                'finish' => route('payments.callback', ['invoice' => $payment->invoice_number]),
            ],
            'expiry' => [
                'start_time' => now()->toIso8601String(),
                'unit' => 'hour',
                'duration' => 24,
            ],
        ];

        try {
            $response = Http::withBasicAuth((string) $config['server_key'], '')
                ->acceptJson()
                ->asJson()
                ->timeout(20)
                ->post($url, $payload);

            if (! $response->successful()) {
                throw new RequestException($response);
            }

            $data = $response->json();

            $payment->update([
                'checkout_url' => $data['redirect_url'] ?? null,
                'gateway_payload' => [
                    'token' => $data['token'] ?? null,
                    'redirect_url' => $data['redirect_url'] ?? null,
                ],
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Midtrans snap transaction failed', [
                'payment_id' => $payment->id,
                'error' => $exception->getMessage(),
            ]);

            $payment->update([
                'gateway' => Payment::GATEWAY_MANUAL,
                'failure_reason' => 'Midtrans tidak tersedia. Silakan gunakan transfer manual.',
                'checkout_url' => null,
            ]);
        }
    }

    private function markSuccess(Payment $payment, array $options = []): UserMembership
    {
        return DB::transaction(function () use ($payment, $options) {
            $locked = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            $existing = $locked->subscriptions()
                ->where('payment_id', $locked->id)
                ->where('action', 'purchase')
                ->first();

            if ($existing && $locked->status === Payment::STATUS_SUCCESS) {
                return $locked->user->membership ?? $this->membership->currentMembership($locked->user);
            }

            $locked->update(array_merge([
                'status' => Payment::STATUS_SUCCESS,
                'paid_at' => now(),
            ], $options));

            $price = $locked->price;
            $plan = $locked->plan;

            abort_unless($price && $plan, 422, 'Data paket pembayaran tidak lengkap.');

            $membership = $this->membership->activate($locked->user, $plan, [
                'duration_days' => (int) $price->duration_days,
                'action' => 'purchase',
                'source' => 'payment',
                'plan_price_id' => $price->id,
                'payment_id' => $locked->id,
                'notes' => 'Pembayaran invoice ' . $locked->invoice_number,
                'created_by' => $options['verified_by'] ?? null,
            ]);

            $this->notifications->paymentSuccess($locked, $membership);

            return $membership;
        });
    }

    private function processWebhook(array $payload): array
    {
        $orderId = (string) ($payload['order_id'] ?? '');
        $payment = Payment::query()
            ->where('gateway_order_id', $orderId)
            ->orWhere('invoice_number', $orderId)
            ->first();

        if (! $payment) {
            throw new \RuntimeException('Invoice tidak ditemukan: ' . $orderId);
        }

        if (abs((float) ($payload['gross_amount'] ?? 0) - (float) $payment->total_amount) > 1) {
            throw new \RuntimeException('Nominal webhook tidak cocok dengan invoice.');
        }

        $transactionStatus = strtolower((string) ($payload['transaction_status'] ?? ''));
        $fraudStatus = strtolower((string) ($payload['fraud_status'] ?? ''));
        $callback = array_merge((array) $payment->callback_payload, [
            'transaction_status' => $transactionStatus,
            'fraud_status' => $fraudStatus,
            'transaction_id' => $payload['transaction_id'] ?? null,
            'payment_type' => $payload['payment_type'] ?? null,
        ]);

        if (in_array($transactionStatus, ['settlement', 'capture'], true) && $fraudStatus !== 'challenge') {
            $this->markSuccess($payment, [
                'gateway_transaction_id' => $payload['transaction_id'] ?? null,
                'gateway_type' => $payload['payment_type'] ?? null,
                'callback_payload' => $callback,
            ]);

            return ['status' => 'success', 'duplicate' => false];
        }

        if ($transactionStatus === 'settlement' && $fraudStatus === 'challenge') {
            $payment->update(['callback_payload' => $callback]);

            return ['status' => 'challenge', 'duplicate' => false];
        }

        if ($transactionStatus === 'expire') {
            $this->markExpired($payment);

            return ['status' => 'expired', 'duplicate' => false];
        }

        if (in_array($transactionStatus, ['cancel', 'deny'], true)) {
            $this->markFailed($payment, 'Transaksi ditolak atau dibatalkan gateway.');

            return ['status' => 'failed', 'duplicate' => false];
        }

        if ($transactionStatus === 'refund') {
            if ($payment->status === Payment::STATUS_SUCCESS) {
                $payment->update(['status' => Payment::STATUS_REFUNDED]);
            }

            return ['status' => 'refunded', 'duplicate' => false];
        }

        $payment->update(['callback_payload' => $callback]);

        return ['status' => $transactionStatus ?: 'ignored', 'duplicate' => false];
    }

    private function validSignature(array $payload): bool
    {
        $serverKey = (string) config('services.midtrans.server_key');

        if ($serverKey === '') {
            return false;
        }

        $source = (string) ($payload['order_id'] ?? '')
            . (string) ($payload['status_code'] ?? '')
            . (string) ($payload['gross_amount'] ?? '')
            . $serverKey;

        $expected = hash('sha512', $source);
        $received = (string) ($payload['signature_key'] ?? '');

        return $received !== '' && hash_equals($expected, $received);
    }

    private function invoiceNumber(): string
    {
        do {
            $invoice = 'ASM-' . date('Ymd') . '-' . Str::upper(Str::random(8));
        } while (Payment::query()->where('invoice_number', $invoice)->exists());

        return $invoice;
    }
}
