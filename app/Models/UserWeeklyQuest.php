<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWeeklyQuest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'week_start',
        'claimed_at',
    ];

    protected $casts = [
        'week_start' => 'date',
        'claimed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
