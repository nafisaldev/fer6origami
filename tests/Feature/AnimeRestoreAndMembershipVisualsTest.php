<?php

namespace Tests\Feature;

use App\Models\Anime;
use App\Models\DeletedAnime;
use App\Models\Episode;
use App\Models\MembershipPlan;
use App\Models\User;
use App\Models\UserMembership;
use App\Models\WebsiteSetting;
use App\Services\DeletedAnimeRestoreService;
use App\Services\MembershipService;
use App\Services\OtakudesuApiService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Fluent;
use Tests\TestCase;

class AnimeRestoreAndMembershipVisualsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        WebsiteSetting::set('site_maintenance', '0');
    }

    public function test_restore_service_recreates_local_anime_and_removes_blacklist(): void
    {
        $this->mock(OtakudesuApiService::class, function ($mock): void {
            $mock->shouldReceive('animeDetail')->once()->andReturn(new Fluent([
                'title' => 'Restored Anime',
                'synopsis' => 'Sinopsis hasil pemulihan.',
                'poster_image' => null,
                'banner_image' => null,
                'studio' => 'Studio Pemulihan',
                'year' => 2025,
                'status' => 'Completed',
                'score' => 8.5,
                'type' => 'TV',
                'genres' => collect([
                    new Fluent(['id' => 'action', 'name' => 'Action']),
                ]),
            ]));
        });

        $blacklist = DeletedAnime::create([
            'slug' => 'restored-anime',
            'title' => 'Restored Anime',
        ]);

        $result = app(DeletedAnimeRestoreService::class)->restore($blacklist);

        $this->assertSame('restored', $result['status']);
        $this->assertDatabaseHas('animes', [
            'slug' => 'restored-anime',
            'title' => 'Restored Anime',
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('genres', ['slug' => 'action']);
        $this->assertDatabaseMissing('deleted_animes', ['id' => $blacklist->id]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'restored']);
    }

    public function test_restore_is_idempotent_when_anime_already_exists(): void
    {
        Anime::create([
            'title' => 'Existing Anime',
            'slug' => 'existing-anime',
            'synopsis' => 'Sudah ada.',
            'status' => 'ongoing',
            'rating' => 'PG-13',
            'score' => 8,
            'type' => 'TV',
        ]);

        $blacklist = DeletedAnime::create([
            'slug' => 'existing-anime',
            'title' => 'Existing Anime',
        ]);

        $result = app(DeletedAnimeRestoreService::class)->restore($blacklist);

        $this->assertSame('exists', $result['status']);
        $this->assertSame(1, Anime::where('slug', 'existing-anime')->count());
        $this->assertDatabaseMissing('deleted_animes', ['id' => $blacklist->id]);
    }

    public function test_admin_can_restore_from_blacklist_panel(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'status' => 'active']);

        $anime = Anime::create([
            'title' => 'Panel Restore Anime',
            'slug' => 'panel-restore-anime',
            'synopsis' => 'Sudah ada.',
            'status' => 'ongoing',
            'rating' => 'PG-13',
            'score' => 8,
            'type' => 'TV',
        ]);

        $blacklist = DeletedAnime::create([
            'slug' => $anime->slug,
            'title' => $anime->title,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.blacklist.restore', $blacklist))
            ->assertRedirect();

        $this->assertDatabaseMissing('deleted_animes', ['id' => $blacklist->id]);
    }

    public function test_membership_visual_and_quality_limit_follow_active_tier(): void
    {
        $service = app(MembershipService::class);
        $free = User::factory()->create();
        $premium = User::factory()->create();
        $vip = User::factory()->create();

        $this->grantTier($premium, MembershipPlan::PREMIUM);
        $this->grantTier($vip, MembershipPlan::VIP);

        $this->assertSame(720, $service->maxQuality($free));
        $this->assertSame(1080, $service->maxQuality($premium));
        $this->assertSame(2160, $service->maxQuality($vip));

        $this->assertSame('720p', $service->visual($free)['max_quality_label']);
        $this->assertSame('1080p', $service->visual($premium)['max_quality_label']);
        $this->assertSame('4K UHD', $service->visual($vip)['max_quality_label']);

        $this->assertNotSame(
            $service->visual($free)['comment_card'],
            $service->visual($premium)['comment_card']
        );
        $this->assertNotSame(
            $service->visual($premium)['comment_card'],
            $service->visual($vip)['comment_card']
        );

        $this->assertSame('1080p', $service->normalizeRequestedQuality('2160p', $premium));
        $this->assertSame(2160, $service->qualityRank('4K'));
    }

    public function test_free_viewer_cannot_watch_1080p_local_server(): void
    {
        $anime = Anime::create([
            'title' => 'Gate Test Anime',
            'slug' => 'gate-test-anime',
            'synopsis' => 'Uji batas kualitas.',
            'status' => 'ongoing',
            'rating' => 'PG-13',
            'score' => 8,
            'type' => 'TV',
        ]);

        $episode = Episode::create([
            'anime_id' => $anime->id,
            'title' => 'Episode 1',
            'slug' => 'gate-test-anime-episode-1',
            'episode_number' => 1,
            'is_published' => true,
            'servers' => [
                [
                    'name' => 'HD Server',
                    'url' => 'https://stream.example.test/ep1-1080',
                    'quality' => '1080p',
                ],
            ],
        ]);

        $response = $this->actingAs(User::factory()->create())->get(route('watch.show', [
            'animeId' => $anime->slug,
            'episodeId' => 'db_ep_' . $episode->id,
            'quality' => '1080p',
        ]));

        $response->assertOk();
        $response->assertSee('Kualitas yang diminta belum bisa diputar');
        $response->assertDontSee('https://stream.example.test/ep1-1080');
    }

    private function grantTier(User $user, string $slug): void
    {
        $plan = MembershipPlan::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => ucfirst($slug),
                'sort_order' => match ($slug) {
                    MembershipPlan::VIP => 3,
                    MembershipPlan::PREMIUM => 2,
                    default => 1,
                },
                'is_active' => true,
            ]
        );

        UserMembership::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => UserMembership::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDays(30),
            'source' => 'test',
        ]);
    }
}
