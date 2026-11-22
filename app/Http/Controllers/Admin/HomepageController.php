<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Banner;
use App\Models\WebsiteSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomepageController extends Controller
{
    public function index(): View
    {
        $banners = Banner::orderBy('sort_order')->get();
        $featuredAnimes = Anime::where('is_featured', true)->get();
        $trendingAnimes = Anime::where('is_trending', true)->get();
        $allAnimes = Anime::orderBy('title')->get(['id', 'title', 'is_featured', 'is_trending', 'is_popular']);

        $settings = [
            'hero_style' => WebsiteSetting::get('hero_style', 'carousel'),
            'show_trending' => (bool) WebsiteSetting::get('show_trending', true),
            'show_ongoing' => (bool) WebsiteSetting::get('show_ongoing', true),
            'show_completed' => (bool) WebsiteSetting::get('show_completed', true),
            'show_schedule' => (bool) WebsiteSetting::get('show_schedule', true),
        ];

        return view('admin.appearance.homepage', [
            'banners' => $banners,
            'featuredAnimes' => $featuredAnimes,
            'trendingAnimes' => $trendingAnimes,
            'allAnimes' => $allAnimes,
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'hero_style' => ['required', 'string', 'in:carousel,single,minimal'],
            'show_trending' => ['nullable', 'boolean'],
            'show_ongoing' => ['nullable', 'boolean'],
            'show_completed' => ['nullable', 'boolean'],
            'show_schedule' => ['nullable', 'boolean'],
            'featured_ids' => ['nullable', 'array'],
            'featured_ids.*' => ['exists:animes,id'],
        ]);

        WebsiteSetting::set('hero_style', $request->input('hero_style'), 'homepage');
        WebsiteSetting::set('show_trending', $request->boolean('show_trending') ? '1' : '0', 'homepage', 'boolean');
        WebsiteSetting::set('show_ongoing', $request->boolean('show_ongoing') ? '1' : '0', 'homepage', 'boolean');
        WebsiteSetting::set('show_completed', $request->boolean('show_completed') ? '1' : '0', 'homepage', 'boolean');
        WebsiteSetting::set('show_schedule', $request->boolean('show_schedule') ? '1' : '0', 'homepage', 'boolean');

        // Sync featured animes
        $featuredIds = $request->input('featured_ids', []);
        Anime::whereNotIn('id', $featuredIds)->where('is_featured', true)->update(['is_featured' => false]);
        if (!empty($featuredIds)) {
            Anime::whereIn('id', $featuredIds)->update(['is_featured' => true]);
        }

        ActivityLogger::log('Homepage', 'updated', 'Memperbarui konfigurasi sections & featured homepage', [
            'hero_style' => $request->input('hero_style'),
            'featured_count' => count($featuredIds),
        ]);

        return back()->with('success', 'Konfigurasi homepage berhasil disimpan.');
    }
}
