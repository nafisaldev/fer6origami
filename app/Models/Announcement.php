<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Announcement extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'Information' => 'Information',
        'Update' => 'Update',
        'Maintenance' => 'Maintenance',
        'Event' => 'Event',
        'Important' => 'Important',
        'New Release' => 'New Release',
    ];

    public const STATUSES = [
        'draft' => 'Draft',
        'published' => 'Published',
        'scheduled' => 'Scheduled',
        'expired' => 'Expired',
    ];

    protected $fillable = [
        'title',
        'slug',
        'content',
        'category',
        'banner_path',
        'status',
        'published_at',
        'expires_at',
        'is_pinned',
        'is_popup',
        'reads_count',
        'created_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_pinned' => 'boolean',
        'is_popup' => 'boolean',
        'reads_count' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function getBannerUrlAttribute(): ?string
    {
        if (blank($this->banner_path)) {
            return null;
        }

        if (Str::startsWith($this->banner_path, ['http://', 'https://'])) {
            return $this->banner_path;
        }

        return Storage::disk('public')->url($this->banner_path);
    }
}
