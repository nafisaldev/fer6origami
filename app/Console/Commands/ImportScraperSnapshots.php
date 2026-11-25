<?php

namespace App\Console\Commands;

use App\Models\ScraperSnapshot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportScraperSnapshots extends Command
{
    protected $signature = 'scraper:import-snapshots
        {--force : Overwrite snapshots even when unchanged}';

    protected $description = 'Import the bundled scraper snapshots into the database for offline catalog browsing.';

    public function handle(): int
    {
        $file = database_path('data/scraper_snapshots.json');

        if (! is_file($file)) {
            $this->warn('Bundled snapshot file not found: ' . $file);

            return self::SUCCESS;
        }

        if (! Schema::hasTable('scraper_snapshots')) {
            $this->warn('scraper_snapshots table is not available; skipping import.');

            return self::SUCCESS;
        }

        $rows = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        $force = (bool) $this->option('force');
        $imported = 0;
        $skipped = 0;

        DB::transaction(function () use ($rows, $force, &$imported, &$skipped): void {
            foreach ($rows as $row) {
                $key = (string) data_get($row, 'snapshot_key', '');
                $path = (string) data_get($row, 'path', '');

                if ($key === '' || $path === '') {
                    $skipped++;
                    continue;
                }

                $payload = (array) data_get($row, 'payload', []);
                $storedAt = data_get($row, 'stored_at') ?: now();
                $existing = ScraperSnapshot::query()->where('snapshot_key', $key)->first();

                if ($existing && ! $force) {
                    $existingPayload = (array) ($existing->payload ?? []);
                    if ($existing->path === $path && $existingPayload == $payload) {
                        $skipped++;
                        continue;
                    }
                }

                ScraperSnapshot::query()->updateOrCreate(
                    ['snapshot_key' => $key],
                    [
                        'path' => $path,
                        'payload' => $payload,
                        'stored_at' => $storedAt,
                    ]
                );

                $imported++;
            }
        });

        Cache::forget('otakudesu:snapshot_anime_index');
        Cache::forget('otakudesu:snapshot_anime_items');

        $this->info("Imported {$imported} scraper snapshots, skipped {$skipped}.");

        return self::SUCCESS;
    }
}
