<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BroadcastNotification extends Model
{
    use HasFactory;

    protected $table = 'notifications_broadcast';

    public const TYPES = [
        'announcement' => 'Pengumuman',
        'episode' => 'Episode Terbaru',
        'maintenance' => 'Maintenance',
        'system' => 'System Notice',
        'role' => 'Role Notice',
    ];

    protected $fillable = [
        'title',
        'message',
        'type',
        'target_role',
        'action_url',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(UserNotificationRead::class, 'notification_id');
    }
}
