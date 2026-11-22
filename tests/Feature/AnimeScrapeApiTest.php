<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AnimeScrapeApiTest extends TestCase
{
    public function test_ongoing_anime_api_returns_expected_structure(): void
    {
        Cache::flush();

        Http::fake([
            'http://127.0.0.1:3000/api/ongoing/page/1' => Http::response([
                'status' => 'success',
                'baseUrl' => 'https://otakudesu.moe/ongoing-anime/',
                'animeList' => [
                    [
                        'id' => 'solo-leveling-sub-indo',
                        'title' => 'Solo Leveling',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/ongoing-anime?page=1');

        $response->assertOk()->assertExactJson([
            'status' => 'success',
            'baseUrl' => 'https://otakudesu.moe/ongoing-anime/',
            'animeList' => [
                [
                    'id' => 'solo-leveling-sub-indo',
                    'title' => 'Solo Leveling',
                ],
            ],
        ]);
    }

    public function test_anime_list_api_returns_expected_structure(): void
    {
        Cache::flush();

        Http::fake([
            'http://127.0.0.1:3000/api/anime-list' => Http::response([
                'status' => 'success',
                'baseUrl' => 'https://otakudesu.moe/anime-list/',
                'animeList' => [
                    [
                        'id' => 'bleach-sub-indo',
                        'title' => 'Bleach',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/anime-list');

        $response->assertOk()->assertExactJson([
            'status' => 'success',
            'baseUrl' => 'https://otakudesu.moe/anime-list/',
            'animeList' => [
                [
                    'id' => 'bleach-sub-indo',
                    'title' => 'Bleach',
                ],
            ],
        ]);
    }

    public function test_genres_api_returns_expected_structure(): void
    {
        Cache::flush();

        Http::fake([
            'http://127.0.0.1:3000/api/genres' => Http::response([
                'genreList' => [
                    [
                        'genre_name' => 'Action',
                        'id' => 'action',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/genres');

        $response->assertOk()->assertExactJson([
            'status' => 'success',
            'genreList' => [
                [
                    'genre_name' => 'Action',
                    'id' => 'action',
                ],
            ],
        ]);
    }

    public function test_search_api_returns_expected_structure(): void
    {
        Cache::flush();

        Http::fake([
            'http://127.0.0.1:3000/api/search/naruto' => Http::response([
                'status' => 'success',
                'search_results' => [
                    [
                        'id' => 'naruto-sub-indo',
                        'title' => 'Naruto',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/search?q=naruto');

        $response->assertOk()->assertExactJson([
            'status' => 'success',
            'query' => 'naruto',
            'search_results' => [
                [
                    'id' => 'naruto-sub-indo',
                    'title' => 'Naruto',
                ],
            ],
        ]);
    }
}
