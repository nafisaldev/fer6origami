<?php

namespace Tests\Feature;

use App\Models\Anime;
use App\Models\Announcement;
use App\Models\Review;
use App\Models\User;
use App\Models\WatchHistory;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HomeSectionsTest extends TestCase
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

    public function test_guest_home_shows_personal_and_filter_sections_with_honest_empty_states(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Continue Watching')
            ->assertSee('Popular This Week')
            ->assertSee('Belum ada anime populer yang tersedia.')
            ->assertSee('Masuk untuk menyimpan riwayat tonton')
            ->assertSee('Browse by Year and Score')
            ->assertSee(route('anime.index', ['year' => now()->year]), false)
            ->assertSee(route('anime.index', ['rating' => '9']), false)
            ->assertSee('Belum ada review yang dipublikasikan.')
            ->assertSee('Belum ada pengumuman aktif.');
    }

    public function test_home_shows_continue_watching_from_snapshot_without_creating_anime(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        WatchHistory::create([
            'user_id' => $user->id,
            'anime_id' => null,
            'anime_slug' => 'nippon-sangoku',
            'anime_title' => 'Nippon Sangoku',
            'episode_title' => 'Episode 12',
            'episode_route_id' => 'ep-12',
        ]);

        $animeCountBefore = Anime::count();

        $this->actingAs($user)->get(route('home'))
            ->assertOk()
            ->assertSee('Nippon Sangoku')
            ->assertSee('Episode 12')
            ->assertSee(route('watch.show', ['animeId' => 'nippon-sangoku', 'episodeId' => 'ep-12']), false)
            ->assertSee('Full History');

        $this->assertSame($animeCountBefore, Anime::count());
    }

    public function test_home_shows_published_announcements_and_reviews(): void
    {
        $announcement = Announcement::create([
            'title' => 'Maintenance terjadwal',
            'slug' => 'maintenance-terjadwal',
            'content' => '<p>Server akan diperbarui.</p>',
            'category' => 'Maintenance',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'is_pinned' => true,
        ]);

        $popularAnime = Anime::create([
            'title' => 'Popular Home Section Anime',
            'slug' => 'popular-home-section-anime',
            'synopsis' => 'Data uji untuk section populer.',
            'is_popular' => true,
        ]);

        $anime = Anime::create([
            'title' => 'Home Section Test Anime',
            'slug' => 'home-section-test-anime',
            'synopsis' => 'Data uji untuk section home.',
        ]);

        Review::create([
            'user_id' => User::factory()->create(['role' => 'user'])->id,
            'anime_id' => $anime->id,
            'rating' => 9,
            'title' => 'Review uji home',
            'content' => 'Animasi dan ceritanya menyenangkan.',
            'is_recommended' => true,
            'status' => 'published',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($popularAnime->title)
            ->assertSee($announcement->title)
            ->assertSee('Server akan diperbarui.')
            ->assertSee('Review uji home')
            ->assertSee('Animasi dan ceritanya menyenangkan.')
            ->assertSee(route('anime.show', $anime->route_id), false);
    }
}
