<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLES = [
        'super_admin' => 'Super Admin',
        'admin' => 'Admin',
        'anime_manager' => 'Anime Manager',
        'content_editor' => 'Content Editor',
        'moderator' => 'Moderator',
        'user' => 'User',
    ];

    public const STATUSES = [
        'active' => 'Active',
        'suspended' => 'Suspended',
        'banned' => 'Banned',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'role_id',
        'status',
        'banned_reason',
        'banned_until',
        'avatar_path',
        'last_login_at',
        'last_login_ip',
        'xp',
        'xp_manual',
        'bio',
        'favorite_anime',
        'badges',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'banned_until' => 'datetime',
        'last_login_at' => 'datetime',
        'xp' => 'integer',
        'xp_manual' => 'boolean',
        'badges' => 'array',
    ];

    public function roleModel(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }

    public function watchHistories(): HasMany
    {
        return $this->hasMany(WatchHistory::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function weeklyQuests(): HasMany
    {
        return $this->hasMany(UserWeeklyQuest::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function membership(): HasOne
    {
        return $this->hasOne(UserMembership::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(UserMembership::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function membershipHistories(): HasMany
    {
        return $this->hasMany(MembershipHistory::class);
    }

    public function membershipNotifications(): HasMany
    {
        return $this->hasMany(MembershipNotification::class);
    }

    public function membershipTier(): string
    {
        return app(\App\Services\MembershipService::class)->tier($this);
    }

    public function hasMembership(string $minimum): bool
    {
        return app(\App\Services\MembershipService::class)->hasAtLeast($this, $minimum);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin'
            || ($this->roleModel && $this->roleModel->slug === 'super_admin');
    }

    public function isAdmin(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $adminRoles = ['admin', 'super_admin', 'anime_manager', 'content_editor', 'moderator'];
        if (in_array($this->role, $adminRoles, true)) {
            return true;
        }

        return $this->roleModel && in_array($this->roleModel->slug, $adminRoles, true);
    }

    public function hasRole(string|array $roles): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $roles = (array) $roles;
        $currentRole = $this->roleModel ? $this->roleModel->slug : $this->role;

        return in_array($currentRole, $roles, true);
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->roleModel) {
            return $this->roleModel->hasPermission($permissionSlug);
        }

        // Default fallbacks for legacy string roles if roleModel not linked yet
        if ($this->role === 'admin') {
            return true;
        }

        return false;
    }

    public function isBanned(): bool
    {
        if ($this->status === 'banned') {
            return true;
        }

        if ($this->status === 'suspended' && $this->banned_until && $this->banned_until->isFuture()) {
            return true;
        }

        return false;
    }

    public function getAvatarUrlAttribute(): string
    {
        if (blank($this->avatar_path)) {
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=e53535&color=fff';
        }

        if (Str::startsWith($this->avatar_path, ['http://', 'https://'])) {
            return $this->avatar_path;
        }

        return Storage::disk('public')->url($this->avatar_path);
    }

    public function getLevelAttribute(): int
    {
        return \App\Services\UserLevelService::getLevel($this->xp ?? 0);
    }

    public function getLevelDetailsAttribute(): array
    {
        return \App\Services\UserLevelService::getLevelDetails($this->xp ?? 0);
    }

    public function getBadgesListAttribute(): array
    {
        return \App\Services\UserLevelService::getBadges($this);
    }

    public function awardXp(int $amount, ?string $reason = null): void
    {
        if ($amount <= 0) {
            return;
        }

        $multiplier = $this->xp_manual
            ? 1.0
            : app(\App\Services\MembershipService::class)->xpMultiplier($this);
        $this->increment('xp', max(1, (int) round($amount * $multiplier)));
    }

    public function syncXp(): int
    {
        return \App\Services\UserLevelService::syncUserXp($this);
    }
}
