<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    public const TYPES = [
        'broken_link' => 'Link Streaming Rusak',
        'subtitle_issue' => 'Subtitle Bermasalah',
        'inappropriate_content' => 'Konten Tidak Sesuai',
        'comment_report' => 'Komentar Spam/Toxic',
        'other' => 'Lainnya',
    ];

    public const STATUSES = [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'resolved' => 'Resolved',
        'rejected' => 'Rejected',
    ];

    protected $fillable = [
        'user_id',
        'reportable_type',
        'reportable_id',
        'type',
        'description',
        'status',
        'admin_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
