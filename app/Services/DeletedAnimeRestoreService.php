<?php

namespace App\Services;

use App\Models\Anime;
use App\Models\DeletedAnime;
use App\Models\Genre;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class DeletedAnimeRestoreService
{
    public function __construct(private readonly OtakudesuApiService $api)
    {
    }

    public function restore(DeletedAnime $record, bool $removeFromBlacklist = true): array
    {
        $slug = trim((string) $record->slug);

        if ($slug === '') {
            return ['status' => 'skipped', 'message' => 'Slug blacklist kosong.'];
        }

        if (Anime::where('slug', $slug)->exists()) {
            if ($removeFromBlacklist) {
                $record->delete();
                cache()->forget('deleted_anime_identifiers');
            }

            return [
                'status' => 'exists',
                'message' => 'Anime sudah ada di katalog lokal. Entri blacklist dihapus.',
            ];
        }

        try {
            $detail = $this->api->animeDetail($slug);
        } catch (Throwable) {
            $detail = null;
        }

        $title = trim((string) ($detail?->title ?? '')) ?: (string) $record->title;
        $attributes = [
            'title' => $title,
            'slug' => $slug,
            'synopsis' => trim((string) ($detail?->synopsis ?? '')) ?: 'Sinopsis belum tersedia.',
            'poster_path' => $this->mediaPath($detail?->poster_image ?? null),
            'banner_path' => $this->mediaPath($detail?->banner_image ?? null),
            'studio' => $this->studioName($detail?->studio ?? null),
            'year' => $this->year($detail?->year ?? null),
            'status' => $this->status($detail?->status ?? null),
            'rating' => 'PG-13',
            'score' => $this->score($detail?->score ?? 0),
            'type' => Str::limit(trim((string) ($detail?->type ?? '')) ?: 'TV', 50, ''),
            'views' => 0,
        ];

        $anime = DB::transaction(function () use ($attributes, $detail) {
            $created = Anime::create($attributes);
            $genreIds = [];

            foreach (collect($detail?->genres ?? []) as $genre) {
                $name = trim((string) ($genre->name ?? ''));
                if ($name === '') {
                    continue;
                }

                $genreSlug = trim((string) ($genre->id ?? '')) ?: Str::slug($name);
                $genreRecord = Genre::firstOrCreate(
                    ['slug' => $genreSlug],
                    ['name' => $name]
                );
                $genreIds[] = $genreRecord->id;
            }

            if ($genreIds !== []) {
                $created->genres()->sync(array_unique($genreIds));
            }

            return $created;
        });

        if ($removeFromBlacklist) {
            $record->delete();
            cache()->forget('deleted_anime_identifiers');
        }

        ActivityLogger::log('Anime', 'restored', "Memulihkan anime: {$title}", [
            'anime_id' => $anime->id,
            'title' => $title,
            'slug' => $slug,
        ]);

        return [
            'status' => 'restored',
            'anime' => $anime,
            'message' => "Anime {$title} berhasil dipulihkan.",
        ];
    }

    private function mediaPath(mixed $url): ?string
    {
        if (! is_scalar($url)) {
            return null;
        }

        $value = trim((string) $url);

        return $value !== '' && strlen($value) <= 255 ? $value : null;
    }

    private function studioName(mixed $studio): ?string
    {
        if (is_array($studio)) {
            $studio = data_get($studio, 'name') ?? data_get($studio, '0.name');
        }

        if (! is_scalar($studio)) {
            return null;
        }

        $value = trim((string) $studio);
        $value = Str::limit($value, 255, '');

        return $value !== '' ? $value : null;
    }

    private function year(mixed $year): ?int
    {
        $value = (int) $year;

        return $value >= 1970 && $value <= (int) date('Y') + 2 ? $value : null;
    }

    private function status(mixed $status): string
    {
        $value = Str::lower(trim(is_scalar($status) ? (string) $status : ''));

        if (str_contains($value, 'complete')) {
            return 'completed';
        }

        if (str_contains($value, 'upcom')) {
            return 'upcoming';
        }

        return 'ongoing';
    }

    private function score(mixed $score): float
    {
        $value = (float) $score;

        return round(min(10, max(0, $value)), 1);
    }
}
