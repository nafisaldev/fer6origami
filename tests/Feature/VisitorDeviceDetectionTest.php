<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VisitorLog;
use App\Services\DeviceDetector;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VisitorDeviceDetectionTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            '*' => Http::response([
                'status' => 'success',
                'home' => ['on_going' => [], 'complete' => []],
                'animeList' => [],
                'genreList' => [],
            ], 200),
        ]);
    }

    public function test_device_detector_identifies_various_user_agents(): void
    {
        // 1. iPhone
        $iphoneUa = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Mobile/15E148 Safari/604.1';
        $iphone = DeviceDetector::detect($iphoneUa);
        $this->assertEquals('Apple iPhone', $iphone['device_name']);
        $this->assertEquals('Mobile', $iphone['device_type']);
        $this->assertEquals('iOS', $iphone['platform']);
        $this->assertEquals('Apple Safari', $iphone['browser']);

        // 2. Samsung Galaxy
        $samsungUa = 'Mozilla/5.0 (Linux; Android 14; SM-S918B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Mobile Safari/537.36';
        $samsung = DeviceDetector::detect($samsungUa);
        $this->assertStringContainsString('Samsung Galaxy', $samsung['device_name']);
        $this->assertEquals('Mobile', $samsung['device_type']);
        $this->assertEquals('Android', $samsung['platform']);
        $this->assertEquals('Google Chrome', $samsung['browser']);

        // 3. Windows 11 PC
        $windowsUa = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';
        $windows = DeviceDetector::detect($windowsUa);
        $this->assertEquals('Windows PC', $windows['device_name']);
        $this->assertEquals('Desktop', $windows['device_type']);
        $this->assertEquals('Windows 10/11', $windows['platform']);
        $this->assertEquals('Google Chrome', $windows['browser']);

        // 4. iPad
        $ipadUa = 'Mozilla/5.0 (iPad; CPU OS 16_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5 Mobile/15E148 Safari/604.1';
        $ipad = DeviceDetector::detect($ipadUa);
        $this->assertEquals('Apple iPad', $ipad['device_name']);
        $this->assertEquals('Tablet', $ipad['device_type']);
        $this->assertEquals('iPadOS', $ipad['platform']);

        // 5. Mac
        $macUa = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Safari/605.1.15';
        $mac = DeviceDetector::detect($macUa);
        $this->assertEquals('Apple Mac', $mac['device_name']);
        $this->assertEquals('Desktop', $mac['device_type']);
        $this->assertEquals('macOS', $mac['platform']);
    }

    public function test_visitor_tracking_middleware_records_device_name(): void
    {
        $uniqueIp = '198.51.100.' . rand(10, 250);
        $userAgent = 'Mozilla/5.0 (Linux; Android 14; SM-G998B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Mobile Safari/537.36';

        $this->withHeaders([
            'User-Agent' => $userAgent,
        ])->withServerVariables([
            'REMOTE_ADDR' => $uniqueIp,
        ])->get('/');

        $this->assertDatabaseHas('visitor_logs', [
            'ip_address' => $uniqueIp,
            'device_type' => 'Mobile',
            'browser' => 'Google Chrome',
        ]);

        $log = VisitorLog::where('ip_address', $uniqueIp)->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('Samsung Galaxy', $log->device_name);
    }

    public function test_admin_can_view_visitor_logs_with_devices(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        VisitorLog::create([
            'ip_address' => '203.0.113.19',
            'url' => 'http://localhost/anime/naruto',
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)',
            'device_name' => 'Apple iPhone',
            'device_type' => 'Mobile',
            'platform' => 'iOS',
            'browser' => 'Apple Safari',
            'user_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.visitor-logs.index'));
        $response->assertStatus(200);
        $response->assertSee('Nama Perangkat');
        $response->assertSee('Apple iPhone');
        $response->assertSee('203.0.113.19');
    }

    public function test_admin_can_delete_visitor_log(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $log = VisitorLog::create([
            'ip_address' => '203.0.113.55',
            'url' => 'http://localhost/',
            'user_agent' => 'Test UA',
            'device_name' => 'Test Device',
            'device_type' => 'Desktop',
            'platform' => 'Windows',
            'browser' => 'Chrome',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.visitor-logs.destroy', $log));
        $response->assertRedirect();

        $this->assertDatabaseMissing('visitor_logs', [
            'id' => $log->id,
        ]);
    }
}
