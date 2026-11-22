<?php

namespace App\Console\Commands;

use App\Services\OtakudesuApiService;
use Illuminate\Console\Command;

class RefreshScraperSnapshots extends Command
{
    protected $signature = 'scraper:refresh-snapshots
        {--path=* : Refresh only specific scraper paths}
        {--pages=0 : Number of pages of complete and ongoing anime to scrape}
        {--all-genres : Scrape anime from all available genres}
        {--deep : Warm more public genre pages and anime details}
        {--anime-limit=20 : Maximum number of specific anime details to warm when deep is enabled}
        {--limit=0 : Only process the first N generated paths}';

    protected $description = 'Refresh local scraper snapshots for public AnimeStream pages with support for multi-page batch scraping.';

    public function handle(OtakudesuApiService $service): int
    {
        $paths = $this->option('path');

        if ($paths === []) {
            $paths = $service->warmSnapshotPaths(
                deep: (bool) $this->option('deep'),
                customPages: (int) $this->option('pages'),
                allGenres: (bool) $this->option('all-genres'),
                animeLimit: (int) $this->option('anime-limit')
            );
        }

        $limit = max(0, (int) $this->option('limit'));

        if ($limit > 0) {
            $paths = array_slice($paths, 0, $limit);
        }

        $this->info('Refreshing ' . count($paths) . ' scraper snapshots...');

        $results = $service->refreshSnapshots($paths);

        foreach ($results as $path => $status) {
            $line = str_starts_with($status, 'ok')
                ? "<info>OK</info> {$path}"
                : "<comment>{$status}</comment> {$path}";

            $this->line($line);
        }

        $this->info('Scraper refresh completed! Total snapshots in database: ' . \App\Models\ScraperSnapshot::count());

        return self::SUCCESS;
    }
}
