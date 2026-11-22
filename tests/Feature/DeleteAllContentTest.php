<?php

namespace Tests\Feature;

use App\Models\Anime;
use App\Models\DeletedAnime;
use App\Models\Episode;
use App\Models\User;
use App\Models\Watchlist;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DeleteAllContentTest extends TestCase
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

    public function test_non_super_admin_cannot_delete_all(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $anime = $this->anime();

        $this->actingAs($user)
            ->delete(route('admin.delete-all', 'animes'))
            ->assertStatus(403);

        $this->assertDatabaseHas('animes', ['id' => $anime->id]);
    }

    public function test_super_admin_can_delete_all_animes_with_cleanup(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $anime = $this->anime();

        $episode = Episode::create([
            'anime_id' => $anime->id,
            'slug' => 'delete-all-test-episode-' . uniqid(),
            'episode_number' => 1,
            'title' => 'Episode 1',
        ]);

        Watchlist::create([
            'user_id' => $admin->id,
            'anime_api_id' => $anime->slug,
            'anime_title' => $anime->title,
            'anime_url' => $anime->slug,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.delete-all', 'animes'))
            ->assertRedirect();

        $this->assertDatabaseMissing('animes', ['id' => $anime->id]);
        $this->assertDatabaseMissing('episodes', ['id' => $episode->id]);
        $this->assertDatabaseMissing('watchlists', ['anime_api_id' => $anime->slug]);
        $this->assertDatabaseMissing('deleted_animes', ['slug' => $anime->slug]);
    }

    public function test_global_delete_all_requires_exact_confirmation(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $anime = $this->anime();

        $this->actingAs($admin)
            ->delete(route('admin.system.delete-all-content.destroy'), ['confirmation' => 'hapus'])
            ->assertSessionHasErrors('confirmation');

        $this->assertDatabaseHas('animes', ['id' => $anime->id]);

        DeletedAnime::query()->delete();

        $this->actingAs($admin)
            ->delete(route('admin.system.delete-all-content.destroy'), ['confirmation' => 'HAPUS SEMUA KONTEN'])
            ->assertRedirect();

        $this->assertDatabaseMissing('animes', ['id' => $anime->id]);
    }

    private function anime(): Anime
    {
        return Anime::create([
            'title' => 'Delete All Test Anime',
            'slug' => 'delete-all-test-anime-' . uniqid(),
            'synopsis' => 'Synopsis for delete all test.',
            'status' => 'ongoing',
            'views' => 0,
        ]);
    }
}
