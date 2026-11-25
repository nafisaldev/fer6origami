<?php

namespace App\Console\Commands;

use App\Services\OtakudesuApiService;
use Illuminate\Console\Command;
use Throwable;

class FillMissingEpisodeSnapshots extends Command
{
    protected $signature = 'scraper:fill-missing-episodes
        {--limit=100 : Maximum episodes to process}
        {--sleep-ms=250 : Delay between requests in milliseconds}
        {--anime= : Only fill episodes for an anime snapshot path/id fragment}
        {--dry-run : Show what would be fetched}
        {--write-db : Also upsert into scraper_snapshots table}';

    protected $description = 'Fetch missing episode snapshots referenced by bundled anime snapshots.';

    public function handle(OtakudesuApiService $service): int
    {
        $file = database_path('data/scraper_snapshots.json');
        if (!is_file($file)) {
            $this->error('Missing snapshot file: ' . $file);
            return self::FAILURE;
        }

        $rows = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        $existing = [];
        foreach ($rows as $row) {
            $path = (string) ($row['path'] ?? '');
            if (str_starts_with($path, 'eps/')) {
                $existing[$path] = true;
            }
        }

        $animeFilter = trim((string) $this->option('anime'));
        $missing = [];
        foreach ($rows as $row) {
            $path = (string) ($row['path'] ?? '');
            if (!str_starts_with($path, 'anime/')) {
                continue;
            }
            if ($animeFilter !== '' && !str_contains($path, $animeFilter)) {
                continue;
            }
            $payload = (array) ($row['payload'] ?? []);
            $episodes = $payload['episode_list'] ?? $payload['episodeList'] ?? $payload['episodes'] ?? [];
            foreach ((array) $episodes as $episode) {
                $id = (string) ($episode['id'] ?? $episode['episode_id'] ?? $episode['episodeId'] ?? '');
                if ($id === '') {
                    continue;
                }
                $epPath = 'eps/' . $id;
                if (isset($existing[$epPath])) {
                    continue;
                }
                $missing[$epPath] = $id;
            }
        }

        $ids = array_values($missing);
        $limit = max(1, (int) $this->option('limit'));
        $ids = array_slice($ids, 0, $limit);
        $this->info('Missing selected: ' . count($ids));

        if ((bool) $this->option('dry-run')) {
            foreach ($ids as $id) {
                $this->line($id);
            }
            return self::SUCCESS;
        }

        $sleepUs = max(0, (int) $this->option('sleep-ms')) * 1000;
        $writeDb = (bool) $this->option('write-db');
        $ok = 0;
        $fail = 0;

        foreach ($ids as $i => $id) {
            $epPath = 'eps/' . $id;
            try {
                $payload = $service->episodeDetail($id, '')->toArray();
                $row = [
                    'snapshot_key' => md5($epPath),
                    'path' => $epPath,
                    'payload' => $payload,
                    'stored_at' => gmdate('Y-m-d H:i:s'),
                ];
                $rows[] = $row;
                $existing[$epPath] = true;
                if ($writeDb) {
                    \App\Models\ScraperSnapshot::query()->updateOrCreate(
                        ['snapshot_key' => $row['snapshot_key']],
                        ['path' => $row['path'], 'payload' => $row['payload'], 'stored_at' => $row['stored_at']]
                    );
                }
                $ok++;
                $hasStream = !empty($payload['stream_url']) || !empty($payload['stream_options']) ? '1' : '0';
                $this->line("[OK] {$id} stream={$hasStream}");
            } catch (Throwable $e) {
                $fail++;
                $this->warn("[ERR] {$id}: " . $e->getMessage());
            }

            if (($i + 1) % 10 === 0) {
                file_put_contents($file, json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                $this->info('Checkpoint saved at ' . ($i + 1));
            }

            if ($sleepUs > 0) {
                usleep($sleepUs);
            }
        }

        file_put_contents($file, json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->info("Done. ok={$ok} fail={$fail} total=" . count($rows));

        return self::SUCCESS;
    }
}
