<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipPlan extends Model
{
    use HasFactory;

    public const FREE = 'free';
    public const PREMIUM = 'premium';
    public const VIP = 'vip';

    protected $fillable = [
        'slug',
        'name',
        'tagline',
        'description',
        'benefits',
        'sort_order',
        'is_active',
        'is_default',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'benefits' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(MembershipPlanPrice::class, 'plan_id');
    }

    public function activePrices(): HasMany
    {
        return $this->prices()->where('is_active', true)->orderBy('duration_days');
    }

    public function userMemberships(): HasMany
    {
        return $this->hasMany(UserMembership::class, 'plan_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
