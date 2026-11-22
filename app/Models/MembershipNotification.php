<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'membership_id',
        'type',
        'title',
        'message',
        'action_url',
        'channel',
        'metadata',
        'sent_at',
        'read_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(UserMembership::class, 'membership_id');
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}
