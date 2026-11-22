<?php

namespace Tests\Feature;

use App\Models\Anime;
use App\Models\Announcement;
use App\Models\BroadcastNotification;
use App\Models\CustomPage;
use App\Models\Episode;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FrontendAdminEffectsTest extends TestCase
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

    public function test_banned_user_is_immediately_logged_out(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $this->actingAs($user);
        $this->get(route('watchlist.index'))->assertStatus(200);

        // Admin bans user
        $user->update([
            'status' => 'banned',
            'banned_reason' => 'Violation of community guidelines',
        ]);

        // User next request is immediately blocked and logged out
        $response = $this->get(route('watchlist.index'));
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_announcement_reads_tracker_and_public_page(): void
    {
        $announcement = Announcement::create([
            'title' => 'Server Maintenance Upgrade Test',
            'slug' => 'server-maintenance-upgrade-test-' . uniqid(),
            'content' => 'We are upgrading our streaming network for better speed.',
            'category' => 'Maintenance',
            'status' => 'published',
            'published_at' => now(),
            'reads_count' => 0,
        ]);

        $response = $this->get(route('announcements.show', $announcement->slug));
        $response->assertStatus(200);
        $response->assertSee('Server Maintenance Upgrade Test');
        $response->assertSee('We are upgrading our streaming network');

        $this->assertEquals(1, $announcement->fresh()->reads_count);
    }

    public function test_seo_settings_effect_on_frontend_and_robots(): void
    {
        WebsiteSetting::set('meta_title', 'Custom SEO Title by Admin');
        WebsiteSetting::set('meta_description', 'Custom SEO Description by Admin');
        WebsiteSetting::set('robots_txt', "User-agent: Googlebot\nAllow: /");

        $homeResponse = $this->get(route('home'));
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Custom SEO Title by Admin');
        $homeResponse->assertSee('Custom SEO Description by Admin');

        $robotsResponse = $this->get('/robots.txt');
        $robotsResponse->assertStatus(200);
        $robotsResponse->assertSee('User-agent: Googlebot');
    }

    public function test_sitemap_xml_generates_valid_urls(): void
    {
        $anime = Anime::create([
            'title' => 'Sitemap Target Anime ' . uniqid(),
            'slug' => 'sitemap-target-anime-' . uniqid(),
            'synopsis' => 'Synopsis for sitemap test',
            'status' => 'ongoing',
        ]);

        $sitemapResponse = $this->get('/sitemap.xml');
        $sitemapResponse->assertStatus(200);
        $sitemapResponse->assertHeader('Content-Type', 'application/xml');
        $sitemapResponse->assertSee($anime->route_id);
    }

    public function test_draft_episode_is_not_viewable_by_guests(): void
    {
        $anime = Anime::create([
            'title' => 'Draft Anime Test ' . uniqid(),
            'slug' => 'draft-anime-test-' . uniqid(),
            'synopsis' => 'Synopsis test',
            'status' => 'ongoing',
        ]);

        $draftEpisode = Episode::create([
            'anime_id' => $anime->id,
            'slug' => 'draft-ep-slug-' . uniqid(),
            'episode_number' => 99,
            'title' => 'Secret Draft Episode',
            'video_url' => 'https://example.com/test.mp4',
            'is_published' => false,
        ]);

        // Guest gets 404
        $guestResponse = $this->get(route('watch.show', [
            'animeId' => $anime->slug,
            'episodeId' => 'db_ep_' . $draftEpisode->id,
        ]));
        $guestResponse->assertStatus(404);

        // Admin can preview it
        $admin = User::factory()->create(['role' => 'super_admin']);
        $adminResponse = $this->actingAs($admin)->get(route('watch.show', [
            'animeId' => $anime->slug,
            'episodeId' => 'db_ep_' . $draftEpisode->id,
        ]));
        $adminResponse->assertStatus(200);
    }

    public function test_broadcast_notifications_and_mark_all_read(): void
    {
        $user = User::factory()->create();

        $notif = BroadcastNotification::create([
            'title' => 'New Episode Alert',
            'message' => 'Episode 5 has been published!',
            'type' => 'episode',
        ]);

        $this->actingAs($user);
        $home = $this->get(route('home'));
        $home->assertStatus(200);
        $home->assertSee('New Episode Alert');

        // Mark all as read
        $readResponse = $this->post(route('notifications.read-all'));
        $readResponse->assertRedirect();

        $this->assertDatabaseHas('user_notification_reads', [
            'notification_id' => $notif->id,
            'user_id' => $user->id,
        ]);
    }
}
