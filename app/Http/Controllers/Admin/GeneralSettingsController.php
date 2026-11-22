<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\WebsiteSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GeneralSettingsController extends Controller
{
    public function index(): View
    {
        $settings = [
            'site_name' => WebsiteSetting::get('site_name', 'Fer6origami'),
            'site_tagline' => WebsiteSetting::get('site_tagline', 'Ultimate Anime Streaming Platform'),
            'site_email' => WebsiteSetting::get('site_email', 'support@fer6origami.com'),
            'allow_registration' => (bool) WebsiteSetting::get('allow_registration', true),
            'default_role' => WebsiteSetting::get('default_role', 'user'),
        ];

        return view('admin.settings.general', [
            'settings' => $settings,
            'roles' => Role::all(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'site_name' => ['required', 'string', 'max:100'],
            'site_tagline' => ['required', 'string', 'max:255'],
            'site_email' => ['required', 'email', 'max:255'],
            'allow_registration' => ['nullable', 'boolean'],
            'default_role' => ['required', 'string', 'exists:roles,slug'],
        ]);

        WebsiteSetting::set('site_name', $request->input('site_name'), 'general');
        WebsiteSetting::set('site_tagline', $request->input('site_tagline'), 'general');
        WebsiteSetting::set('site_email', $request->input('site_email'), 'general');
        WebsiteSetting::set('allow_registration', $request->boolean('allow_registration') ? '1' : '0', 'general', 'boolean');
        WebsiteSetting::set('default_role', $request->input('default_role'), 'general');

        ActivityLogger::log('Settings', 'general_updated', 'Memperbarui pengaturan umum website', [
            'site_name' => $request->input('site_name'),
            'site_email' => $request->input('site_email'),
        ]);

        return back()->with('success', 'Pengaturan umum berhasil diperbarui.');
    }
}
