<?php

namespace App\Services;

use App\Models\ScraperSnapshot;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Throwable;

class OtakudesuApiService
{
    public function homeData(): Fluent
    {
        $payload = $this->getCached('home', 'home');
        $home = data_get($payload, 'home', []);

        $ongoing = collect(data_get($home, 'on_going', []))
            ->map(fn (array $item) => $this->normalizeAnimeSummary($item, 'ongoing'))
            ->map(fn (Fluent $anime) => $this->enrichAnimeImages($anime));

        $complete = collect(data_get($home, 'complete', []))
            ->map(fn (array $item) => $this->normalizeAnimeSummary($item, 'completed'))
            ->map(fn (Fluent $anime) => $this->enrichAnimeImages($anime));

        if ($ongoing->isEmpty()) {
            $ongoing = collect(data_get($this->getCached('ongoing:1', 'ongoing/page/1'), 'animeList', []))
                ->map(fn (array $item) => $this->normalizeAnimeSummary($item, 'ongoing'))
                ->map(fn (Fluent $anime) => $this->enrichAnimeImages($anime))
                ->values();
        }

        if ($complete->isEmpty()) {
            $complete = collect(data_get($this->getCached('complete:1', 'complete/page/1'), 'animeList', []))
                ->map(fn (array $item) => $this->normalizeAnimeSummary($item, 'completed'))
                ->map(fn (Fluent $anime) => $this->enrichAnimeImages($anime))
                ->values();
        }

        return new Fluent([
            'trending' => $ongoing->take(6),
            'popular' => $complete->sortByDesc('score')->take(8)->values(),
            'latestEpisodes' => $this->latestEpisodesFromOngoing($ongoing->take(6)),
            'ongoing' => $ongoing,
            'complete' => $complete,
        ]);
    }

    public function homeApi(): array
    {
        $payload = $this->getCached('home', 'home');

        return [
            'status' => data_get($payload, 'status', 'success'),
            'baseUrl' => data_get($payload, 'baseUrl', 'https://otakudesu.moe/'),
            'home' => [
                'on_going' => array_values(data_get($payload, 'home.on_going', [])),
                'complete' => array_values(data_get($payload, 'home.complete', [])),
            ],
        ];
    }

    public function getGenres(): Collection
    {
        $payload = $this->getCached('genres', 'genres');

        return collect(data_get($payload, 'genreList', []))
            ->map(fn (array $item) => new Fluent([
                'id' => $this->sanitizeId((string) data_get($item, 'id', '')),
                'name' => data_get($item, 'genre_name', 'Unknown'),
                'image' => data_get($item, 'image_link'),
            ]));
    }

    public function catalog(array $filters, int $page = 1): Fluent
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $genre = trim((string) ($filters['genre'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));
        $year = trim((string) ($filters['year'] ?? ''));
        $minScore = trim((string) ($filters['rating'] ?? ''));
        $source = 'all';
        $rawItems = collect();

        if ($search !== '') {
            $source = 'search';
            $rawItems = collect(data_get($this->searchApi($search), 'search_results', []));
        } elseif ($genre !== '') {
            $source = $this->shouldUseFastGenrePath($year, $minScore) ? 'genre-fast' : 'genre-all';
            $payload = $source === 'genre-fast'
                ? $this->getCached("genre-catalog:{$genre}:page:{$page}", "genres/{$genre}/catalog/page/{$page}")
                : $this->getCached("genre-catalog:{$genre}", "genres/{$genre}/catalog");
            $rawItems = collect(data_get($payload, 'animeList', []));
        } elseif ($status === 'completed') {
            $source = 'completed';
            $payload = $this->getCached("complete:{$page}", "complete/page/{$page}");
            $rawItems = collect(data_get($payload, 'animeList', []));
        } elseif ($status === 'ongoing') {
            $source = 'ongoing';
            $payload = $this->getCached("ongoing:{$page}", "ongoing/page/{$page}");
            $rawItems = collect(data_get($payload, 'animeList', []));
        } else {
            $payload = $this->getCached('anime-list', 'anime-list');
            $rawItems = $this->mergeSnapshotAnimeSummaries(collect(data_get($payload, 'animeList', [])));
        }

        $items = $rawItems
            ->map(fn (array $item) => $this->normalizeAnimeSummary($item, data_get($item, 'status', $status)))
            ->filter(function (Fluent $anime) use ($year, $minScore) {
                if ($year !== '' && (string) $anime->year !== $year) {
                    return false;
                }

                if ($minScore !== '' && (float) $anime->score < (float) $minScore) {
                    return false;
                }

                return true;
            })
            ->values();

        $perPage = $this->catalogPerPage($source);
        $pageItems = in_array($source, ['all', 'genre-all'], true)
            ? $items->forPage($page, $perPage)->values()
            : $items;
        if ($source === 'genre-fast') {
            $pageItems = $items->take($perPage)->values();
        }
        $pageItems = $pageItems
            ->map(fn (Fluent $anime) => $this->enrichAnimeImages($anime, $source !== 'genre-fast'))
            ->values();
        $paginator = new LengthAwarePaginator(
            $pageItems,
            in_array($source, ['all', 'genre-all'], true)
                ? $items->count()
                : ((($page - 1) * $perPage) + $items->count() + ($this->sourceHasMore($source, $rawItems->count()) ? 1 : 0)),
            $perPage,
            $page,
            [
                'path' => route('anime.index'),
                'query' => request()->query(),
            ]
        );

        return new Fluent([
            'items' => collect($paginator->items()),
            'pagination' => new Fluent([
                'paginator' => $paginator,
                'current_page' => $page,
                'previous_page' => $page > 1 ? $page - 1 : null,
                'next_page' => in_array($source, ['all', 'genre-all'], true)
                    ? ($paginator->hasMorePages() ? $page + 1 : null)
                    : ($this->sourceHasMore($source, $rawItems->count()) ? $page + 1 : null),
            ]),
        ]);
    }

    public function animeDetail(string $animeId): Fluent
    {
        $animeId = $this->sanitizeId($animeId);
        $payload = $this->getCached('anime:' . md5($animeId), 'anime/' . $animeId);

        $episodes = collect(data_get($payload, 'episode_list', []))
            ->reject(fn (array $item) => str_contains(Str::lower((string) data_get($item, 'title', '')), 'masih kosong'))
            ->map(fn (array $item) => $this->normalizeEpisodeSummary($item, $animeId))
            ->values();

        $title = data_get($payload, 'title');
        if (! $title || $title === 'Unknown Anime') {
            $title = (string) Str::of($animeId)->replace(['-sub-indo', '-subtitle-indonesia', '-'], [' ', ' ', ' '])->trim()->title();
        }

        return new Fluent([
            'id' => $animeId,
            'route_id' => $this->encodeId($animeId),
            'title' => $title,
            'poster_image' => $this->posterImage(data_get($payload, 'thumb')),
            'banner_image' => $this->bannerImage(data_get($payload, 'thumb')),
            'synopsis' => data_get($payload, 'synopsis', 'No synopsis available yet.'),
            'japanese' => data_get($payload, 'japanase'),
            'score' => (float) data_get($payload, 'score', 0),
            'producer' => data_get($payload, 'producer'),
            'type' => data_get($payload, 'type'),
            'status' => data_get($payload, 'status'),
            'total_episode' => data_get($payload, 'total_episode'),
            'duration' => data_get($payload, 'duration'),
            'release_date' => data_get($payload, 'release_date'),
            'year' => $this->extractYear((string) data_get($payload, 'release_date')),
            'studio' => data_get($payload, 'studio'),
            'views' => 0,
            'genres' => collect(data_get($payload, 'genre_list', []))
                ->map(fn (array $genre) => new Fluent([
                    'id' => $this->sanitizeId((string) data_get($genre, 'genre_id', '')),
                    'name' => data_get($genre, 'genre_name', 'Unknown'),
                ]))
                ->values(),
            'episodes' => $episodes,
        ]);
    }

    public function completeAnimeApi(int $page = 1): array
    {
        $page = max(1, $page);
        $payload = $this->getCached("complete:{$page}", "complete/page/{$page}");

        return [
            'status' => data_get($payload, 'status', 'success'),
            'baseUrl' => data_get($payload, 'baseUrl', 'https://otakudesu.moe/complete-anime/'),
            'animeList' => array_values(data_get($payload, 'animeList', [])),
        ];
    }

    public function ongoingAnimeApi(int $page = 1): array
    {
        $page = max(1, $page);
        $payload = $this->getCached("ongoing:{$page}", "ongoing/page/{$page}");

        return [
            'status' => data_get($payload, 'status', 'success'),
            'baseUrl' => data_get($payload, 'baseUrl', 'https://otakudesu.moe/ongoing-anime/'),
            'animeList' => array_values(data_get($payload, 'animeList', [])),
        ];
    }

    public function animeListApi(): array
    {
        $payload = $this->getCached('anime-list', 'anime-list');

        return [
            'status' => data_get($payload, 'status', 'success'),
            'baseUrl' => data_get($payload, 'baseUrl', 'https://otakudesu.moe/anime-list/'),
            'animeList' => $this->hydrateApiItems(array_values(data_get($payload, 'animeList', []))),
        ];
    }

    public function genresApi(): array
    {
        $payload = $this->getCached('genres', 'genres');

        return [
            'status' => data_get($payload, 'status', 'success'),
            'genreList' => array_values(data_get($payload, 'genreList', [])),
        ];
    }

    public function genreAnimeApi(string $genreId, int $page = 1): array
    {
        $page = max(1, $page);
        $genreId = $this->sanitizeId($genreId);
        $payload = $this->getCached("genres:{$genreId}:page:{$page}", "genres/{$genreId}/page/{$page}");

        return [
            'status' => data_get($payload, 'status', 'success'),
            'genreId' => $genreId,
            'page' => $page,
            'animeList' => array_values(data_get($payload, 'animeList', [])),
        ];
    }

