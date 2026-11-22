<?php

namespace Tests\Feature;

use App\Models\DeletedAnime;
use App\Models\User;
use App\Models\WebsiteSetting;
use App\Services\OtakudesuApiService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Fluent;
use Tests\TestCase;

class CatalogAndMaintenanceFixTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        WebsiteSetting::set('site_maintenance', '0');
        WebsiteSetting::set('maintenance_mode', '0');

        Http::fake([
            '*' => Http::response([
                'status' => 'success',
                'home' => ['on_going' => [], 'complete' => []],
                'animeList' => [],
                'genreList' => [],
            ], 200),
        ]);
    }

    public function test_blacklist_identifier_variants_do_not_inflate_the_hidden_count(): void
    {
        $catalog = new Fluent([
            'items' => collect([
                new Fluent(['id' => 'hidden-slug', 'route_id' => 'hidden-slug', 'title' => 'Hidden Anime']),
                new Fluent(['id' => 'visible-slug', 'route_id' => 'visible-slug', 'title' => 'Visible Anime']),
            ]),
            'pagination' => new Fluent([
                'paginator' => new LengthAwarePaginator([], 10, 48, 1),
                'current_page' => 1,
                'previous_page' => null,
                'next_page' => null,
            ]),
        ]);

        $this->mock(OtakudesuApiService::class, function ($mock) use ($catalog): void {
            $mock->shouldReceive('catalog')->andReturn($catalog);
            $mock->shouldReceive('getGenres')->andReturn(collect());
            $mock->shouldReceive('scoreOptions')->andReturn([]);
            $mock->shouldReceive('yearOptions')->andReturn(collect());
        });

        DeletedAnime::create([
            'slug' => 'hidden-slug',
            'title' => 'Hidden Anime',
        ]);

        $response = $this->get(route('anime.index'));

        $response->assertOk();
        $this->assertSame(9, $response->viewData('catalogTotal'));
        $response->assertDontSee('Hidden Anime');
        $response->assertSee('Visible Anime');
    }

    public function test_admin_security_form_enables_maintenance_for_frontend_and_api(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'status' => 'active']);

        $this->actingAs($admin)->post('/admin/settings/security', [
            'maintenance_mode' => 1,
            'maintenance_message' => 'Sedang ada pemeliharaan server.',
            'max_login_attempts' => 5,
            'lockout_minutes' => 15,
        ])->assertRedirect();

        $this->assertEquals(1, WebsiteSetting::get('maintenance_mode'));
        $this->assertEquals(1, WebsiteSetting::get('site_maintenance'));

        $this->app['auth']->forgetGuards();

        $this->get('/')->assertStatus(503)->assertSee('Sedang ada pemeliharaan server.');
        $this->get('/anime')->assertStatus(503);
        $this->getJson('/api/anime-list')
            ->assertStatus(503)
            ->assertJsonPath('status', 'maintenance');

        $this->actingAs($admin)->get('/')->assertOk();
    }

    public function test_admin_can_turn_maintenance_back_off(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'status' => 'active']);

        WebsiteSetting::set('maintenance_mode', '1');
        WebsiteSetting::set('site_maintenance', '1');

        $this->actingAs($admin)->post('/admin/settings/security', [
            'maintenance_message' => 'Website kembali normal.',
            'max_login_attempts' => 5,
            'lockout_minutes' => 15,
        ])->assertRedirect();

        $this->assertEquals(0, WebsiteSetting::get('maintenance_mode'));
        $this->assertEquals(0, WebsiteSetting::get('site_maintenance'));

        $this->app['auth']->forgetGuards();

        $this->get('/')->assertOk();
    }
}
