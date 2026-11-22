<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiContractFixTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_search_falls_back_to_cached_anime_list_when_upstream_is_empty(): void
    {
        Http::fake([
            'http://127.0.0.1:3000/api/anime-list' => Http::response([
                'status' => 'success',
                'animeList' => [
                    [
                        'id' => 'naruto-shippuuden-sub-indo',
                        'title' => 'Naruto Shippuuden',
                        'thumb' => 'https://example.com/naruto.jpg',
                    ],
                ],
            ], 200),
            'http://127.0.0.1:3000/api/search/naruto' => Http::response([], 200),
        ]);

        $response = $this->getJson('/api/search?q=naruto');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('query', 'naruto')
            ->assertJsonPath('search_results.0.id', 'naruto-shippuuden-sub-indo');
    }

    public function test_v1_compatibility_routes_are_registered(): void
    {
        $uris = collect(Route::getRoutes())
            ->map(fn ($route) => $route->uri())
            ->all();

        foreach ([
            'api/v1/home',
            'api/v1/catalog',
            'api/v1/anime-list',
            'api/v1/search',
            'api/v1/anime/{animeId}',
            'api/v1/episode/{episodeId}',
        ] as $uri) {
            $this->assertContains($uri, $uris);
        }
    }

    public function test_unknown_anime_detail_returns_not_found(): void
    {
        Http::fake([
            'http://127.0.0.1:3000/api/anime/does-not-exist-xyz' => Http::response([], 200),
        ]);

        $this->getJson('/api/anime/does-not-exist-xyz')
            ->assertNotFound()
            ->assertJsonPath('status', 'not_found')
            ->assertJsonPath('animeId', 'does-not-exist-xyz')
            ->assertJsonPath('data', []);
    }

    public function test_unknown_episode_detail_returns_not_found(): void
    {
        Http::fake([
            'http://127.0.0.1:3000/api/eps/does-not-exist-xyz' => Http::response([], 200),
        ]);

        $this->getJson('/api/episode/does-not-exist-xyz')
            ->assertNotFound()
            ->assertJsonPath('status', 'not_found')
            ->assertJsonPath('episodeId', 'does-not-exist-xyz')
            ->assertJsonPath('data', []);
    }
}
