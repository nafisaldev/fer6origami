<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        Cache::flush();

        Http::fake([
            'http://127.0.0.1:3000/api/home' => Http::response([
                'status' => 'success',
                'baseUrl' => 'https://otakudesu.moe/',
                'home' => [
                    'on_going' => [
                        [
                            'id' => 'one-piece',
                            'title' => 'One Piece',
                            'thumb' => 'https://example.com/one-piece.jpg',
                            'score' => 9.1,
                        ],
                    ],
                    'complete' => [
                        [
                            'id' => 'naruto',
                            'title' => 'Naruto',
                            'thumb' => 'https://example.com/naruto.jpg',
                            'score' => 8.5,
                        ],
                    ],
                ],
            ], 200),
            'http://127.0.0.1:3000/api/anime/one-piece' => Http::response([
                'title' => 'One Piece',
                'thumb' => 'https://example.com/one-piece.jpg',
                'episode_list' => [
                    [
                        'id' => 'one-piece-episode-1',
                        'title' => 'Episode 1',
                        'uploaded_on' => 'Hari ini',
                    ],
                ],
            ], 200),
            'http://127.0.0.1:3000/api/anime-list' => Http::response([
                'status' => 'success',
                'baseUrl' => 'https://otakudesu.moe/anime-list/',
                'animeList' => [
                    [
                        'id' => 'one-piece',
                        'title' => 'One Piece',
                        'thumb' => 'https://example.com/one-piece.jpg',
                        'score' => 9.1,
                    ],
                ],
            ], 200),
            'http://127.0.0.1:3000/api/genres' => Http::response([
                'genreList' => [],
            ], 200),
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
