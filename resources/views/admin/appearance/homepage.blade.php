@extends('layouts.app')

@section('title', 'Pengaturan Homepage — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.25em] text-brand-400 font-bold">Website Management</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Pengaturan Homepage</h1>
            <p class="mt-1 text-xs text-slate-400">Atur hero carousel, anime featured, urutan konten, dan visibilitas section beranda.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.banners.index') }}" class="rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white hover:bg-white/20">
                Kelola Banner Hero &rarr;
            </a>
            <a href="{{ route('home') }}" target="_blank" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white">
                Preview Beranda
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.appearance.homepage') }}" class="space-y-6">
        @csrf

        {{-- HERO STYLE & SECTION VISIBILITY --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated space-y-5">
            <h2 class="text-xs font-bold uppercase tracking-wider text-brand-400 border-b border-white/5 pb-2">
                Tata Letak & Visibilitas Bagian Beranda
            </h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Gaya Tampilan Hero Banner</label>
                    <select name="hero_style" class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2.5 text-xs text-white">
                        <option value="carousel" @selected($settings['hero_style'] === 'carousel')>Slider Carousel Interaktif</option>
                        <option value="single" @selected($settings['hero_style'] === 'single')>Single Static Hero Spotlight</option>
                        <option value="minimal" @selected($settings['hero_style'] === 'minimal')>Minimal Compact Banner</option>
                    </select>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 pt-2">
                <label class="flex items-center gap-2.5 rounded-xl border border-white/5 bg-white/5 p-3 text-xs text-slate-300 cursor-pointer">
                    <input type="checkbox" name="show_trending" value="1" @checked($settings['show_trending']) class="rounded border-white/20 bg-[#161924] text-brand-500 focus:ring-0">
                    <span class="font-bold text-white">Tampilkan Section Trending</span>
                </label>
                <label class="flex items-center gap-2.5 rounded-xl border border-white/5 bg-white/5 p-3 text-xs text-slate-300 cursor-pointer">
                    <input type="checkbox" name="show_ongoing" value="1" @checked($settings['show_ongoing']) class="rounded border-white/20 bg-[#161924] text-brand-500 focus:ring-0">
                    <span class="font-bold text-white">Tampilkan Anime Ongoing</span>
                </label>
                <label class="flex items-center gap-2.5 rounded-xl border border-white/5 bg-white/5 p-3 text-xs text-slate-300 cursor-pointer">
                    <input type="checkbox" name="show_completed" value="1" @checked($settings['show_completed']) class="rounded border-white/20 bg-[#161924] text-brand-500 focus:ring-0">
                    <span class="font-bold text-white">Tampilkan Anime Completed</span>
                </label>
                <label class="flex items-center gap-2.5 rounded-xl border border-white/5 bg-white/5 p-3 text-xs text-slate-300 cursor-pointer">
                    <input type="checkbox" name="show_schedule" value="1" @checked($settings['show_schedule']) class="rounded border-white/20 bg-[#161924] text-brand-500 focus:ring-0">
                    <span class="font-bold text-white">Tampilkan Jadwal Rilis</span>
                </label>
            </div>
        </div>

        {{-- FEATURED ANIME SELECTION --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated space-y-4">
            <div class="flex items-center justify-between border-b border-white/5 pb-2">
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-brand-400">Pilih Anime Featured di Homepage</h2>
                    <p class="text-xs text-slate-400">Centang anime yang ingin dijadikan sorotan utama (featured showcase) di halaman depan.</p>
                </div>
                <span class="text-xs text-brand-400 font-bold">{{ $featuredAnimes->count() }} dipilih</span>
            </div>

            @if ($allAnimes->isEmpty())
                <p class="py-6 text-center text-xs text-slate-500">Belum ada anime di database.</p>
            @else
                <div class="max-h-80 overflow-y-auto rounded-xl border border-white/10 bg-white/5 p-4 scrollbar-thin">
                    <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($allAnimes as $anime)
                            <label class="flex items-center gap-2.5 rounded-lg border border-white/5 bg-[#161924] p-2.5 text-xs text-slate-300 hover:border-white/10 cursor-pointer">
                                <input type="checkbox" name="featured_ids[]" value="{{ $anime->id }}" @checked($anime->is_featured) class="rounded border-white/20 bg-[#11131b] text-brand-500 focus:ring-0">
                                <span class="font-bold text-white truncate">{{ $anime->title }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <button type="submit" class="rounded-xl bg-brand-500 px-6 py-2.5 text-xs font-bold text-white shadow-glow hover:bg-brand-600">
                Simpan Pengaturan Homepage
            </button>
        </div>
    </form>
</div>
@endsection
