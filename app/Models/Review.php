<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    public const STATUSES = [
        'published' => 'Published',
        'hidden' => 'Hidden',
    ];

    protected $fillable = [
        'user_id',
        'anime_id',
        'rating',
        'title',
        'content',
        'is_recommended',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_recommended' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
    }
}
