<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REFUNDED = 'refunded';

    public const GATEWAY_MIDTRANS = 'midtrans';
    public const GATEWAY_MANUAL = 'manual';

    protected $fillable = [
        'invoice_number',
        'user_id',
        'plan_id',
        'plan_price_id',
        'amount',
        'discount_amount',
        'total_amount',
        'currency',
        'status',
        'gateway',
        'gateway_order_id',
        'gateway_transaction_id',
        'gateway_type',
        'gateway_payload',
        'callback_payload',
        'checkout_url',
        'proof_path',
        'expired_at',
        'paid_at',
        'verified_by',
        'verified_at',
        'failure_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'gateway_payload' => 'array',
        'callback_payload' => 'array',
        'expired_at' => 'datetime',
        'paid_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'plan_id');
    }

    public function price(): BelongsTo
    {
        return $this->belongsTo(MembershipPlanPrice::class, 'plan_price_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(MembershipSubscription::class, 'payment_id');
    }

    public function voucherRedemptions(): HasMany
    {
        return $this->hasMany(VoucherRedemption::class, 'payment_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }
}
