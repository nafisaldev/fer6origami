<?php

namespace Tests\Feature;

use App\Models\Anime;
use App\Models\Announcement;
use App\Models\Episode;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FrontendAdminIntegrationTest extends TestCase
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
    public function test_admin_created_anime_appears_on_homepage_and_catalog(): void
    {
        $uniq = uniqid();
        $anime = Anime::create([
            'title' => 'Chainsaw Man Frontend ' . $uniq,
            'slug' => 'chainsaw-man-frontend-' . $uniq,
            'synopsis' => 'A unique test anime created by admin for user display.',
            'is_trending' => true,
            'is_featured' => true,
            'status' => 'ongoing',
            'score' => 8.9,
            'year' => 2026,
        ]);

        // 1. Visit Homepage: must see the admin-created anime!
        $homeResponse = $this->get(route('home'));
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee($anime->title);

        // 2. Visit Catalog: must see the admin-created anime!
        $catalogResponse = $this->get(route('anime.index'));
        $catalogResponse->assertStatus(200);
        $catalogResponse->assertSee($anime->title);

        // 3. Visit Detail Page: must render anime overview!
        $detailResponse = $this->get(route('anime.show', ['animeId' => $anime->slug]));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee($anime->title);
    }

    public function test_pinned_announcement_appears_on_frontend_layout(): void
    {
        $announcement = Announcement::create([
            'title' => 'Important Maintenance Notice 2026',
            'slug' => 'important-maintenance-notice-2026-' . uniqid(),
            'content' => 'We are rolling out new 4K streaming servers tonight.',
            'category' => 'Important',
            'status' => 'published',
            'is_pinned' => true,
            'published_at' => now(),
        ]);

        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertSee('Important Maintenance Notice 2026');
        $response->assertSee('We are rolling out new 4K streaming servers tonight.');
    }

    public function test_user_can_submit_comment_and_report_for_admin(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $anime = Anime::create([
            'title' => 'Jujutsu Kaisen Test ' . uniqid(),
            'slug' => 'jujutsu-kaisen-test-' . uniqid(),
            'synopsis' => 'Testing user comments.',
            'status' => 'ongoing',
        ]);
        $episode = Episode::create([
            'anime_id' => $anime->id,
            'episode_number' => 1,
            'title' => 'Ryomen Sukuna',
            'slug' => 'ryomen-sukuna-' . uniqid(),
            'is_published' => true,
        ]);

        // 1. Submit comment
        $commentResponse = $this->actingAs($user)->post(route('comments.store'), [
            'anime_id' => $anime->id,
            'episode_id' => $episode->id,
            'content' => 'This episode is absolutely peak cinema!',
        ]);
        $commentResponse->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'content' => 'This episode is absolutely peak cinema!',
            'status' => 'published',
        ]);

        // 2. Submit report
        $reportResponse = $this->actingAs($user)->post(route('reports.store'), [
            'type' => 'broken_link',
            'description' => 'Player stream buffer stuck at 50%.',
        ]);
        $reportResponse->assertRedirect();
        $this->assertDatabaseHas('reports', [
            'user_id' => $user->id,
            'type' => 'broken_link',
            'description' => 'Player stream buffer stuck at 50%.',
            'status' => 'pending',
        ]);
    }

    public function test_maintenance_mode_blocks_guest_and_permits_admin(): void
    {
        WebsiteSetting::set('site_maintenance', '1');
        WebsiteSetting::set('site_maintenance_message', 'Emergency maintenance in progress.');

        // Guest user gets 503 maintenance page
        $guestResponse = $this->get(route('home'));
        $guestResponse->assertStatus(503);
        $guestResponse->assertSee('Emergency maintenance in progress.');

        // Admin user can still access site
        $admin = User::factory()->create(['role' => 'super_admin']);
        $adminResponse = $this->actingAs($admin)->get(route('home'));
        $adminResponse->assertStatus(200);

        // Turn off maintenance mode
        WebsiteSetting::set('site_maintenance', '0');
    }

    public function test_deleted_anime_disappears_completely_from_user_views(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $uniq = uniqid();

        $anime = Anime::create([
            'title' => 'Deleted Target Anime ' . $uniq,
            'slug' => 'deleted-target-anime-' . $uniq,
            'synopsis' => 'Anime that will be deleted by admin.',
            'status' => 'ongoing',
            'is_trending' => true,
        ]);

        // Verify it is visible before deletion
        $beforeHome = $this->get(route('home'));
        $beforeHome->assertStatus(200);
        $beforeHome->assertSee($anime->title);

        // Admin deletes the anime
        $deleteResponse = $this->actingAs($admin)->delete(route('admin.animes.destroy', $anime));
        $deleteResponse->assertRedirect();
        $this->assertDatabaseMissing('animes', ['id' => $anime->id]);
        $this->assertDatabaseHas('deleted_animes', ['slug' => $anime->slug]);

        // Verify it is completely gone from home
        $afterHome = $this->get(route('home'));
        $afterHome->assertStatus(200);
        $afterHome->assertDontSee($anime->title);

        // Verify it is completely gone from catalog
        $afterCatalog = $this->get(route('anime.index'));
        $afterCatalog->assertStatus(200);
        $afterCatalog->assertDontSee($anime->title);

        // Verify direct URL returns 404
        $afterDetail = $this->get(route('anime.show', ['animeId' => $anime->slug]));
        $afterDetail->assertStatus(404);
    }
}
