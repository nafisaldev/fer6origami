<?php

namespace Tests\Feature;

use App\Models\Anime;
use App\Models\User;
use App\Models\WatchHistory;
use App\Models\WebsiteSetting;
use App\Services\UserEngagementService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UserEngagementTest extends TestCase
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

    public function test_streak_counts_consecutive_watch_days(): void
    {
        $user = User::factory()->create();
        $anime = $this->anime();

        foreach ([0, 1, 2, 4] as $daysAgo) {
            $history = WatchHistory::create([
                'user_id' => $user->id,
                'anime_id' => $anime->id,
                'anime_slug' => $anime->slug,
                'episode_title' => 'Episode 1',
            ]);

            $history->timestamps = false;
            $history->created_at = now()->subDays($daysAgo);
            $history->save();
        }

        $streak = (new UserEngagementService())->streak($user);

        $this->assertSame(3, $streak['current']);
        $this->assertSame(3, $streak['best']);
        $this->assertTrue($streak['watched_today']);
        $this->assertCount(7, $streak['days']);
    }

    public function test_weekly_quest_claim_awards_xp_once(): void
    {
        $user = User::factory()->create(['xp' => 0]);
        $anime = $this->anime();

        for ($i = 0; $i < 5; $i++) {
            WatchHistory::create([
                'user_id' => $user->id,
                'anime_id' => $anime->id,
                'anime_slug' => $anime->slug,
                'episode_title' => 'Episode ' . ($i + 1),
            ]);
        }

        $response = $this->actingAs($user)->post(route('profile.weekly-quest.claim'));

        $response->assertRedirect();
        $this->assertSame(100, $user->fresh()->xp);
        $this->assertDatabaseHas('user_weekly_quests', [
            'user_id' => $user->id,
            'week_start' => now()->startOfWeek()->toDateString(),
        ]);

        $this->actingAs($user)
            ->post(route('profile.weekly-quest.claim'))
            ->assertStatus(403);

        $this->assertSame(100, $user->fresh()->xp);
    }

    public function test_profile_page_exposes_engagement_data(): void
    {
        $user = User::factory()->create();
        $anime = $this->anime();

        WatchHistory::create([
            'user_id' => $user->id,
            'anime_id' => $anime->id,
            'anime_slug' => $anime->slug,
            'episode_title' => 'Episode 1',
        ]);

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertViewHas('streak', fn ($streak) => $streak['current'] === 1)
            ->assertViewHas('weeklyQuest', fn ($quest) => $quest['progress'] === 1)
            ->assertViewHas('continueWatching', fn ($items) => $items->count() === 1);
    }

    public function test_profile_is_reachable_from_navbar(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee(route('profile.show'))
            ->assertSee('Lv. ' . $user->level);
    }

    private function anime(): Anime
    {
        return Anime::create([
            'title' => 'Engagement Test Anime',
            'slug' => 'engagement-test-anime-' . uniqid(),
            'synopsis' => 'Synopsis for engagement test.',
            'status' => 'ongoing',
            'views' => 0,
        ]);
    }
}
