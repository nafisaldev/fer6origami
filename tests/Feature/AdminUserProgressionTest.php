<?php

namespace Tests\Feature;

use App\Models\DeletedAnime;
use App\Models\User;
use App\Models\UserWeeklyQuest;
use App\Models\WatchHistory;
use App\Models\Watchlist;
use App\Models\WebsiteSetting;
use App\Services\UserLevelService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminUserProgressionTest extends TestCase
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

    public function test_non_admin_cannot_use_progression_panels(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get(route('admin.engagement.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('admin.watch-activity.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('admin.badges.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('admin.blacklist.index'))->assertStatus(403);
        $this->actingAs($user)
            ->patch(route('admin.users.level.update', $user), ['level' => 5])
            ->assertStatus(403);
    }

    public function test_admin_can_set_user_level_and_locks_xp_to_manual_mode(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['role' => 'user', 'xp' => 0]);

        $this->actingAs($admin)
            ->patch(route('admin.users.level.update', $target), ['level' => 10])
            ->assertRedirect();

        $target->refresh();

        $this->assertSame(4500, $target->xp);
        $this->assertSame(10, $target->level);
        $this->assertTrue($target->xp_manual);
    }

    public function test_manual_level_is_not_overwritten_by_activity_sync(): void
    {
        $target = User::factory()->create(['role' => 'user', 'xp' => 0, 'xp_manual' => true]);

        WatchHistory::create([
            'user_id' => $target->id,
            'anime_slug' => 'manual-level-test',
            'anime_title' => 'Manual Level Test',
            'episode_title' => 'Episode 1',
        ]);

        $this->assertSame(0, UserLevelService::syncUserXp($target->fresh()));
        $this->assertSame(0, $target->fresh()->xp);
    }

    public function test_admin_can_restore_automatic_xp_sync(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['role' => 'user', 'xp' => 0, 'xp_manual' => true]);

        WatchHistory::create([
            'user_id' => $target->id,
            'anime_slug' => 'auto-sync-test',
            'anime_title' => 'Auto Sync Test',
            'episode_title' => 'Episode 1',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.level.update', $target), [
                'level' => 1,
                'auto_sync' => 1,
            ])
            ->assertRedirect();

        $target->refresh();

        $this->assertFalse($target->xp_manual);
        $this->assertGreaterThanOrEqual(125, $target->xp);
    }

    public function test_admin_can_award_manual_badge(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['role' => 'user']);

        $this->actingAs($admin)
            ->patch(route('admin.badges.update', $target), ['badges' => ['critic']])
            ->assertRedirect();

        $this->assertContains('critic', $target->fresh()->badges);

        $critic = collect(UserLevelService::getBadges($target->fresh()))
            ->firstWhere('id', 'critic');

        $this->assertTrue($critic['unlocked']);
        $this->assertTrue($critic['manual']);
    }

    public function test_all_new_admin_panels_are_reachable(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.engagement.index'))->assertOk()->assertSee('Quest & Streak');
        $this->actingAs($admin)->get(route('admin.watch-activity.index'))->assertOk()->assertSee('Riwayat & Watchlist');
        $this->actingAs($admin)->get(route('admin.badges.index'))->assertOk()->assertSee('Badge & Pencapaian');
        $this->actingAs($admin)->get(route('admin.blacklist.index'))->assertOk()->assertSee('Blacklist Anime');

        $target = User::factory()->create(['role' => 'user']);
        $this->actingAs($admin)->get(route('admin.users.show', $target))
            ->assertOk()
            ->assertSee('Level & XP Pengguna', false);
    }

    public function test_regular_admin_cannot_clear_all_new_resources_or_change_super_admin_level(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        foreach (['weekly-quests', 'watch-histories', 'watchlists', 'blacklist', 'badges'] as $resource) {
            $this->actingAs($admin)
                ->delete(route('admin.delete-all', $resource))
                ->assertStatus(403);
        }

        $this->actingAs($admin)
            ->patch(route('admin.users.level.update', $superAdmin), ['level' => 2])
            ->assertStatus(403);

        $this->actingAs($admin)
            ->patch(route('admin.badges.update', $superAdmin), ['badges' => ['critic']])
            ->assertStatus(403);
    }

    public function test_super_admin_can_clear_all_new_resources(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $target = User::factory()->create(['role' => 'user', 'badges' => ['critic']]);

        UserWeeklyQuest::create([
            'user_id' => $target->id,
            'week_start' => now()->startOfWeek()->toDateString(),
            'claimed_at' => now(),
        ]);

        WatchHistory::create([
            'user_id' => $target->id,
            'anime_slug' => 'cleanup-history-test',
            'anime_title' => 'Cleanup History Test',
        ]);

        Watchlist::create([
            'user_id' => $target->id,
            'anime_api_id' => 'cleanup-watchlist-test',
            'anime_title' => 'Cleanup Watchlist Test',
            'anime_url' => 'cleanup-watchlist-test',
        ]);

        DeletedAnime::blacklistAnime('Cleanup Blacklist Test', 'cleanup-blacklist-test', $admin->id);

        foreach (['weekly-quests', 'watch-histories', 'watchlists', 'blacklist', 'badges'] as $resource) {
            $this->actingAs($admin)
                ->delete(route('admin.delete-all', $resource))
                ->assertRedirect();
        }

        $this->assertDatabaseMissing('user_weekly_quests', ['user_id' => $target->id]);
        $this->assertDatabaseMissing('watch_histories', ['user_id' => $target->id]);
        $this->assertDatabaseMissing('watchlists', ['user_id' => $target->id]);
        $this->assertDatabaseMissing('deleted_animes', ['slug' => 'cleanup-blacklist-test']);
        $this->assertNull($target->fresh()->badges);
        $this->assertNotNull($target->fresh());
    }
}
