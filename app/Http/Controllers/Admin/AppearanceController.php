<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AppearanceController extends Controller
{
    public function index(): View
    {
        $settings = [
            'primary_color' => WebsiteSetting::get('primary_color', '#e53535'),
            'accent_color' => WebsiteSetting::get('accent_color', '#f97316'),
            'footer_text' => WebsiteSetting::get('footer_text', '© 2026 Fer6origami. All rights reserved.'),
            'site_logo' => WebsiteSetting::get('site_logo'),
            'site_favicon' => WebsiteSetting::get('site_favicon'),
            'social_discord' => WebsiteSetting::get('social_discord', 'https://discord.gg'),
            'social_telegram' => WebsiteSetting::get('social_telegram', 'https://t.me'),
            'social_instagram' => WebsiteSetting::get('social_instagram', 'https://instagram.com'),
        ];

        return view('admin.appearance.theme', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'primary_color' => ['required', 'string', 'max:20'],
            'accent_color' => ['required', 'string', 'max:20'],
            'footer_text' => ['required', 'string', 'max:1000'],
            'social_discord' => ['nullable', 'url', 'max:255'],
            'social_telegram' => ['nullable', 'url', 'max:255'],
            'social_instagram' => ['nullable', 'url', 'max:255'],
            'logo_upload' => ['nullable', 'image', 'max:2048'],
            'favicon_upload' => ['nullable', 'mimes:ico,png,jpg,svg', 'max:1024'],
        ]);

        WebsiteSetting::set('primary_color', $request->input('primary_color'), 'appearance');
        WebsiteSetting::set('accent_color', $request->input('accent_color'), 'appearance');
        WebsiteSetting::set('footer_text', $request->input('footer_text'), 'appearance', 'textarea');
        WebsiteSetting::set('social_discord', $request->input('social_discord'), 'appearance');
        WebsiteSetting::set('social_telegram', $request->input('social_telegram'), 'appearance');
        WebsiteSetting::set('social_instagram', $request->input('social_instagram'), 'appearance');

        if ($request->hasFile('logo_upload')) {
            $path = $request->file('logo_upload')->store('branding', 'public');
            WebsiteSetting::set('site_logo', $path, 'appearance', 'image');
        }

        if ($request->hasFile('favicon_upload')) {
            $path = $request->file('favicon_upload')->store('branding', 'public');
            WebsiteSetting::set('site_favicon', $path, 'appearance', 'image');
        }

        ActivityLogger::log('Appearance', 'updated', 'Memperbarui tema, branding, dan footer website');

        return back()->with('success', 'Pengaturan tampilan & tema berhasil diperbarui.');
    }
}