    public function searchApi(string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return [
                'status' => 'success',
                'query' => '',
                'search_results' => [],
            ];
        }

        $payload = $this->getCached('search:' . md5($query), 'search/' . rawurlencode($query));
        $results = array_values(data_get($payload, 'search_results', []));

        if ($results === []) {
            $results = $this->searchLocalAnimeList($query);
        }

        return [
            'status' => data_get($payload, 'status', 'success'),
            'query' => $query,
            'search_results' => $this->hydrateApiItems($results),
        ];
    }

    public function animeDetailApi(string $animeId): array
    {
        $animeId = $this->sanitizeId($animeId);
        $payload = $this->getCached('anime:' . md5($animeId), 'anime/' . $animeId);

        return [
            'status' => data_get($payload, 'status', 'success'),
            'animeId' => $animeId,
            'data' => $payload,
        ];
    }

    public function episodeDetailApi(string $episodeId): array
    {
        $episodeId = $this->sanitizeId($episodeId);
        $payload = $this->getCached('episode:' . md5($episodeId), 'eps/' . $episodeId);

        return [
            'status' => data_get($payload, 'status', 'success'),
            'episodeId' => $episodeId,
            'data' => $payload,
        ];
    }

    public function hasAnimeDetailData(array $payload): bool
    {
        if ($payload === []) {
            return false;
        }

        $title = trim((string) data_get($payload, 'title', ''));
        $hasContent = ! empty(data_get($payload, 'episode_list', []))
            || ! empty(data_get($payload, 'genre_list', []))
            || trim((string) data_get($payload, 'synopsis', '')) !== ''
            || trim((string) data_get($payload, 'thumb', '')) !== '';

        return ! $this->isPlaceholderDetailTitle($title) && $hasContent;
    }

    public function hasEpisodeDetailData(string $episodeId, array $payload): bool
    {
        if ($payload === []) {
            return false;
        }

        $title = trim((string) data_get($payload, 'title', ''));

        if ($this->isPlaceholderDetailTitle($title) || $this->isGeneratedEpisodeTitle($episodeId, $title)) {
            return false;
        }

        return true;
    }

    private function isPlaceholderDetailTitle(string $title): bool
    {
        $normalized = Str::lower(trim($title));

        return in_array($normalized, [
            '',
            'otakudesu official',
            'otakudesu',
            '404',
            '404 not found',
            'page not found',
            'halaman tidak ditemukan',
            'anime',
        ], true);
    }

    private function isGeneratedEpisodeTitle(string $episodeId, string $title): bool
    {
        $generated = Str::of($episodeId)->replace('-', ' ')->title()->toString();

        return Str::lower(trim($title)) === Str::lower(trim($generated));
    }

    public function episodeDetail(string $episodeId, string $animeId, ?string $preferredQuality = null, ?Fluent $anime = null, ?string $preferredServer = null): Fluent
    {
        $episodeId = $this->sanitizeId($episodeId);
        $anime = $anime ?: $this->animeDetail($animeId);

        if ($episodeId === $this->sanitizeId($animeId) && ! empty($anime->episodes)) {
            $firstEp = collect($anime->episodes)->first();
            if ($firstEp && ! empty($firstEp->id)) {
                $episodeId = $this->sanitizeId($firstEp->id);
            }
        }

        $payload = $this->getCached('episode:' . md5($episodeId), 'eps/' . $episodeId);
        $qualities = collect(data_get($payload, 'qualities', []));
        $streamOptions = collect(data_get($payload, 'stream_options', []));

        if ($qualities->isEmpty()) {
            $qualities = collect([
                data_get($payload, 'quality.low_quality'),
                data_get($payload, 'quality.medium_quality'),
                data_get($payload, 'quality.high_quality'),
            ])->filter()->values();
        }

        $selectedStream = $this->selectStreamOption($streamOptions, $preferredQuality, $preferredServer);

        $rawStreamUrl = data_get($selectedStream, 'url', data_get($payload, 'link_stream'));
        // Reject dummy/sample video URLs
        $isValidStreamUrl = $rawStreamUrl
            && ! str_contains((string) $rawStreamUrl, 'samplelib')
            && trim((string) $rawStreamUrl) !== '';

        return new Fluent([
            'id' => $episodeId,
            'route_id' => $this->encodeId($episodeId),
            'title' => data_get($payload, 'title', 'Episode'),
            'stream_url' => $isValidStreamUrl ? $rawStreamUrl : null,
            'stream_quality' => data_get($selectedStream, 'quality', data_get($payload, 'stream_quality')),
            'stream_label' => data_get($selectedStream, 'label'),
            'stream_options' => $streamOptions,
            'qualities' => $qualities,
            'mirror_groups' => collect([
                data_get($payload, 'mirror1'),
                data_get($payload, 'mirror2'),
                data_get($payload, 'mirror3'),
            ])->filter()->values(),
            'anime' => $anime,
            'episode_number' => $this->extractEpisodeNumber((string) data_get($payload, 'title', '')),
            'synopsis' => 'Enjoy watching this episode in high quality.',
        ]);
    }

    public function relatedAnime(Fluent $anime, int $limit = 4): Collection
    {
        $firstGenre = $anime->genres->first();

        if (! $firstGenre) {
            return $this->homeData()->popular->take($limit);
        }

        $payload = $this->getCached("genre:{$firstGenre->id}:1", "genres/{$firstGenre->id}/page/1");

        return collect(data_get($payload, 'animeList', []))
            ->map(fn (array $item) => $this->normalizeAnimeSummary($item))
            ->map(fn (Fluent $item) => $this->enrichAnimeImages($item))
            ->reject(fn (Fluent $item) => $item->id === $anime->id)
            ->take($limit)
            ->values();
    }

    public function scoreOptions(): array
    {
        return [
            '9' => '9.0+',
            '8' => '8.0+',
            '7' => '7.0+',
        ];
    }

    public function yearOptions(): Collection
    {
        $currentYear = (int) now()->format('Y');

        return collect(range($currentYear, $currentYear - 10));
    }

    public function scraperHealth(): array
    {
        $apiUrl = $this->apiBaseUrl();
        $sourceUrl = rtrim((string) config('services.otakudesu.source_url', 'https://otakudesu.best'), '/');

        return [
            'configured' => [
                'api_base_url' => $apiUrl,
                'source_url' => $sourceUrl,
                'proxy_disabled' => (bool) config('services.otakudesu.disable_proxy', true),
            ],
            'checks' => [
                'api' => $apiUrl !== ''
                    ? $this->probe(fn () => $this->client()->get('home'))
                    : [
                        'ok' => true,
                        'status' => 200,
                        'message' => 'connected (built-in direct scraper)',
                    ],
                'source' => $this->probe(fn () => $this->sourceClient()->get('/')),
            ],
            'cache' => [
                'unavailable' => (bool) Cache::get('otakudesu:unavailable', false),
                'source_unavailable' => (bool) Cache::get('otakudesu:source_unavailable', false),
                'last_error' => Cache::get('otakudesu:last_error'),
            ],
        ];
    }

    public function encodeId(string $id): string
    {
        return rawurlencode($this->sanitizeId($id));
    }

    public function decodeId(string $id): string
    {
        return $this->sanitizeId(rawurldecode($id));
    }

    private function latestEpisodesFromOngoing(Collection $ongoing): Collection
    {
        return $ongoing
            ->map(function (Fluent $anime) {
                $epLabel = $anime->episode_label ?: 'Latest Episode';
                $epNum = $this->extractEpisodeNumber((string) $epLabel);

                $cacheKey = 'otakudesu:v4:anime:' . md5((string) $anime->id);
                $cachedPayload = Cache::get($cacheKey);

                if (is_array($cachedPayload) && ! empty($cachedPayload['episode_list'])) {
                    $firstEp = $cachedPayload['episode_list'][0];
                    $epId = $this->sanitizeId((string) data_get($firstEp, 'id', ''));

                    return new Fluent([
                        'title' => data_get($firstEp, 'title', $anime->title . ' ' . $epLabel),
                        'episode_number' => $this->extractEpisodeNumber((string) data_get($firstEp, 'title', '')),
                        'uploaded_on' => data_get($firstEp, 'uploaded_on', $anime->uploaded_on),
                        'route_id' => $this->encodeId($epId),
                        'anime_route_id' => $anime->route_id,
                        'anime' => $anime,
                        'synopsis' => 'Latest episode of ' . $anime->title . '.',
                    ]);
                }

                return new Fluent([
                    'title' => $anime->title . ' ' . $epLabel,
                    'episode_number' => $epNum,
                    'uploaded_on' => $anime->uploaded_on ?: 'New',
                    'route_id' => $anime->route_id,
                    'anime_route_id' => $anime->route_id,
                    'anime' => $anime,
                    'synopsis' => 'Latest episode of ' . $anime->title . '.',
                ]);
            })
            ->filter()
            ->values();
    }

    private function fallbackScore(string $id, string $title = ''): float
    {
        $hash = abs(crc32($id . ':' . $title));
        $offset = ($hash % 125) / 100.0;
        return round(7.4 + $offset, 1);
    }

    private function normalizeType(?string $rawType, ?string $status = null): string
    {
        $raw = trim((string) $rawType);
        $lower = strtolower($raw);

        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        if (in_array($lower, $days, true) || is_numeric($raw) || $lower === '' || $lower === 'anime') {
            return 'TV';
        }

        if (str_contains($lower, 'tv')) {
            return 'TV';
        }
        if (str_contains($lower, 'movie')) {
            return 'Movie';
        }
        if (str_contains($lower, 'ova')) {
            return 'OVA';
        }
        if (str_contains($lower, 'ona')) {
            return 'ONA';
        }
        if (str_contains($lower, 'special')) {
            return 'Special';
        }

        return Str::title($raw);
    }

    private function normalizeAnimeSummary(array $item, ?string $fallbackStatus = null): Fluent
    {
        $id = $this->sanitizeId((string) data_get($item, 'id', ''));

        $title = data_get($item, 'title') ?? data_get($item, 'anime_name');
        if (! $title || $title === 'Unknown Anime') {
            $title = (string) Str::of($id)->replace(['-sub-indo', '-subtitle-indonesia', '-'], [' ', ' ', ' '])->trim()->title();
        }

        $rawScore = data_get($item, 'score');
        $rawDayOrScore = data_get($item, 'day_updated');
        $score = (float) $rawScore;
        if ($score <= 0.0 && is_numeric($rawDayOrScore)) {
            $score = (float) $rawDayOrScore;
        }

        $rawType = data_get($item, 'type') ?? $rawDayOrScore;
        $type = $this->normalizeType($rawType, data_get($item, 'status', $fallbackStatus));

        return new Fluent([
            'id' => $id,
            'route_id' => $this->encodeId($id),
            'title' => $title,
            'poster_image' => $this->posterImage(data_get($item, 'thumb')),
            'banner_image' => $this->bannerImage(data_get($item, 'thumb')),
            'synopsis' => data_get($item, 'episode', 'Anime details available on the overview page.'),
            'studio' => data_get($item, 'studio'),
            'year' => $this->extractYear((string) (data_get($item, 'release_date') ?? data_get($item, 'uploaded_on', ''))),
            'status' => $this->normalizeStatus(data_get($item, 'status', $fallbackStatus)),
            'score' => $score > 0 ? $score : $this->fallbackScore($id, $title),
            'type' => $type,
            'day_updated' => !is_numeric($rawDayOrScore) ? $rawDayOrScore : null,
            'views' => 0,
            'genres' => collect(data_get($item, 'genre_list', []))
                ->map(fn (array $genre) => new Fluent([
                    'id' => $this->sanitizeId((string) (data_get($genre, 'genre_id') ?? data_get($genre, 'id', ''))),
                    'name' => data_get($genre, 'genre_name') ?? data_get($genre, 'genre_title') ?? 'Unknown',
                ]))
                ->values(),
            'episode_label' => data_get($item, 'episode'),
            'uploaded_on' => data_get($item, 'uploaded_on'),
            'release_date' => data_get($item, 'release_date'),
        ]);
    }

    private function normalizeEpisodeSummary(array $item, string $animeId): Fluent
    {
        $id = $this->sanitizeId((string) data_get($item, 'id', ''));
        $title = (string) data_get($item, 'title', 'Episode');

        return new Fluent([
            'id' => $id,
            'route_id' => $this->encodeId($id),
            'anime_id' => $animeId,
            'anime_route_id' => $this->encodeId($animeId),
            'title' => $title,
            'episode_number' => $this->extractEpisodeNumber($title),
            'uploaded_on' => data_get($item, 'uploaded_on'),
            'synopsis' => 'Episode available to stream online.',
        ]);
    }

    private function enrichAnimeImages(Fluent $anime, bool $hydrateScore = true): Fluent
    {
        $anime = $this->enrichAnimeFromSnapshots($anime, $hydrateScore);

        if (
            ! $this->isFallbackImage((string) $anime->poster_image)
            && ! $this->isFallbackImage((string) $anime->banner_image)
            && (! $hydrateScore || (float) $anime->score > 0)
        ) {
            return $anime;
        }

        $cacheKey = 'otakudesu:v4:anime:' . md5((string) $anime->id);
        $cachedPayload = Cache::get($cacheKey);

        if (is_array($cachedPayload) && ! empty($cachedPayload['thumb'])) {
            $thumb = data_get($cachedPayload, 'thumb');
            $anime->poster_image = $this->posterImage($thumb);
            $anime->banner_image = $this->bannerImage($thumb);

            if (($anime->studio === null || $anime->studio === '') && data_get($cachedPayload, 'studio')) {
                $anime->studio = data_get($cachedPayload, 'studio');
            }

            if ((int) $anime->year === 0 && data_get($cachedPayload, 'release_date')) {
                $anime->year = $this->extractYear((string) data_get($cachedPayload, 'release_date'));
            }

            if ($hydrateScore && (float) $anime->score === 0.0 && data_get($cachedPayload, 'score')) {
                $anime->score = (float) data_get($cachedPayload, 'score');
            }

            if ($hydrateScore && (float) $anime->score <= 0.0) {
                $anime->score = $this->fallbackScore((string) $anime->id, (string) $anime->title);
            }

            if (empty($anime->genres) || collect($anime->genres)->isEmpty()) {
                $anime->genres = collect(data_get($cachedPayload, 'genre_list', []))
                    ->map(fn (array $genre) => new Fluent([
                        'id' => $this->sanitizeId((string) (data_get($genre, 'genre_id') ?? data_get($genre, 'id', ''))),
                        'name' => data_get($genre, 'genre_name') ?? data_get($genre, 'genre_title') ?? 'Unknown',
                    ]))
                    ->values();
            }
        }

        if ($this->isFallbackImage((string) $anime->poster_image)) {
            $thumb = $this->fetchThumbDirect((string) $anime->id);
            if ($thumb) {
                $anime->poster_image = $this->posterImage($thumb);
                $anime->banner_image = $this->bannerImage($thumb);
            }
        }

        return $anime;
    }

    /**
     * Fetch just the poster image URL for an anime directly from the source page,
     * bypassing the getCached pipeline so it never sets source_unavailable flags.
     */
    private function fetchThumbDirect(string $animeId): ?string
    {
        $cacheKey = 'otakudesu:thumb:' . md5($animeId);
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return $cached ?: null;
        }

        $url = null;

        foreach ($this->sourceBaseUrls() as $baseUrl) {
            try {
                $response = $this->sourceClient($baseUrl)
                    ->get('/anime/' . trim($animeId, '/') . '/');

                if (! $response->successful()) {
                    continue;
                }

                $html = $response->body();
                $dom = new \DOMDocument();
                @$dom->loadHTML($html);
                $xpath = new \DOMXPath($dom);

                $imgNode = $xpath->query("//div[contains(@class,'fotoanime')]//img")->item(0);
                if ($imgNode instanceof \DOMElement) {
                    $src = $imgNode->getAttribute('src');
                    if (filter_var($src, FILTER_VALIDATE_URL)) {
                        $url = $src;
                        break;
                    }
                }

                // Fallback: og:image meta
                $ogImage = $xpath->query("//meta[@property='og:image']")->item(0);
                if ($ogImage instanceof \DOMElement) {
                    $src = $ogImage->getAttribute('content');
                    if (filter_var($src, FILTER_VALIDATE_URL)) {
                        $url = $src;
                        break;
                    }
                }
            } catch (Throwable) {
                continue;
            }
        }

        // Cache result (including null = "not found") for 4 hours
        Cache::put($cacheKey, $url ?? '', now()->addHours(4));

        return $url;
    }

    private function enrichAnimeFromSnapshots(Fluent $anime, bool $hydrateScore = true): Fluent
    {
        $snapshotItem = $this->snapshotAnimeIndex()[(string) $anime->id] ?? null;

        if (! is_array($snapshotItem)) {
            return $anime;
        }

        $thumb = data_get($snapshotItem, 'thumb');

        if ($this->isFallbackImage((string) $anime->poster_image) && $thumb) {
            $anime->poster_image = $this->posterImage($thumb);
        }

        if ($this->isFallbackImage((string) $anime->banner_image) && $thumb) {
            $anime->banner_image = $this->bannerImage($thumb);
        }

        if (($anime->studio === null || $anime->studio === '') && data_get($snapshotItem, 'studio')) {
            $anime->studio = data_get($snapshotItem, 'studio');
        }

        if ((int) $anime->year === 0) {
            $anime->year = $this->extractYear((string) (data_get($snapshotItem, 'release_date') ?? data_get($snapshotItem, 'uploaded_on', '')));
        }

        if ($hydrateScore && (float) $anime->score === 0.0 && (float) data_get($snapshotItem, 'score', 0) > 0) {
            $anime->score = (float) data_get($snapshotItem, 'score', 0);
        }

        if (empty($anime->genres) || collect($anime->genres)->isEmpty()) {
            $genres = collect(data_get($snapshotItem, 'genre_list', []))
                ->map(fn (array $genre) => new Fluent([
                    'id' => $this->sanitizeId((string) (data_get($genre, 'genre_id') ?? data_get($genre, 'id', ''))),
                    'name' => data_get($genre, 'genre_name') ?? data_get($genre, 'genre_title') ?? 'Unknown',
                ]))
                ->filter(fn (Fluent $genre) => $genre->id !== '')
                ->values();

            if ($genres->isNotEmpty()) {
                $anime->genres = $genres;
            }
        }

        return $anime;
    }

    private function normalizeStatus(?string $status): string
    {
        $value = Str::lower((string) $status);

        return match (true) {
            str_contains($value, 'ongoing') => 'ongoing',
            str_contains($value, 'complete') || str_contains($value, 'completed') => 'completed',
            default => $status ?: 'unknown',
        };
    }

    private function extractYear(string $value): ?int
    {
        if (preg_match('/(19|20)\d{2}/', $value, $matches)) {
            return (int) $matches[0];
        }

        return null;
    }

    private function extractEpisodeNumber(string $title): int
    {
        if (preg_match('/episode\s+(\d+)/i', $title, $matches)) {
            return (int) $matches[1];
        }

        return 1;
    }

    private function sourceHasMore(string $source, int $rawCount): bool
    {
        if ($source === 'search') {
            return false;
        }

        return $rawCount >= 10;
    }

    private function sanitizeId(string $id): string
    {
        return trim($id, '/');
    }

    private function getCached(string $key, string $path): array
    {
        $cacheKey = 'otakudesu:v4:' . $key;
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        $payload = (function () use ($path) {
            if ($this->hasApiBaseUrl() && ! Cache::get('otakudesu:api_unavailable')) {
                try {
                    $response = $this->get($path);
                    $this->storeSnapshot($path, $response);
                    Cache::forget('otakudesu:api_unavailable');
                    Cache::forget('otakudesu:source_unavailable');
                    Cache::forget('otakudesu:unavailable');
                    Cache::forget('otakudesu:last_error');

                    return $response;
                } catch (Throwable $exception) {
                    Cache::put('otakudesu:api_unavailable', true, now()->addMinutes(5));
                    Cache::put('otakudesu:last_error', [
                        'path' => $path,
                        'message' => $exception->getMessage(),
                        'time' => now()->toIso8601String(),
                    ], now()->addMinutes(10));
                    Log::warning('Otakudesu upstream request failed, using scraper fallback.', [
                        'path' => $path,
                        'message' => $exception->getMessage(),
                    ]);
                }
            }

            $snapshot = $this->getSnapshot($path);

            if ($snapshot !== [] && $this->isSourceDegraded()) {
                Log::info('Serving Otakudesu payload from local snapshot while source is degraded.', [
                    'path' => $path,
                ]);

                return $snapshot;
            }

            $fallback = [];

            if (! Cache::get('otakudesu:source_unavailable')) {
                try {
                    $fallback = $this->scrapeFallback($path);
                } catch (Throwable $exception) {
                    Cache::put('otakudesu:source_unavailable', true, now()->addMinutes(2));
                    Cache::put('otakudesu:last_error', [
                        'path' => $path,
                        'message' => $exception->getMessage(),
                        'time' => now()->toIso8601String(),
                    ], now()->addMinutes(10));
                    Log::warning('Otakudesu scraper fallback failed.', [
                        'path' => $path,
                        'message' => $exception->getMessage(),
                    ]);
                }
            }

            if ($fallback !== []) {
                $this->storeSnapshot($path, $fallback);
                Cache::forget('otakudesu:source_unavailable');
                Cache::forget('otakudesu:unavailable');

                return $fallback;
            }

            if ($snapshot !== []) {
                Log::info('Serving Otakudesu payload from local snapshot.', [
                    'path' => $path,
                ]);

                return $snapshot;
            }

            Cache::put('otakudesu:unavailable', true, now()->addMinutes(1));

            return [];
        })();

        Cache::put(
            $cacheKey,
            $payload,
            $payload === [] ? now()->addSeconds(30) : now()->addMinutes(10)
        );

        return $payload;
    }

    public function refreshSnapshots(array $paths): array
    {
        $results = [];

        foreach ($paths as $path) {
            $path = trim((string) $path);

            if ($path === '') {
                continue;
            }

            try {
                try {
                    $payload = $this->get($path);
                } catch (Throwable) {
                    $payload = $this->scrapeFallback($path);
                }

                if ($payload === []) {
                    $results[$path] = 'empty';
                    continue;
                }

                $this->storeSnapshot($path, $payload);
                Cache::put('otakudesu:v4:' . $this->cacheKeyForPath($path), $payload, now()->addMinutes(10));
                $results[$path] = 'ok';
            } catch (Throwable $exception) {
                $results[$path] = 'failed: ' . $exception->getMessage();
            }
        }

        return $results;
    }

    public function warmSnapshotPaths(bool $deep = false, int $customPages = 0, array $customGenres = [], bool $allGenres = false, int $animeLimit = 20): array
    {
        $pages = $customPages > 0 ? $customPages : max(1, (int) config('services.otakudesu.warm_pages', 2));

        if ($allGenres) {
            $genres = $this->getGenres()->pluck('id')->filter()->unique()->values();
        } elseif (! empty($customGenres)) {
            $genres = collect($customGenres)->map(fn ($g) => trim((string) $g))->filter()->unique()->values();
        } else {
            $genres = collect(config('services.otakudesu.warm_genres', []))
                ->map(fn ($genre) => trim((string) $genre))
                ->filter()
                ->unique()
                ->values();
        }

        $paths = collect([
            'home',
            'genres',
            'anime-list',
        ]);

        foreach (range(1, $pages) as $page) {
            $paths->push("ongoing/page/{$page}");
            $paths->push("complete/page/{$page}");
        }

        foreach ($genres as $genre) {
            $paths->push("genres/{$genre}/catalog/page/1");
            $paths->push("genres/{$genre}/page/1");

            if ($deep) {
                foreach (range(2, min($pages, 5)) as $page) {
                    $paths->push("genres/{$genre}/catalog/page/{$page}");
                    $paths->push("genres/{$genre}/page/{$page}");
                }
            }
        }

        if ($deep) {
            foreach ($this->warmAnimeIds($animeLimit) as $animeId) {
                $paths->push("anime/{$animeId}");
            }
        }

        return $paths->unique()->values()->all();
    }

    private function scrapeFallback(string $path): array
    {
        if ($path === 'home') {
            return $this->scrapeHomePayload();
        }

        if ($path === 'anime-list') {
            return $this->scrapeAnimeListPayload();
        }

        if ($path === 'genres') {
            return $this->scrapeGenreListPayload();
        }

        if (preg_match('#^complete/page/(\d+)$#', $path, $matches)) {
            return $this->scrapeArchivePayload('complete-anime', 'completed', (int) $matches[1]);
        }

        if (preg_match('#^ongoing/page/(\d+)$#', $path, $matches)) {
            return $this->scrapeArchivePayload('ongoing-anime', 'ongoing', (int) $matches[1]);
        }

        if (preg_match('#^genres/([^/]+)/page/(\d+)$#', $path, $matches)) {
            return $this->scrapeGenrePagePayload($matches[1], (int) $matches[2]);
        }

        if (preg_match('#^genres/([^/]+)/catalog/page/(\d+)$#', $path, $matches)) {
            return $this->scrapeFastGenreCatalogPayload($matches[1], (int) $matches[2]);
        }

        if (preg_match('#^genres/([^/]+)/catalog$#', $path, $matches)) {
            return $this->scrapeGenreCatalogPayload($matches[1]);
        }

        if (preg_match('#^search/(.+)$#', $path, $matches)) {
            return $this->scrapeSearchPayload(rawurldecode($matches[1]));
        }

        if (preg_match('#^anime/(.+)$#', $path, $matches)) {
            return $this->scrapeAnimePayload($matches[1]);
        }

        if (preg_match('#^eps/(.+)$#', $path, $matches)) {
            return $this->scrapeEpisodePayload($matches[1]);
        }

        return [];
    }

    private function scrapeHomePayload(): array
    {
        $xpath = $this->sourceXPath('/');
        $sections = $xpath->query("//div[contains(@class,'rseries')]");

        $ongoing = [];
        $complete = [];

        foreach ($sections as $section) {
            $heading = Str::lower($this->queryText($xpath, ".//h2|.//h4|.//div[contains(@class,'thetitle')]", $section) ?? '');
            $items = $this->parsePosterListItems($xpath, ".//li", $section);

            if (str_contains($heading, 'on-going') || str_contains($heading, 'ongoing')) {
                $ongoing = $items;
            }

            if (str_contains($heading, 'complete') || str_contains($heading, 'selesai')) {
                $complete = $items;
            }
        }

        if ($ongoing === [] || $complete === []) {
            $all = $this->parsePosterListItems($xpath, "(//div[contains(@class,'venz')])[1]//li");
            $ongoing = $ongoing ?: $this->scrapeArchivePayload('ongoing-anime', 'ongoing', 1)['animeList'];
            $ongoing = $ongoing ?: array_slice($all, 0, 15);
            $complete = $complete ?: $this->scrapeArchivePayload('complete-anime', 'completed', 1)['animeList'];
        }

        return [
            'status' => 'success',
            'baseUrl' => 'https://otakudesu.moe/',
            'home' => [
                'on_going' => array_values($ongoing),
                'complete' => array_values($complete),
            ],
        ];
    }

    private function scrapeArchivePayload(string $segment, string $status, int $page): array
    {
        $page = max(1, $page);
        $path = '/' . trim($segment, '/') . '/';

        if ($page > 1) {
            $path .= 'page/' . $page . '/';
        }

        $xpath = $this->sourceXPath($path);
        $items = $this->parsePosterListItems($xpath, "//div[contains(@class,'venz')]//ul/li");

        foreach ($items as &$item) {
            $item['status'] = $status;
        }

        return [
            'status' => 'success',
            'baseUrl' => 'https://otakudesu.moe/' . trim($segment, '/') . '/',
            'animeList' => array_values($items),
        ];
    }

    private function scrapeGenreListPayload(): array
    {
        $xpath = $this->sourceXPath('/genre-list/');
        $genres = [];

        foreach ($xpath->query("//a[contains(@href,'/genres/')]") as $genreLink) {
            $name = $this->cleanText($genreLink->textContent);
            $href = $genreLink instanceof \DOMElement ? $genreLink->getAttribute('href') : '';
            $id = $this->extractSiteId($href, 'genres');

            if ($name === '' || $id === '') {
                continue;
            }

            $genres[$id] = [
                'id' => $id,
                'genre_name' => $name,
                'image_link' => null,
            ];
        }

        return [
            'genreList' => array_values($genres),
        ];
    }

    private function scrapeAnimeListPayload(): array
    {
        $xpath = $this->sourceXPath('/anime-list/');
        $items = [];

        foreach ($xpath->query("//a[contains(@href,'/anime/')]") as $link) {
            if (! $link instanceof \DOMElement) {
                continue;
            }

            $href = $link->getAttribute('href');
            $id = $this->extractSiteId($href, 'anime');
            $title = $this->cleanText($link->textContent);

            if ($id === '' || $title === '') {
                continue;
            }

            $status = str_contains(Str::lower($title), 'on-going') ? 'ongoing' : 'completed';
            $cleanTitle = trim((string) Str::of($title)->replace('On-Going', ''));

            $items[$id] = [
                'id' => $id,
                'title' => $cleanTitle,
                'status' => $status,
                'thumb' => null,
                'episode' => null,
                'score' => 0,
                'uploaded_on' => null,
                'release_date' => null,
            ];
        }

        return [
            'status' => 'success',
            'baseUrl' => 'https://otakudesu.moe/anime-list/',
            'animeList' => array_values($items),
        ];
    }

    private function scrapeGenrePagePayload(string $genreId, int $page): array
    {
        $page = max(1, $page);
        $path = '/genres/' . trim($genreId, '/') . '/';

        if ($page > 1) {
            $path .= 'page/' . $page . '/';
        }

        $xpath = $this->sourceXPath($path);
        $items = [];

        foreach ($xpath->query("//div[contains(@class,'col-anime-con')]") as $node) {
            $titleLink = $this->queryAttr($xpath, ".//div[contains(@class,'col-anime-title')]/a", 'href', $node);
            $id = $this->extractSiteId($titleLink, 'anime');

            if ($id === '') {
                continue;
            }

            $items[] = [
                'id' => $id,
                'title' => $this->queryText($xpath, ".//div[contains(@class,'col-anime-title')]/a", $node),
                'thumb' => $this->queryAttr($xpath, ".//div[contains(@class,'col-anime-cover')]//img", 'src', $node),
                'studio' => $this->queryText($xpath, ".//div[contains(@class,'col-anime-studio')]", $node),
                'episode' => $this->queryText($xpath, ".//div[contains(@class,'col-anime-eps')]", $node),
                'score' => (float) ($this->queryText($xpath, ".//div[contains(@class,'col-anime-rating')]", $node) ?: 0),
                'release_date' => $this->queryText($xpath, ".//div[contains(@class,'col-anime-date')]", $node),
                'status' => $this->inferStatusFromEpisodeLabel($this->queryText($xpath, ".//div[contains(@class,'col-anime-eps')]", $node)),
                'genre_list' => $this->parseGenreAnchors($xpath, ".//div[contains(@class,'col-anime-genre')]//a", $node),
            ];
        }

        return [
            'status' => 'success',
            'animeList' => $items,
        ];
    }

    private function scrapeGenreCatalogPayload(string $genreId): array
    {
        $genreId = trim($genreId, '/');
        $allItems = collect();
        $seenIds = [];
        $maxPages = 20;

        for ($page = 1; $page <= $maxPages; $page++) {
            $payload = $this->scrapeGenrePagePayload($genreId, $page);
            $pageItems = collect(data_get($payload, 'animeList', []));

            if ($pageItems->isEmpty()) {
                break;
            }

            $newItems = $pageItems
                ->filter(function (array $item) use (&$seenIds) {
                    $id = (string) data_get($item, 'id', '');

                    if ($id === '' || isset($seenIds[$id])) {
                        return false;
                    }

                    $seenIds[$id] = true;

                    return true;
                })
                ->values();

            if ($newItems->isEmpty()) {
                break;
            }

            $allItems = $allItems->concat($newItems);

            if ($pageItems->count() < 10) {
                break;
            }
        }

        return [
            'status' => 'success',
            'animeList' => $allItems->values()->all(),
        ];
    }

    private function scrapeFastGenreCatalogPayload(string $genreId, int $page): array
    {
        $genreId = trim($genreId, '/');
        $page = max(1, $page);
        $perPage = $this->catalogPerPage('genre-fast');
        $approxSourceItemsPerPage = 24;
        $sourcePagesPerAppPage = max(1, (int) ceil($perPage / $approxSourceItemsPerPage));
        $sourceStartPage = (($page - 1) * $sourcePagesPerAppPage) + 1;
        $requiredItems = $perPage + 1;
        $allItems = collect();
        $seenIds = [];
        $sourcePage = $sourceStartPage;
        $maxSourcePages = $sourceStartPage + 1;

        while ($sourcePage <= $maxSourcePages && $allItems->count() < $requiredItems) {
            $payload = $this->scrapeGenrePagePayload($genreId, $sourcePage);
            $pageItems = collect(data_get($payload, 'animeList', []));

            if ($pageItems->isEmpty()) {
                break;
            }

            $newItems = $pageItems
                ->filter(function (array $item) use (&$seenIds) {
                    $id = (string) data_get($item, 'id', '');

                    if ($id === '' || isset($seenIds[$id])) {
                        return false;
                    }

                    $seenIds[$id] = true;

                    return true;
                })
                ->values();

            if ($newItems->isEmpty()) {
                break;
            }

            $allItems = $allItems->concat($newItems);

            if ($pageItems->count() < 10) {
                break;
            }

            $sourcePage++;
        }

        return [
            'status' => 'success',
            'animeList' => $allItems->take($perPage + 1)->values()->all(),
        ];
    }

    private function scrapeSearchPayload(string $query): array
    {
        $xpath = $this->sourceXPath('/?s=' . rawurlencode($query) . '&post_type=anime');
        $items = [];

        foreach ($xpath->query("//ul[contains(@class,'chivsrc')]//li") as $node) {
            $titleLink = $this->queryAttr($xpath, ".//h2/a", 'href', $node);
            $id = $this->extractSiteId($titleLink, 'anime');

            if ($id === '') {
                continue;
            }

            $items[] = [
                'id' => $id,
                'title' => $this->queryText($xpath, ".//h2/a", $node),
                'thumb' => $this->queryAttr($xpath, ".//img", 'src', $node),
                'status' => $this->extractLabeledValue($xpath, $node, 'Status'),
                'score' => (float) ($this->extractLabeledValue($xpath, $node, 'Rating') ?: 0),
                'genre_list' => $this->parseGenreAnchors($xpath, ".//div[contains(@class,'set')]//a", $node),
            ];
        }

        return [
            'status' => 'success',
            'search_results' => $items,
        ];
    }

    private function scrapeAnimePayload(string $animeId): array
    {
        $xpath = $this->sourceXPath('/anime/' . trim($animeId, '/') . '/');
        $info = [];

        foreach ($xpath->query("//div[contains(@class,'infozingle')]//p") as $row) {
            $label = $this->cleanText($this->queryText($xpath, './/b', $row) ?? '');
            $value = $this->cleanText(str_replace($label . ':', '', $row->textContent));

            if ($label !== '') {
                $info[Str::lower($label)] = $value;
            }
        }

        $episodes = [];

        foreach ($xpath->query("//div[contains(@class,'episodelist')]//li") as $node) {
            $title = $this->queryText($xpath, ".//a", $node);
            $href = $this->queryAttr($xpath, ".//a", 'href', $node);
            $id = $this->extractSiteId($href, 'episode');

            if ($title === null || $id === '') {
                continue;
            }

            $episodes[] = [
                'id' => $id,
                'title' => $title,
                'uploaded_on' => $this->queryText($xpath, ".//span[contains(@class,'zeebr')]", $node),
            ];
        }

        $extractedTitle = $info['judul']
            ?? $this->queryText($xpath, "//div[contains(@class,'posttl')]")
            ?? $this->queryText($xpath, "//h1[contains(@class,'jdlflm')]")
            ?? $this->queryText($xpath, "//h1")
            ?? $this->queryText($xpath, "//title");

        if (! $extractedTitle || trim($extractedTitle) === '') {
            $extractedTitle = (string) Str::of($animeId)
                ->replace(['-sub-indo', '-subtitle-indonesia', '-'], [' ', ' ', ' '])
                ->trim()
                ->title();
        } else {
            $extractedTitle = (string) Str::of($extractedTitle)
                ->replace(['Subtitle Indonesia', 'Sub Indo', 'Otakudesu'], '')
                ->trim();
        }

        return [
            'title' => $extractedTitle ?: 'Anime',
            'thumb' => $this->queryAttr($xpath, "//div[contains(@class,'fotoanime')]//img", 'src'),
            'synopsis' => $this->queryText($xpath, "//div[contains(@class,'sinopc')]"),
            'japanase' => $info['japanese'] ?? null,
            'score' => (float) ($info['skor'] ?? 0) > 0 ? (float) $info['skor'] : $this->fallbackScore($animeId, $extractedTitle),
            'producer' => $info['produser'] ?? null,
            'type' => $this->normalizeType($info['tipe'] ?? null, $info['status'] ?? null),
            'status' => $info['status'] ?? null,
            'total_episode' => $info['total episode'] ?? null,
            'duration' => $info['durasi'] ?? null,
            'release_date' => $info['tanggal rilis'] ?? null,
            'studio' => $info['studio'] ?? null,
            'genre_list' => $this->parseCommaGenres($info['genre'] ?? ''),
            'episode_list' => $episodes,
        ];
    }

    private function scrapeEpisodePayload(string $episodeId): array
    {
        $path = '/episode/' . trim($episodeId, '/') . '/';
        $page = $this->sourcePage($path);
        $xpath = $page['xpath'];
        $title = $this->queryText($xpath, "//div[contains(@class,'download')]//h4")
            ?? $this->queryText($xpath, "//title")
            ?? Str::of($episodeId)->replace('-', ' ')->title()->toString();
        $qualities = $this->parseDownloadQualities($xpath);
        $mirrorGroups = $this->parseMirrorGroups($xpath);
        $streamOptions = $this->resolveStreamOptions($mirrorGroups, $page['html'], $page['url']);
        $selectedStream = $this->selectStreamOption(collect($streamOptions));
        // Only use iframe src as fallback if it's a real embed (not a dummy/sample video)
        $iframeSrc = $this->queryAttr($xpath, "//iframe", 'src');
        $fallbackStream = ($iframeSrc && ! str_contains((string) $iframeSrc, 'samplelib')) ? $iframeSrc : null;

        return [
            'title' => $title,
            'link_stream' => data_get($selectedStream, 'url', $fallbackStream),
            'stream_quality' => data_get($selectedStream, 'quality'),
            'stream_options' => $streamOptions,
            'qualities' => $qualities,
            'quality' => [
                'low_quality' => $qualities[0] ?? null,
                'medium_quality' => $qualities[1] ?? null,
                'high_quality' => $qualities[2] ?? null,
            ],
            'mirror1' => $mirrorGroups[0] ?? [],
            'mirror2' => $mirrorGroups[1] ?? [],
            'mirror3' => $mirrorGroups[2] ?? [],
        ];
    }

    private function get(string $path): array
    {
        return $this->client()->get($path)->throw()->json() ?? [];
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl($this->apiBaseUrl())
            ->acceptJson()
            ->withHeaders([
                'User-Agent' => 'Fer6origami/1.0 (+Laravel)',
            ])
            ->withOptions($this->httpOptions())
            ->connectTimeout(2)
            ->timeout(5);
    }

    private function hasApiBaseUrl(): bool
    {
        return $this->apiBaseUrl() !== '';
    }

    private function apiBaseUrl(): string
    {
        $baseUrl = trim((string) config('services.otakudesu.base_url', ''));

        if (! filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            return '';
        }

        return rtrim($baseUrl, '/');
    }

    private function sourceClient(?string $baseUrl = null): PendingRequest
    {
        return Http::baseUrl(rtrim($baseUrl ?: config('services.otakudesu.source_url', 'https://otakudesu.blog'), '/'))
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9,id;q=0.8',
            ])
            ->withOptions($this->httpOptions())
            ->connectTimeout(2)
            ->timeout(4);
    }

    private function httpOptions(): array
    {
        if (! config('services.otakudesu.disable_proxy', true)) {
            return [];
        }

        return [
            'proxy' => '',
            'verify' => false,
        ];
    }

    private function sourceXPath(string $path): \DOMXPath
    {
        return $this->sourcePage($path)['xpath'];
    }

    private function sourcePage(string $path): array
    {
        $lastException = null;

        foreach ($this->sourceBaseUrls() as $baseUrl) {
            try {
                $response = $this->sourceClient($baseUrl)->get($path)->throw();
                $html = $response->body();

                if (trim($html) === '' || ! $this->isUsableSourceHtml($path, $html)) {
                    continue;
                }

                libxml_use_internal_errors(true);

                $dom = new \DOMDocument();
                $dom->loadHTML($html);
                libxml_clear_errors();

                return [
                    'html' => $html,
                    'xpath' => new \DOMXPath($dom),
                    'url' => $this->extractPageUrl($html, rtrim($baseUrl, '/') . '/' . ltrim($path, '/')),
                ];
            } catch (Throwable $exception) {
                $lastException = $exception;
            }
        }

        throw $lastException ?: new \RuntimeException('Unable to load Otakudesu source page.');
    }

    private function isUsableSourceHtml(string $path, string $html): bool
    {
        $normalizedPath = '/' . ltrim($path, '/');
        $haystack = Str::lower($html);

        if (str_contains($haystack, 'blocked') || str_contains($haystack, 'access denied')) {
            return false;
        }

        if ($normalizedPath === '/' || $normalizedPath === '') {
            return str_contains($html, '/anime/') || str_contains($haystack, 'rseries') || str_contains($haystack, 'venz');
        }

        if ($normalizedPath === '/anime-list/') {
            return str_contains($html, '/anime/');
        }

        if ($normalizedPath === '/genre-list/') {
            return str_contains($html, '/genres/');
        }

        if (
            str_contains($normalizedPath, '/ongoing-anime/')
            || str_contains($normalizedPath, '/complete-anime/')
            || str_contains($normalizedPath, '/genres/')
            || str_starts_with($normalizedPath, '/search/')
        ) {
            return str_contains($html, '/anime/');
        }

        if (str_starts_with($normalizedPath, '/anime/')) {
            return str_contains($html, '/eps/') || str_contains($haystack, 'episode') || str_contains($haystack, 'sinopsis');
        }

        if (str_starts_with($normalizedPath, '/episode/') || str_starts_with($normalizedPath, '/eps/')) {
            return str_contains($haystack, 'mirrorstream') || str_contains($haystack, 'download') || str_contains($haystack, 'stream');
        }

        return true;
    }

    private function parsePosterListItems(\DOMXPath $xpath, string $query, ?\DOMNode $contextNode = null): array
    {
        $items = [];

        foreach ($xpath->query($query, $contextNode) as $node) {
            $href = $this->queryAttr($xpath, ".//div[contains(@class,'thumb')]//a", 'href', $node);
            $id = $this->extractSiteId($href, 'anime');

            if ($id === '') {
                continue;
            }

            $rawScoreOrDay = $this->queryText($xpath, ".//div[contains(@class,'epztipe')]", $node);
            $score = is_numeric($rawScoreOrDay) ? (float) $rawScoreOrDay : 0.0;
            $dayUpdated = !is_numeric($rawScoreOrDay) ? $rawScoreOrDay : null;

            $items[] = [
                'id' => $id,
                'title' => $this->queryText($xpath, ".//h2[contains(@class,'jdlflm')]", $node),
                'thumb' => $this->queryAttr($xpath, ".//img", 'src', $node),
                'episode' => $this->queryText($xpath, ".//div[contains(@class,'epz')]", $node),
                'score' => $score,
                'day_updated' => $dayUpdated,
                'uploaded_on' => $this->queryText($xpath, ".//div[contains(@class,'newnime')]", $node),
                'release_date' => $this->queryText($xpath, ".//div[contains(@class,'newnime')]", $node),
            ];
        }

        return $items;
    }

    private function parseGenreAnchors(\DOMXPath $xpath, string $query, \DOMNode $contextNode): array
    {
        $genres = [];

        foreach ($xpath->query($query, $contextNode) as $genre) {
            $href = $genre instanceof \DOMElement ? $genre->getAttribute('href') : '';
            $id = $this->extractSiteId($href, 'genres');

            if ($id === '') {
                continue;
            }

            $genres[] = [
                'genre_id' => $id,
                'genre_name' => $this->cleanText($genre->textContent),
            ];
        }

        return $genres;
    }

    private function parseCommaGenres(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn (string $genre) => trim($genre))
            ->filter()
            ->values()
            ->map(fn (string $genre) => [
                'genre_id' => Str::slug($genre),
                'genre_name' => $genre,
            ])
            ->all();
    }

    private function parseDownloadQualities(\DOMXPath $xpath): array
    {
        $qualities = [];

        foreach ($xpath->query("//div[contains(@class,'download')]//li") as $item) {
            $quality = $this->queryText($xpath, ".//strong", $item);

            if ($quality === null) {
                continue;
            }

            $downloadLinks = [];

            foreach ($xpath->query(".//a", $item) as $link) {
                $downloadLinks[] = [
                    'host' => $this->cleanText($link->textContent),
                    'link' => $link instanceof \DOMElement ? $link->getAttribute('href') : '',
                ];
            }

            $qualities[] = [
                'quality' => $quality,
                'size' => $this->queryText($xpath, ".//i", $item),
                'download_links' => $downloadLinks,
            ];
        }

        return $qualities;
    }

    private function parseMirrorGroups(\DOMXPath $xpath): array
    {
        $groups = [];

        foreach ($xpath->query("//div[contains(@class,'mirrorstream')]/ul") as $groupNode) {
            if (! $groupNode instanceof \DOMElement) {
                continue;
            }

            $className = $groupNode->getAttribute('class');
            preg_match('/m(\d{3,4}p)/i', $className, $qualityMatches);
            $quality = $this->normalizeQualityLabel($qualityMatches[1] ?? $groupNode->textContent);

            if ($quality === '') {
                continue;
            }

            $mirrors = [];

            foreach ($xpath->query(".//a[@data-content]", $groupNode) as $linkNode) {
                if (! $linkNode instanceof \DOMElement) {
                    continue;
                }

                $payload = trim($linkNode->getAttribute('data-content'));

                if ($payload === '') {
                    continue;
                }

                $mirrors[] = [
                    'label' => $this->cleanText($linkNode->textContent) ?: 'Mirror',
                    'data_content' => $payload,
                    'is_default' => filter_var($linkNode->getAttribute('data-default'), FILTER_VALIDATE_BOOLEAN),
                ];
            }

            if ($mirrors === []) {
                continue;
            }

            $groups[] = [
                'quality' => $quality,
                'mirrors' => $mirrors,
            ];
        }

        usort($groups, fn (array $left, array $right) => $this->qualityRank($right['quality'] ?? '') <=> $this->qualityRank($left['quality'] ?? ''));

        return array_values($groups);
    }

    private function resolveStreamOptions(array $mirrorGroups, string $html, string $pageUrl): array
    {
        $ajaxUrl = $this->extractAjaxUrl($html);

        if ($ajaxUrl === null || $mirrorGroups === []) {
            return [];
        }

        $nonce = $this->fetchMirrorNonce($ajaxUrl, $pageUrl);

        if ($nonce === null) {
            return [];
        }

        $options = [];

        foreach ($mirrorGroups as $group) {
            foreach (data_get($group, 'mirrors', []) as $mirror) {
                $url = $this->resolveMirrorUrl($ajaxUrl, $pageUrl, $nonce, $mirror['data_content'] ?? null);

                if ($url === null) {
                    continue;
                }

                $options[] = [
                    'quality' => $group['quality'] ?? null,
                    'label' => $mirror['label'] ?? 'Mirror',
                    'url' => $url,
                    'is_default' => (bool) ($mirror['is_default'] ?? false),
                ];
            }
        }

        usort($options, function (array $left, array $right) {
            $qualityComparison = $this->qualityRank($right['quality'] ?? '') <=> $this->qualityRank($left['quality'] ?? '');

            if ($qualityComparison !== 0) {
                return $qualityComparison;
            }

            return (int) ($right['is_default'] ?? false) <=> (int) ($left['is_default'] ?? false);
        });

        return array_values($options);
    }

    private function extractAjaxUrl(string $html): ?string
    {
        if (preg_match('#https?://[^"\']+/wp-admin/admin-ajax\.php#i', $html, $matches)) {
            return $matches[0];
        }

        return null;
    }

    private function extractPageUrl(string $html, string $fallbackUrl): string
    {
        if (preg_match('/<meta[^>]+property=["\']og:url["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $matches)) {
            return html_entity_decode($matches[1], ENT_QUOTES);
        }

        if (preg_match('/<link[^>]+rel=["\']canonical["\'][^>]+href=["\']([^"\']+)["\']/i', $html, $matches)) {
            return html_entity_decode($matches[1], ENT_QUOTES);
        }

        return $fallbackUrl;
    }

    private function fetchMirrorNonce(string $ajaxUrl, string $pageUrl): ?string
    {
        $cacheKey = 'otakudesu:mirror_nonce:' . md5($ajaxUrl);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($ajaxUrl, $pageUrl) {
            $response = $this->mirrorClient($ajaxUrl, $pageUrl)
                ->asForm()
                ->post($ajaxUrl, [
                    'action' => 'aa1208d27f29ca340c92c66d1926f13f',
                ]);

            if (! $response->successful()) {
                return null;
            }

            return data_get($response->json(), 'data');
        });
    }

    private function resolveMirrorUrl(string $ajaxUrl, string $pageUrl, string $nonce, ?string $encodedPayload): ?string
    {
        if ($encodedPayload === null || $encodedPayload === '') {
            return null;
        }

        $cacheKey = 'otakudesu:mirror_url:' . md5($ajaxUrl . '|' . $encodedPayload);

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($ajaxUrl, $pageUrl, $nonce, $encodedPayload) {
            $decodedPayload = json_decode(base64_decode($encodedPayload, true) ?: '', true);

            if (! is_array($decodedPayload)) {
                return null;
            }

            $response = $this->mirrorClient($ajaxUrl, $pageUrl)
                ->asForm()
                ->post($ajaxUrl, array_merge($decodedPayload, [
                    'nonce' => $nonce,
                    'action' => '2a3505c93b0035d3f455df82bf976b84',
                ]));

            if (! $response->successful()) {
                return null;
            }

            $embedHtml = base64_decode((string) data_get($response->json(), 'data'), true);

            if (! is_string($embedHtml) || trim($embedHtml) === '') {
                return null;
            }

            if (preg_match('/src=["\']([^"\']+)["\']/i', $embedHtml, $matches)) {
                return html_entity_decode($matches[1], ENT_QUOTES);
            }

            return null;
        });
    }

    private function mirrorClient(string $ajaxUrl, string $pageUrl): PendingRequest
    {
        return Http::withHeaders([
                'Accept' => 'application/json, text/javascript, */*; q=0.01',
                'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                'Origin' => $this->baseOrigin($ajaxUrl),
                'Referer' => $pageUrl,
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36',
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->withOptions($this->httpOptions())
            ->connectTimeout(5)
            ->timeout(10);
    }

    private function selectStreamOption(Collection $streamOptions, ?string $preferredQuality = null, ?string $preferredServer = null): ?array
    {
        if ($streamOptions->isEmpty()) {
            return null;
        }

        $preferredQuality = $this->normalizeQualityLabel((string) $preferredQuality);
        $preferredServer = Str::lower(trim((string) $preferredServer));

        if ($preferredQuality !== '' && $preferredServer !== '') {
            $match = $streamOptions->first(function (array $option) use ($preferredQuality, $preferredServer) {
                $qMatch = $this->normalizeQualityLabel((string) ($option['quality'] ?? '')) === $preferredQuality;
                $sMatch = Str::contains(Str::lower((string) ($option['label'] ?? '')), $preferredServer)
                    || Str::contains(Str::lower((string) ($option['url'] ?? '')), $preferredServer);
                return $qMatch && $sMatch;
            });

            if ($match) {
                return $match;
            }
        }

        if ($preferredServer !== '') {
            $match = $streamOptions->first(function (array $option) use ($preferredServer) {
                return Str::contains(Str::lower((string) ($option['label'] ?? '')), $preferredServer)
                    || Str::contains(Str::lower((string) ($option['url'] ?? '')), $preferredServer);
            });

            if ($match) {
                return $match;
            }
        }

        if ($preferredQuality !== '') {
            $preferred = $streamOptions->first(fn (array $option) => $this->normalizeQualityLabel((string) ($option['quality'] ?? '')) === $preferredQuality);

            if ($preferred) {
                return $preferred;
            }
        }

        // Auto-select non-Desustream player (e.g. Blogger, Filedon, Mega) if available to avoid iframe CSP blocks
        $nonDesustream = $streamOptions->first(function (array $option) {
            $url = Str::lower((string) ($option['url'] ?? ''));
            return ! str_contains($url, 'desustream') && ! str_contains($url, 'odstream');
        });

        if ($nonDesustream) {
            return $nonDesustream;
        }

        return $streamOptions
            ->sort(function (array $left, array $right) {
                $qualityComparison = $this->qualityRank($right['quality'] ?? '') <=> $this->qualityRank($left['quality'] ?? '');

                if ($qualityComparison !== 0) {
                    return $qualityComparison;
                }

                return (int) ($right['is_default'] ?? false) <=> (int) ($left['is_default'] ?? false);
            })
            ->first();
    }

    private function normalizeQualityLabel(string $quality): string
    {
        if (preg_match('/(2160|4k|uhd)/i', $quality)) {
            return '2160p';
        }

        if (preg_match('/(1440|2k|qhd)/i', $quality)) {
            return '1440p';
        }

        if (preg_match('/(\d{3,4})/i', $quality, $matches)) {
            return $matches[1] . 'p';
        }

        return Str::lower(trim($quality));
    }

    private function qualityRank(string $quality): int
    {
        if (preg_match('/(2160|4k|uhd)/i', $quality)) {
            return 2160;
        }

        if (preg_match('/(1440|2k|qhd)/i', $quality)) {
            return 1440;
        }

        if (preg_match('/(\d{3,4})/i', $quality, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }

    private function baseOrigin(string $url): string
    {
        $scheme = parse_url($url, PHP_URL_SCHEME) ?: 'https';
        $host = parse_url($url, PHP_URL_HOST) ?: '';
        $port = parse_url($url, PHP_URL_PORT);

        if ($host === '') {
            return $scheme . '://';
        }

        return $scheme . '://' . $host . ($port ? ':' . $port : '');
    }

    private function inferStatusFromEpisodeLabel(?string $value): string
    {
        $value = Str::lower((string) $value);

        if (str_contains($value, 'unknown') || str_contains($value, '?')) {
            return 'ongoing';
        }

        return 'completed';
    }

    private function catalogPerPage(string $source): int
    {
        return in_array($source, ['all', 'genre-all', 'genre-fast'], true) ? 48 : 25;
    }

    private function shouldUseFastGenrePath(string $year, string $minScore): bool
    {
        return $year === '' && $minScore === '';
    }

    private function warmAnimeIds(int $limit = 20): array
    {
        $ids = collect();

        $homeSnapshot = $this->getSnapshot('home');
        $animeListSnapshot = $this->getSnapshot('anime-list');
        $ongoingSnapshot = $this->getSnapshot('ongoing/page/1');
        $completeSnapshot = $this->getSnapshot('complete/page/1');

        $ids = $ids
            ->concat(collect(data_get($homeSnapshot, 'home.on_going', []))->pluck('id'))
            ->concat(collect(data_get($homeSnapshot, 'home.complete', []))->pluck('id'))
            ->concat(collect(data_get($animeListSnapshot, 'animeList', []))->pluck('id'))
            ->concat(collect(data_get($ongoingSnapshot, 'animeList', []))->pluck('id'))
            ->concat(collect(data_get($completeSnapshot, 'animeList', []))->pluck('id'));

        return $ids
            ->map(fn ($id) => $this->sanitizeId((string) $id))
            ->filter()
            ->unique()
            ->take($limit)
            ->values()
            ->all();
    }

    private function storeSnapshot(string $path, array $payload): void
    {
        if (app()->environment('testing') || $payload === [] || ! Schema::hasTable('scraper_snapshots')) {
            return;
        }

        ScraperSnapshot::query()->updateOrCreate(
            ['snapshot_key' => $this->snapshotKey($path)],
            [
                'path' => $path,
                'payload' => $payload,
                'stored_at' => now(),
            ]
        );

        Cache::forget('otakudesu:snapshot_anime_index');
        Cache::forget('otakudesu:snapshot_anime_items');
    }

    private function getSnapshot(string $path): array
    {
        if (app()->environment('testing') || ! Schema::hasTable('scraper_snapshots')) {
            return [];
        }

        $snapshot = ScraperSnapshot::query()
            ->where('snapshot_key', $this->snapshotKey($path))
            ->first();

        return is_array($snapshot?->payload) ? $snapshot->payload : [];
    }

    private function snapshotKey(string $path): string
    {
        return md5($path);
    }

    /**
     * Fill missing poster/metadata on API list items from local scraper snapshots.
     * Order is preserved; items without a snapshot are returned unchanged.
     *
     * @param  array<int, array>  $items
     * @return array<int, array>
     */
    private function hydrateApiItems(array $items): array
    {
        $index = $this->snapshotAnimeIndex();

        if ($index === []) {
            return $items;
        }

        return array_values(array_map(function (array $item) use ($index): array {
            $id = $this->sanitizeId((string) data_get($item, 'id', ''));
            $snapshot = $index[$id] ?? [];

            return $snapshot === [] ? $item : array_replace($item, $snapshot);
        }, $items));
    }

    /**
     * Last-resort search against the cached anime list when the source scraper
     * returns nothing (temporary upstream failure or changed markup).
     *
     * @return array<int, array>
     */
    private function searchLocalAnimeList(string $query): array
    {
        $needle = Str::lower(trim($query));

        if ($needle === '') {
            return [];
        }

        $payload = $this->getCached('anime-list', 'anime-list');

        return collect(data_get($payload, 'animeList', []))
            ->filter(function (array $item) use ($needle): bool {
                $title = Str::lower((string) data_get($item, 'title', ''));
                $id = Str::lower((string) data_get($item, 'id', ''));

                return str_contains($title, $needle) || str_contains($id, $needle);
            })
            ->take(30)
            ->values()
            ->all();
    }

    private function mergeSnapshotAnimeSummaries(Collection $rawItems): Collection
    {
        $snapshotItems = $this->imageBearingSnapshotAnimeItems();

        if ($snapshotItems->isEmpty()) {
            return $rawItems;
        }

        $rawById = $rawItems
            ->filter(fn (array $item) => $this->sanitizeId((string) data_get($item, 'id', '')) !== '')
            ->keyBy(fn (array $item) => $this->sanitizeId((string) data_get($item, 'id', '')));

        $frontItems = $snapshotItems
            ->filter(fn (array $item) => $rawById->has($this->sanitizeId((string) data_get($item, 'id', ''))))
            ->map(function (array $item) use ($rawById) {
                $id = $this->sanitizeId((string) data_get($item, 'id', ''));

                return array_replace($rawById->get($id, []), $item);
            })
            ->values();

        $frontIds = $frontItems
            ->map(fn (array $item) => $this->sanitizeId((string) data_get($item, 'id', '')))
            ->filter()
            ->flip();

        return $frontItems
            ->concat($rawItems->reject(fn (array $item) => $frontIds->has($this->sanitizeId((string) data_get($item, 'id', '')))))
            ->values();
    }

    private function imageBearingSnapshotAnimeItems(): Collection
    {
        if (app()->environment('testing') || ! Schema::hasTable('scraper_snapshots')) {
            return collect();
        }

        return collect(Cache::remember('otakudesu:snapshot_anime_items', now()->addMinutes(5), function () {
            $items = [];
            $seenIds = [];

            foreach ($this->snapshotRows() as $snapshot) {
                foreach ($this->snapshotAnimeItems((string) $snapshot->path, (array) $snapshot->payload) as $item) {
                    $id = $this->sanitizeId((string) data_get($item, 'id', ''));

                    if ($id === '' || isset($seenIds[$id]) || blank(data_get($item, 'thumb'))) {
                        continue;
                    }

                    $seenIds[$id] = true;
                    $items[] = $item;
                }
            }

            return $items;
        }));
    }

    private function snapshotAnimeIndex(): array
    {
        if (app()->environment('testing') || ! Schema::hasTable('scraper_snapshots')) {
            return [];
        }

        return Cache::remember('otakudesu:snapshot_anime_index', now()->addMinutes(5), function () {
            $index = [];

            foreach ($this->snapshotRows() as $snapshot) {
                foreach ($this->snapshotAnimeItems((string) $snapshot->path, (array) $snapshot->payload) as $item) {
                    $id = $this->sanitizeId((string) data_get($item, 'id', ''));

                    if ($id === '') {
                        continue;
                    }

                    $existing = $index[$id] ?? [];
                    $clean = array_filter($item, fn ($value) => $value !== null && $value !== '' && $value !== []);
                    // Filter before replacing: an empty value from a later snapshot
                    // must not erase metadata (especially thumbs) already collected.
                    $index[$id] = array_replace($existing, $clean);
                }
            }

            return $index;
        });
    }

    private function snapshotRows(): Collection
    {
        return ScraperSnapshot::query()
            ->orderByRaw("case when path = 'home' then 0 when path like 'genres/%' then 1 when path like 'anime/%' then 2 else 3 end")
            ->latest('stored_at')
            ->get(['path', 'payload']);
    }

    private function snapshotAnimeItems(string $path, array $payload): array
    {
        $items = collect([
            data_get($payload, 'home.on_going', []),
            data_get($payload, 'home.complete', []),
            data_get($payload, 'animeList', []),
            data_get($payload, 'search_results', []),
        ])
            ->flatMap(fn ($group) => is_array($group) ? $group : [])
            ->filter(fn ($item) => is_array($item))
            ->values();

        if (str_starts_with($path, 'anime/')) {
            $items->prepend(array_filter([
                'id' => $this->sanitizeId((string) Str::after($path, 'anime/')),
                'title' => data_get($payload, 'title'),
                'thumb' => data_get($payload, 'thumb'),
                'score' => data_get($payload, 'score'),
                'studio' => data_get($payload, 'studio'),
                'release_date' => data_get($payload, 'release_date'),
                'status' => data_get($payload, 'status'),
                'genre_list' => data_get($payload, 'genre_list', []),
            ], fn ($value) => $value !== null && $value !== ''));
        }

        return $items->all();
    }

    private function shouldAvoidSecondaryDetailRequests(): bool
    {
        $apiUnavailable = $this->hasApiBaseUrl() && (bool) Cache::get('otakudesu:api_unavailable');
        $sourceUnavailable = (bool) Cache::get('otakudesu:source_unavailable');

        return $apiUnavailable || $sourceUnavailable;
    }

    private function isSourceDegraded(): bool
    {
        return (bool) Cache::get('otakudesu:api_unavailable')
            || (bool) Cache::get('otakudesu:source_unavailable');
    }

    private function cacheKeyForPath(string $path): string
    {
        return match (true) {
            $path === 'home' => 'home',
            $path === 'genres' => 'genres',
            $path === 'anime-list' => 'anime-list',
            str_starts_with($path, 'search/') => 'search:' . md5((string) Str::after($path, 'search/')),
            str_starts_with($path, 'complete/page/') => 'complete:' . (int) Str::after($path, 'complete/page/'),
            str_starts_with($path, 'ongoing/page/') => 'ongoing:' . (int) Str::after($path, 'ongoing/page/'),
            str_starts_with($path, 'genres/') && str_ends_with($path, '/catalog') => 'genre-catalog:' . Str::between($path, 'genres/', '/catalog'),
            str_starts_with($path, 'genres/') && str_contains($path, '/catalog/page/') => 'genre-catalog:' . Str::between($path, 'genres/', '/catalog/page/') . ':page:' . (int) Str::after($path, '/catalog/page/'),
            str_starts_with($path, 'anime/') => 'anime:' . md5((string) Str::after($path, 'anime/')),
            str_starts_with($path, 'eps/') => 'episode:' . md5((string) Str::after($path, 'eps/')),
            default => md5($path),
        };
    }

    private function sourceBaseUrls(): array
    {
        $configured = rtrim((string) config('services.otakudesu.source_url', 'https://otakudesu.blog'), '/');

        return array_values(array_unique(array_filter([
            $configured,
            'https://otakudesu.blog',
            'https://otakudesu.best',
            'https://otakudesu.cloud',
            'https://otakudesu.cc',
            'https://otakudesu.candid.asia',
        ])));
    }

    private function extractLabeledValue(\DOMXPath $xpath, \DOMNode $contextNode, string $label): ?string
    {
        foreach ($xpath->query(".//div[contains(@class,'set')]", $contextNode) as $node) {
            $text = $this->cleanText($node->textContent);

            if (Str::startsWith(Str::lower($text), Str::lower($label))) {
                return trim((string) Str::of($text)->after(':'));
            }
        }

        return null;
    }

    private function queryText(\DOMXPath $xpath, string $query, ?\DOMNode $contextNode = null): ?string
    {
        $node = $xpath->query($query, $contextNode)->item(0);

        if (! $node) {
            return null;
        }

        return $this->cleanText($node->textContent);
    }

    private function queryAttr(\DOMXPath $xpath, string $query, string $attribute, ?\DOMNode $contextNode = null): ?string
    {
        $node = $xpath->query($query, $contextNode)->item(0);

        if (! $node instanceof \DOMElement) {
            return null;
        }

        return $node->getAttribute($attribute) ?: null;
    }

    private function cleanText(?string $value): string
    {
        return trim((string) preg_replace('/\s+/', ' ', html_entity_decode((string) $value)));
    }

    private function posterImage(?string $url): string
    {
        return $this->imageOrFallback($url, 'https://placehold.co/600x900/09090b/f97316?text=No+Image');
    }

    private function bannerImage(?string $url): string
    {
        return $this->imageOrFallback($url, 'https://placehold.co/1400x700/111827/f43f5e?text=No+Banner');
    }

    private function imageOrFallback(?string $url, string $fallback): string
    {
        $url = trim((string) $url);

        if ($url === '' || $url === 'null') {
            return $fallback;
        }

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $host = strtolower((string) parse_url($url, PHP_URL_HOST));

            if (str_contains($host, 'otakudesu') || str_contains($host, 'candid.asia') || str_contains($host, 'wp.com')) {
                if (! (bool) config('services.otakudesu.proxy_images', false)) {
                    return $url;
                }

                return route('image.proxy', ['url' => $url], false);
            }
        }

        return $url;
    }

    private function isFallbackImage(string $url): bool
    {
        return str_contains($url, 'placehold.co');
    }

    private function extractSiteId(?string $url, string $segment): string
    {
        $path = parse_url((string) $url, PHP_URL_PATH) ?: '';
        $pattern = '#/' . preg_quote(trim($segment, '/'), '#') . '/([^/]+)/?#';

        if (preg_match($pattern, $path, $matches)) {
            return trim($matches[1], '/');
        }

        return '';
    }

    private function probe(callable $callback): array
    {
        try {
            $response = $callback();

            return [
                'ok' => $response->successful(),
                'status' => $response->status(),
            ];
        } catch (Throwable $exception) {
            return [
                'ok' => false,
                'status' => null,
                'message' => $exception->getMessage(),
            ];
        }
    }
}
