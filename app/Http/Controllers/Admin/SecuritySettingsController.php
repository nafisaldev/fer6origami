<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecuritySettingsController extends Controller
{
    public function index(): View
    {
        $settings = [
            'maintenance_mode' => (bool) WebsiteSetting::get('maintenance_mode', false),
            'maintenance_message' => WebsiteSetting::get('maintenance_message', 'Website sedang dalam pemeliharaan berkala.'),
            'max_login_attempts' => WebsiteSetting::get('max_login_attempts', 5),
            'lockout_minutes' => WebsiteSetting::get('lockout_minutes', 15),
            'ip_blacklist' => WebsiteSetting::get('ip_blacklist', ''),
        ];

        return view('admin.settings.security', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'maintenance_mode' => ['nullable', 'boolean'],
            'maintenance_message' => ['required', 'string', 'max:1000'],
            'max_login_attempts' => ['required', 'integer', 'min:3', 'max:20'],
            'lockout_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'ip_blacklist' => ['nullable', 'string'],
        ]);

        $maintenance = $request->boolean('maintenance_mode') ? '1' : '0';
        $maintenanceMessage = (string) $request->input('maintenance_message');

        WebsiteSetting::set('maintenance_mode', $maintenance, 'security', 'boolean');
        WebsiteSetting::set('maintenance_message', $maintenanceMessage, 'security', 'textarea');
        // Legacy keys are still read by older consumers; keep them in sync.
        WebsiteSetting::set('site_maintenance', $maintenance);
        WebsiteSetting::set('site_maintenance_message', $maintenanceMessage);
        WebsiteSetting::set('max_login_attempts', $request->input('max_login_attempts'), 'security');
        WebsiteSetting::set('lockout_minutes', $request->input('lockout_minutes'), 'security');
        WebsiteSetting::set('ip_blacklist', $request->input('ip_blacklist'), 'security', 'textarea');

        ActivityLogger::log('Security', 'updated', 'Memperbarui konfigurasi keamanan dan maintenance mode', [
            'maintenance_mode' => $request->boolean('maintenance_mode'),
            'max_attempts' => $request->input('max_login_attempts'),
        ]);

        return back()->with('success', 'Pengaturan keamanan berhasil diperbarui.');
    }
}
