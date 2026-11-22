<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OtakudesuApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GenreAnimeController extends Controller
{
    public function __construct(private readonly OtakudesuApiService $api)
    {
    }

    public function __invoke(Request $request, string $genre): JsonResponse
    {
        return response()->json(
            $this->api->genreAnimeApi($genre, $request->integer('page', 1))
        );
    }
}
