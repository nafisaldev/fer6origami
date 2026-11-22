<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OtakudesuApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScrapeRefreshController extends Controller
{
    public function __construct(private readonly OtakudesuApiService $api)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        @set_time_limit(300);

        $paths = (array) $request->input('paths', []);
        $pages = (int) $request->input('pages', 0);
        $allGenres = (bool) $request->input('all_genres', false);
        $deep = (bool) $request->input('deep', false);
        $limit = (int) $request->input('limit', 0);
        $animeLimit = (int) $request->input('anime_limit', 20);

        if (empty($paths)) {
            $paths = $this->api->warmSnapshotPaths(
                deep: $deep,
                customPages: $pages,
                allGenres: $allGenres,
                animeLimit: $animeLimit
            );
        }

        if ($limit > 0) {
            $paths = array_slice($paths, 0, $limit);
        }

        $results = $this->api->refreshSnapshots($paths);

        return response()->json([
            'status' => 'success',
            'scraped_count' => count($results),
            'results' => $results,
            'total_snapshots' => \App\Models\ScraperSnapshot::count(),
        ]);
    }
}
