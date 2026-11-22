<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = [
        'anime_id',
        'title',
        'slug',
        'episode_number',
        'synopsis',
        'video_url',
        'servers',
        'subtitles',
        'qualities',
        'duration_minutes',
        'aired_at',
        'is_published',
        'minimum_plan',
        'early_access_until',
    ];

    protected $casts = [
        'aired_at' => 'datetime',
        'early_access_until' => 'datetime',
        'duration_minutes' => 'integer',
        'episode_number' => 'integer',
        'is_published' => 'boolean',
        'servers' => 'array',
        'subtitles' => 'array',
        'qualities' => 'array',
    ];

    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function watchHistories(): HasMany
    {
        return $this->hasMany(WatchHistory::class);
    }

    public function getStreamUrlAttribute(): string
    {
        return $this->video_url ?: 'https://samplelib.com/lib/preview/mp4/sample-5s.mp4';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
