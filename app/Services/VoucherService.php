<?php

namespace App\Services;

use App\Models\MembershipPlan;
use App\Models\MembershipPlanPrice;
use App\Models\Payment;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherRedemption;
use Illuminate\Support\Facades\DB;

class VoucherService
{
    public function validate(?string $code, MembershipPlanPrice $price, User $user): array
    {
        if (blank($code)) {
            return ['voucher' => null, 'discount' => 0];
        }

        $voucher = Voucher::query()
            ->where('code', strtoupper(trim($code)))
            ->where('is_active', true)
            ->first();

        if (! $voucher) {
            return ['voucher' => null, 'discount' => 0, 'error' => 'Kode voucher tidak ditemukan atau tidak aktif.'];
        }

        if ($voucher->valid_from && $voucher->valid_from->isFuture()) {
            return ['voucher' => null, 'discount' => 0, 'error' => 'Voucher belum berlaku.'];
        }

        if ($voucher->valid_until && $voucher->valid_until->isPast()) {
            return ['voucher' => null, 'discount' => 0, 'error' => 'Voucher sudah kedaluwarsa.'];
        }

        if ($voucher->usage_limit !== null && $voucher->used_count >= $voucher->usage_limit) {
            return ['voucher' => null, 'discount' => 0, 'error' => 'Kuota voucher sudah habis.'];
        }

        $userUses = VoucherRedemption::query()
            ->where('voucher_id', $voucher->id)
            ->where('user_id', $user->id)
            ->count();

        if ($userUses >= $voucher->per_user_limit) {
            return ['voucher' => null, 'discount' => 0, 'error' => 'Anda sudah memakai voucher ini.'];
        }

        $planSlug = $price->plan->slug ?? null;

        if ($voucher->applies_to_plans && ! in_array($planSlug, $voucher->applies_to_plans, true)) {
            return ['voucher' => null, 'discount' => 0, 'error' => 'Voucher tidak berlaku untuk paket ini.'];
        }

        if ($voucher->applies_to_durations && ! in_array((int) $price->duration_days, array_map('intval', $voucher->applies_to_durations), true)) {
            return ['voucher' => null, 'discount' => 0, 'error' => 'Voucher tidak berlaku untuk durasi ini.'];
        }

        if ($voucher->min_purchase && (float) $price->price < (float) $voucher->min_purchase) {
            return ['voucher' => null, 'discount' => 0, 'error' => 'Minimum pembelian Rp ' . number_format((float) $voucher->min_purchase, 0, ',', '.') . '.'];
        }

        $discount = $voucher->type === Voucher::TYPE_PERCENT
            ? (float) $price->price * ((float) $voucher->value / 100)
            : (float) $voucher->value;

        if ($voucher->max_discount) {
            $discount = min($discount, (float) $voucher->max_discount);
        }

        $discount = min($discount, (float) $price->price);

        return [
            'voucher' => $voucher,
            'discount' => round($discount, 2),
        ];
    }

    public function redeem(Voucher $voucher, User $user, Payment $payment, float $discount): VoucherRedemption
    {
        return DB::transaction(function () use ($voucher, $user, $payment, $discount) {
            $locked = Voucher::query()->where('id', $voucher->id)->lockForUpdate()->firstOrFail();

            if ($locked->usage_limit !== null && $locked->used_count >= $locked->usage_limit) {
                abort(422, 'Kuota voucher sudah habis.');
            }

            $locked->increment('used_count');

            return VoucherRedemption::create([
                'voucher_id' => $locked->id,
                'user_id' => $user->id,
                'payment_id' => $payment->id,
                'discount_amount' => $discount,
                'used_at' => now(),
            ]);
        });
    }

    public function plansOptions(): array
    {
        return MembershipPlan::query()->orderBy('sort_order')->pluck('name', 'slug')->all();
    }
}
