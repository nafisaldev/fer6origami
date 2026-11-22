<?php

namespace Tests\Feature;

use App\Models\Anime;
use App\Models\User;
use App\Services\OtakudesuApiService;
use App\Services\UserEngagementService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Fluent;
use Mockery;
use Tests\TestCase;

class WatchHistoryWithoutAdminAnimeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_watching_api_anime_does_not_create_admin_anime(): void
    {
        Http::fake(['*' => Http::response(['status' => 'success'], 200)]);

        $anime = new Fluent([
            'id' => 'api-anime-42',
            'route_id' => 'api-anime-42',
            'title' => 'Anime dari API',
            'poster_image' => 'https://example.com/poster.jpg',
            'banner_image' => 'https://example.com/banner.jpg',
            'synopsis' => 'Sinopsis anime',
            'studio' => 'Studio API',
            'status' => 'ongoing',
            'genres' => collect(),
            'episodes' => collect(),
        ]);

        $episode = new Fluent([
            'id' => 'api-ep-1',
            'route_id' => 'api-ep-1',
            'title' => 'Episode 1',
            'episode_number' => 1,
            'stream_url' => null,
            'stream_quality' => null,
            'stream_label' => null,
            'stream_options' => collect(),
            'qualities' => collect(),
            'anime' => $anime,
            'synopsis' => null,
        ]);

        $api = Mockery::mock(OtakudesuApiService::class);
        $api->shouldReceive('decodeId')->andReturnUsing(fn (string $id) => $id);
        $api->shouldReceive('encodeId')->andReturnUsing(fn (string $id) => $id);
        $api->shouldReceive('animeDetail')->andReturn($anime);
        $api->shouldReceive('episodeDetail')->andReturn($episode);
        $api->shouldReceive('relatedAnime')->andReturn(collect());
        $this->app->instance(OtakudesuApiService::class, $api);

        $user = User::factory()->create();
        $animeCount = Anime::count();

        $this->actingAs($user)
            ->get(route('watch.show', ['animeId' => 'api-anime-42', 'episodeId' => 'api-ep-1']))
            ->assertStatus(200);

        $this->assertSame($animeCount, Anime::count());
        $this->assertDatabaseHas('watch_histories', [
            'user_id' => $user->id,
            'anime_id' => null,
            'anime_slug' => 'api-anime-42',
            'anime_title' => 'Anime dari API',
            'episode_title' => 'Episode 1',
        ]);

        $continue = app(UserEngagementService::class)->continueWatching($user->fresh());
        $this->assertCount(1, $continue);
        $this->assertSame('Anime dari API', $continue->first()['anime']->title);
    }
}
