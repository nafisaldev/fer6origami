<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OtakudesuApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(private readonly OtakudesuApiService $api)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $query = (string) ($request->query('q') ?? $request->query('search') ?? '');

        return response()->json(
            $this->api->searchApi($query)
        );
    }
}
