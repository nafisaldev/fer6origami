<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Anime extends Model
{
    use HasFactory;

    public const STATUSES = [
        'ongoing' => 'Ongoing',
        'completed' => 'Completed',
        'upcoming' => 'Upcoming',
    ];

    public const RATINGS = [
        'G' => 'G',
        'PG' => 'PG',
        'PG-13' => 'PG-13',
        'R-17' => 'R-17',
    ];

    public const DAYS = [
        'monday' => 'Senin (Monday)',
        'tuesday' => 'Selasa (Tuesday)',
        'wednesday' => 'Rabu (Wednesday)',
        'thursday' => 'Kamis (Thursday)',
        'friday' => 'Jumat (Friday)',
        'saturday' => 'Sabtu (Saturday)',
        'sunday' => 'Minggu (Sunday)',
    ];

    protected $fillable = [
        'title',
        'slug',
        'synopsis',
        'poster_path',
        'banner_path',
        'studio',
        'studio_id',
        'year',
        'status',
        'release_day',
        'rating',
        'score',
        'type',
        'views',
        'is_trending',
        'is_popular',
        'is_featured',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'year' => 'integer',
        'score' => 'decimal:1',
        'views' => 'integer',
        'is_trending' => 'boolean',
        'is_popular' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class)->withTimestamps();
    }

    public function studioModel(): BelongsTo
    {
        return $this->belongsTo(Studio::class, 'studio_id');
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
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

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $nested) use ($search) {
                    $nested
                        ->where('title', 'like', '%' . $search . '%')
                        ->orWhere('studio', 'like', '%' . $search . '%')
                        ->orWhere('synopsis', 'like', '%' . $search . '%');
                });
            })
            ->when($filters['genre'] ?? null, function (Builder $query, string $genre) {
                $query->whereHas('genres', fn (Builder $genreQuery) => $genreQuery->where('slug', $genre));
            })
            ->when($filters['year'] ?? null, fn (Builder $query, string $year) => $query->where('year', $year))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['rating'] ?? null, fn (Builder $query, string $rating) => $query->where('rating', $rating))
            ->when($filters['release_day'] ?? null, fn (Builder $query, string $day) => $query->where('release_day', $day));
    }

    public function getPosterImageAttribute(): string
    {
        return $this->resolveMediaUrl(
            $this->poster_path,
            'https://placehold.co/600x900/09090b/f97316?text=' . urlencode($this->title)
        );
    }

    public function getBannerImageAttribute(): string
    {
        return $this->resolveMediaUrl(
            $this->banner_path,
            'https://placehold.co/1400x700/0f172a/e11d48?text=' . urlencode($this->title)
        );
    }

    private function resolveMediaUrl(?string $value, string $fallback): string
    {
        if (blank($value)) {
            return $fallback;
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        if (Str::startsWith($value, '/')) {
            return url($value);
        }

        return Storage::disk('public')->url($value);
    }

    public function getRouteIdAttribute(): string
    {
        return $this->slug ?: ('db_' . $this->id);
    }

    public function toCardFluent(): \Illuminate\Support\Fluent
    {
        return new \Illuminate\Support\Fluent([
            'id' => 'db_' . $this->id,
            'route_id' => $this->route_id,
            'title' => $this->title,
            'poster_image' => $this->poster_image,
            'banner_image' => $this->banner_image,
            'synopsis' => $this->synopsis,
            'studio' => $this->studio ?: ($this->studioModel?->name ?? 'Fer6origami'),
            'year' => $this->year,
            'status' => $this->status ?: 'Available',
            'score' => (float) $this->score,
            'type' => $this->type ?: 'TV',
            'genres' => $this->genres->map(fn ($g) => new \Illuminate\Support\Fluent(['id' => $g->slug, 'name' => $g->name])),
            'episode_label' => $this->episodes()->exists() ? 'Eps ' . $this->episodes()->count() : null,
            'episodes_count' => $this->episodes()->count(),
        ]);
    }

    public function toDetailFluent(): \Illuminate\Support\Fluent
    {
        $episodes = $this->episodes()
            ->where('is_published', true)
            ->orderBy('episode_number')
            ->get()
            ->map(function ($ep) {
                return new \Illuminate\Support\Fluent([
                    'id' => 'db_ep_' . $ep->id,
                    'route_id' => 'db_ep_' . $ep->id,
                    'anime_id' => $this->route_id,
                    'anime_route_id' => $this->route_id,
                    'title' => $ep->title ?: ('Episode ' . $ep->episode_number),
                    'episode_number' => $ep->episode_number,
                    'uploaded_on' => $ep->aired_at ? $ep->aired_at->diffForHumans() : 'Available',
                    'synopsis' => 'Stream online in high quality on Fer6origami.',
                    'stream_url' => $ep->stream_url,
                    'servers' => $ep->servers,
                    'subtitles' => $ep->subtitles,
                    'qualities' => $ep->qualities,
                ]);
            });

        return new \Illuminate\Support\Fluent([
            'id' => 'db_' . $this->id,
            'route_id' => $this->route_id,
            'title' => $this->title,
            'poster_image' => $this->poster_image,
            'banner_image' => $this->banner_image,
            'synopsis' => $this->synopsis,
            'japanese' => $this->title,
            'score' => (float) $this->score,
            'producer' => $this->studio,
            'type' => $this->type ?: 'TV',
            'status' => $this->status ?: 'Available',
            'total_episode' => $this->episodes()->count(),
            'duration' => '24 min',
            'release_date' => $this->year ? (string) $this->year : 'Recent',
            'year' => (string) $this->year,
            'studio' => $this->studio ?: ($this->studioModel?->name ?? 'Fer6origami'),
            'views' => $this->views ?? 0,
            'genres' => $this->genres->map(fn ($g) => new \Illuminate\Support\Fluent(['id' => $g->slug, 'name' => $g->name])),
            'episodes' => $episodes,
            'is_db' => true,
        ]);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
