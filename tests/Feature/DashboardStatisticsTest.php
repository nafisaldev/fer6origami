<?php

namespace Tests\Feature;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\User;
use App\Models\VisitorLog;
use App\Models\WatchHistory;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DashboardStatisticsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        WebsiteSetting::set('site_maintenance', '0');

        Http::fake([
            '*' => Http::response([
                'status' => 'success',
                'home' => ['on_going' => [], 'complete' => []],
                'animeList' => [],
                'genreList' => [],
            ], 200),
        ]);
    }

    public function test_visitor_tracking_and_dashboard_statistics(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $initialCount = VisitorLog::count();
        $randomIp = '192.168.20.' . rand(10, 99);

        // 1. Visit frontend page as guest from new IP
        $this->withServerVariables(['REMOTE_ADDR' => $randomIp])
            ->get(route('home'))
            ->assertStatus(200);

        // Assert visitor was logged
        $this->assertGreaterThan($initialCount, VisitorLog::count());

        // 2. Visit dashboard as admin
        $dashboardResponse = $this->actingAs($admin)->get(route('admin.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertViewHas('visitors', function ($visitors) {
            return $visitors['today'] >= 1 && $visitors['week'] >= 1 && $visitors['month'] >= 1;
        });
    }

    public function test_watch_activity_recording_for_anime(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $anime = Anime::create([
            'title' => 'Test Anime Watch',
            'slug' => 'test-anime-watch',
            'synopsis' => 'Test synopsis for anime watch',
            'status' => 'ongoing',
            'views' => 10,
        ]);

        $episode = Episode::create([
            'anime_id' => $anime->id,
            'slug' => 'test-anime-watch-episode-1',
            'episode_number' => 1,
            'title' => 'Episode 1',
            'video_url' => 'https://example.com/stream.mp4',
            'stream_url' => 'https://example.com/stream.mp4',
        ]);

        // Watch the episode
        $watchResponse = $this->get(route('watch.show', [
            'animeId' => $anime->slug,
            'episodeId' => 'db_ep_' . $episode->id,
        ]));
        $watchResponse->assertStatus(200);

        // Assert WatchHistory was recorded
        $this->assertDatabaseHas('watch_histories', [
            'anime_id' => $anime->id,
            'episode_id' => $episode->id,
        ]);

        // Check dashboard shows watch activity
        $dashboardResponse = $this->actingAs($admin)->get(route('admin.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertViewHas('watchActivityDays', function ($days) {
            $lastDay = end($days);
            return $lastDay['count'] >= 1;
        });
    }

    public function test_user_growth_chart_reflects_registrations(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        // Register 2 new users today
        User::factory()->count(2)->create();

        $dashboardResponse = $this->actingAs($admin)->get(route('admin.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertViewHas('userGrowthDays', function ($days) {
            $lastDay = end($days);
            // $admin + 2 new users = 3 users today
            return $lastDay['count'] >= 3;
        });
    }
}
