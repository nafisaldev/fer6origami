<?php

namespace Tests\Feature;

use App\Models\Anime;
use App\Models\Role;
use App\Models\Studio;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use DatabaseTransactions;
    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Pusat Kendali Fer6origami');
    }

    public function test_admin_can_view_analytics_and_export(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.analytics.index'));
        $response->assertStatus(200);

        $exportResponse = $this->actingAs($admin)->get(route('admin.analytics.export'));
        $exportResponse->assertStatus(200);
    }

    public function test_admin_can_create_studio(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.studios.store'), [
            'name' => 'Wit Studio Test',
            'country' => 'Japan',
            'founded_year' => 2012,
            'website' => 'https://witstudio.co.jp',
            'description' => 'Animation studio test',
        ]);

        $response->assertRedirect(route('admin.studios.index'));
        $this->assertDatabaseHas('studios', [
            'name' => 'Wit Studio Test',
        ]);
    }

    public function test_admin_can_ban_and_unban_user(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $targetUser = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);

        $banResponse = $this->actingAs($admin)->post(route('admin.users.ban', $targetUser), [
            'status' => 'banned',
            'banned_reason' => 'Spamming toxic comments',
        ]);

        $banResponse->assertRedirect();
        $this->assertEquals('banned', $targetUser->fresh()->status);

        $unbanResponse = $this->actingAs($admin)->post(route('admin.users.unban', $targetUser));
        $unbanResponse->assertRedirect();
        $this->assertEquals('active', $targetUser->fresh()->status);
    }

    public function test_admin_can_clear_cache(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.system.cache.clear'), [
            'type' => 'application',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_admin_can_manage_anime_and_bulk_actions(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $uniq = uniqid();
        $anime = Anime::create([
            'title' => 'Chainsaw Man ' . $uniq,
            'slug' => 'chainsaw-man-' . $uniq,
            'synopsis' => 'Test synopsis description for anime.',
            'is_featured' => false,
            'status' => 'ongoing',
        ]);

        // Toggle featured bulk action
        $bulkResponse = $this->actingAs($admin)->post(route('admin.animes.bulk'), [
            'bulk_action' => 'feature',
            'selected_ids' => [$anime->id],
        ]);
        $bulkResponse->assertRedirect();
        $this->assertTrue((bool) $anime->fresh()->is_featured);

        // Bulk status change
        $bulkStatusResponse = $this->actingAs($admin)->post(route('admin.animes.bulk'), [
            'bulk_action' => 'status_completed',
            'selected_ids' => [$anime->id],
        ]);
        $bulkStatusResponse->assertRedirect();
        $this->assertEquals('completed', $anime->fresh()->status);
    }

    public function test_admin_can_create_and_pin_announcement(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.announcements.store'), [
            'title' => 'Scheduled Server Maintenance',
            'category' => 'Maintenance',
            'content' => 'Server will be undergoing routine maintenance at midnight.',
            'status' => 'published',
            'is_pinned' => 1,
            'is_popup' => 0,
        ]);

        $response->assertRedirect(route('admin.announcements.index'));
        $this->assertDatabaseHas('announcements', [
            'title' => 'Scheduled Server Maintenance',
            'category' => 'Maintenance',
            'is_pinned' => 1,
        ]);
    }

    public function test_admin_can_resolve_report(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $report = \App\Models\Report::create([
            'user_id' => $admin->id,
            'type' => 'broken_link',
            'description' => 'Server 1 gives 404 error.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.reports.update', $report), [
            'status' => 'resolved',
            'admin_notes' => 'Switched fallback mirror to direct CDN.',
        ]);

        $response->assertRedirect();
        $this->assertEquals('resolved', $report->fresh()->status);
        $this->assertEquals('Switched fallback mirror to direct CDN.', $report->fresh()->admin_notes);
    }

    public function test_admin_can_view_activity_logs(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        \App\Services\ActivityLogger::log('anime', 'create', 'Admin created test anime', ['title' => 'Test']);

        $response = $this->actingAs($admin)->get(route('admin.activity-logs.index'));
        $response->assertStatus(200);
        $response->assertSee('Admin created test anime');
    }
}
