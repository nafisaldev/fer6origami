<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class SeoController extends Controller
{
    public function index(): View
    {
        $settings = [
            'meta_title' => WebsiteSetting::get('meta_title', 'Fer6origami — Streaming Anime HD Sub Indo'),
            'meta_description' => WebsiteSetting::get('meta_description', 'Platform streaming anime terbaik dengan katalog terlengkap.'),
            'meta_keywords' => WebsiteSetting::get('meta_keywords', 'nonton anime, streaming anime, anime hd, fer6origami'),
            'canonical_url' => WebsiteSetting::get('canonical_url', 'https://fer6origami.com'),
            'og_image' => WebsiteSetting::get('og_image'),
            'robots_txt' => WebsiteSetting::get('robots_txt', "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /api/"),
        ];

        return view('admin.settings.seo', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'meta_title' => ['required', 'string', 'max:255'],
            'meta_description' => ['required', 'string', 'max:1000'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'canonical_url' => ['required', 'url', 'max:255'],
            'robots_txt' => ['required', 'string'],
            'og_image_upload' => ['nullable', 'image', 'max:3072'],
        ]);

        WebsiteSetting::set('meta_title', $request->input('meta_title'), 'seo');
        WebsiteSetting::set('meta_description', $request->input('meta_description'), 'seo', 'textarea');
        WebsiteSetting::set('meta_keywords', $request->input('meta_keywords'), 'seo');
        WebsiteSetting::set('canonical_url', $request->input('canonical_url'), 'seo');
        WebsiteSetting::set('robots_txt', $request->input('robots_txt'), 'seo', 'textarea');

        if ($request->hasFile('og_image_upload')) {
            $path = $request->file('og_image_upload')->store('seo', 'public');
            WebsiteSetting::set('og_image', $path, 'seo', 'image');
        }

        // Also write robots.txt to public/robots.txt if writable
        try {
            File::put(public_path('robots.txt'), $request->input('robots_txt'));
        } catch (\Throwable) {}

        ActivityLogger::log('SEO', 'updated', 'Memperbarui konfigurasi SEO, Meta Tags, dan robots.txt');

        return back()->with('success', 'Konfigurasi SEO berhasil diperbarui.');
    }
}
