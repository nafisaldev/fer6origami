<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OtakudesuApiService;
use Illuminate\Http\JsonResponse;

class AnimeDetailController extends Controller
{
    public function __construct(private readonly OtakudesuApiService $api)
    {
    }

    public function __invoke(string $animeId): JsonResponse
    {
        $response = $this->api->animeDetailApi($animeId);

        if (! $this->api->hasAnimeDetailData((array) data_get($response, 'data', []))) {
            return response()->json([
                'status' => 'not_found',
                'animeId' => data_get($response, 'animeId', $animeId),
                'data' => [],
            ], 404);
        }

        return response()->json($response);
    }
}
