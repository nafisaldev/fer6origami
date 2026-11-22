@extends('layouts.app')

@section('title', 'Konfigurasi SEO & Search Engine — Admin Fer6origami')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center justify-between border-b border-white/10 pb-4">
        <div>
            <p class="text-xs uppercase tracking-wider text-brand-400 font-bold">Website Management</p>
            <h1 class="font-display text-2xl font-bold text-white">SEO & Pengaturan Metadata</h1>
        </div>
        <a href="{{ route('home') }}" target="_blank" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white">
            Lihat Website &rarr;
        </a>
    </div>

    <form method="POST" action="{{ route('admin.settings.seo') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- DEFAULT META TAGS --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-brand-400 border-b border-white/5 pb-2">
                Default Meta Tags & Identitas Web
            </h2>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Default Meta Title <span class="text-brand-500">*</span></label>
                <input type="text" name="meta_title" value="{{ $settings['meta_title'] }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Default Meta Description <span class="text-brand-500">*</span></label>
                <textarea name="meta_description" rows="3" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white leading-relaxed">{{ $settings['meta_description'] }}</textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Default Meta Keywords</label>
                    <input type="text" name="meta_keywords" value="{{ $settings['meta_keywords'] }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Canonical Base URL <span class="text-brand-500">*</span></label>
                    <input type="url" name="canonical_url" value="{{ $settings['canonical_url'] }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Upload Default Open Graph (OG) Share Image</label>
                @if ($settings['og_image'])
                    <div class="mb-2 flex items-center gap-3 rounded-xl bg-white/5 p-2">
                        <img src="{{ Storage::disk('public')->url($settings['og_image']) }}" alt="OG Image" class="h-10 w-16 object-cover rounded">
                        <span class="text-[10px] text-slate-400">OG Image aktif</span>
                    </div>
                @endif
                <input type="file" name="og_image_upload" accept="image/*" class="w-full text-xs text-slate-400 file:mr-3 file:rounded-lg file:border-0 file:bg-white/10 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-white/20">
            </div>
        </div>

        {{-- ROBOTS.TXT & SITEMAP --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-brand-400 border-b border-white/5 pb-2">
                Robots.txt & Sitemap Index
            </h2>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Isi Berkas robots.txt</label>
                <textarea name="robots_txt" rows="5" required class="w-full rounded-xl border border-white/10 bg-black/40 p-4 font-mono text-xs text-emerald-400 leading-relaxed">{{ $settings['robots_txt'] }}</textarea>
                <p class="mt-1 text-[11px] text-slate-500">File ini akan otomatis diperbarui di public/robots.txt untuk diindeks oleh Googlebot.</p>
            </div>

            <div class="rounded-xl border border-white/5 bg-white/5 p-4 text-xs space-y-2">
                <span class="font-bold text-white block">Sitemap Generator Status</span>
                <p class="text-slate-400">URL Sitemap XML Publik: <a href="{{ url('/sitemap.xml') }}" target="_blank" class="text-brand-400 font-mono hover:underline">{{ url('/sitemap.xml') }}</a></p>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <button type="submit" class="rounded-xl bg-brand-500 px-6 py-2.5 text-xs font-bold text-white shadow-glow hover:bg-brand-600">
                Simpan Konfigurasi SEO
            </button>
        </div>
    </form>
</div>
@endsection
