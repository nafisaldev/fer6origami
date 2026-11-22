<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OtakudesuApiService;
use Illuminate\Http\JsonResponse;

class EpisodeStreamController extends Controller
{
    public function __construct(private readonly OtakudesuApiService $api)
    {
    }

    public function __invoke(string $episodeId): JsonResponse
    {
        $response = $this->api->episodeDetailApi($episodeId);

        if (! $this->api->hasEpisodeDetailData(
            (string) data_get($response, 'episodeId', $episodeId),
            (array) data_get($response, 'data', [])
        )) {
            return response()->json([
                'status' => 'not_found',
                'episodeId' => data_get($response, 'episodeId', $episodeId),
                'data' => [],
            ], 404);
        }

        return response()->json($response);
    }
}
