<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DeletedAnime extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'deleted_by',
    ];

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get all blacklisted / deleted anime slugs and identifier variants.
     *
     * @return array<string>
     */
    public static function blacklistedIdentifiers(): array
    {
        return cache()->remember('deleted_anime_identifiers', 3600, function () {
            $records = static::all(['slug', 'title']);
            $identifiers = [];

            foreach ($records as $record) {
                if ($record->slug) {
                    $identifiers[] = strtolower(trim($record->slug));
                    $identifiers[] = 'db_' . strtolower(trim($record->slug));
                }
                if ($record->title) {
                    $identifiers[] = strtolower(trim($record->title));
                    $identifiers[] = Str::slug($record->title);
                }
            }

            return array_unique(array_filter($identifiers));
        });
    }

    public static function isBlacklisted(?string $identifier): bool
    {
        if (blank($identifier)) {
            return false;
        }

        $clean = strtolower(trim($identifier));
        $slugClean = Str::slug($identifier);

        $list = static::blacklistedIdentifiers();

        return in_array($clean, $list, true) || in_array($slugClean, $list, true);
    }

    public static function blacklistAnime(string $title, string $slug, ?int $userId = null): void
    {
        static::firstOrCreate([
            'slug' => $slug,
        ], [
            'title' => $title,
            'deleted_by' => $userId,
        ]);

        cache()->forget('deleted_anime_identifiers');
    }
}
