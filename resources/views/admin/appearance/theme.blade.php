@extends('layouts.app')

@section('title', 'Tampilan & Tema — Admin Fer6origami')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center justify-between border-b border-white/10 pb-4">
        <div>
            <p class="text-xs uppercase tracking-wider text-brand-400 font-bold">Website Management</p>
            <h1 class="font-display text-2xl font-bold text-white">Tampilan, Tema & Branding</h1>
        </div>
        <a href="{{ route('home') }}" target="_blank" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white">
            Lihat Website &rarr;
        </a>
    </div>

    <form method="POST" action="{{ route('admin.appearance.theme') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- BRANDING / LOGO / FAVICON --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-brand-400 border-b border-white/5 pb-2">
                Branding Logo & Favicon
            </h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Upload Logo Website</label>
                    @if ($settings['site_logo'])
                        <div class="mb-2 flex items-center gap-3 rounded-xl bg-white/5 p-2">
                            <img src="{{ Storage::disk('public')->url($settings['site_logo']) }}" alt="Logo" class="h-8 object-contain">
                            <span class="text-[10px] text-slate-400">Logo aktif saat ini</span>
                        </div>
                    @endif
                    <input type="file" name="logo_upload" accept="image/*" class="w-full text-xs text-slate-400 file:mr-3 file:rounded-lg file:border-0 file:bg-white/10 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-white/20">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Upload Favicon (.ico / .png)</label>
                    @if ($settings['site_favicon'])
                        <div class="mb-2 flex items-center gap-3 rounded-xl bg-white/5 p-2">
                            <img src="{{ Storage::disk('public')->url($settings['site_favicon']) }}" alt="Favicon" class="h-6 w-6 object-contain">
                            <span class="text-[10px] text-slate-400">Favicon aktif saat ini</span>
                        </div>
                    @endif
                    <input type="file" name="favicon_upload" accept=".ico,.png,.svg" class="w-full text-xs text-slate-400 file:mr-3 file:rounded-lg file:border-0 file:bg-white/10 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-white/20">
                </div>
            </div>
        </div>

        {{-- COLOR THEME --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-brand-400 border-b border-white/5 pb-2">
                Palet Warna Tema
            </h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Warna Utama (Primary Color)</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="primary_color" value="{{ $settings['primary_color'] }}" class="h-10 w-16 cursor-pointer rounded-xl border border-white/10 bg-transparent p-1">
                        <input type="text" value="{{ $settings['primary_color'] }}" readonly class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-mono text-white">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Warna Aksen (Accent Color)</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="accent_color" value="{{ $settings['accent_color'] }}" class="h-10 w-16 cursor-pointer rounded-xl border border-white/10 bg-transparent p-1">
                        <input type="text" value="{{ $settings['accent_color'] }}" readonly class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-mono text-white">
                    </div>
                </div>
            </div>
        </div>

        {{-- FOOTER & SOCIAL LINKS --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-brand-400 border-b border-white/5 pb-2">
                Footer & Komunitas Sosial Media
            </h2>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Teks Copyright Footer</label>
                <textarea name="footer_text" rows="2" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">{{ $settings['footer_text'] }}</textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Discord URL</label>
                    <input type="url" name="social_discord" value="{{ $settings['social_discord'] }}" placeholder="https://discord.gg/..." class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs text-white">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Telegram URL</label>
                    <input type="url" name="social_telegram" value="{{ $settings['social_telegram'] }}" placeholder="https://t.me/..." class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs text-white">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Instagram URL</label>
                    <input type="url" name="social_instagram" value="{{ $settings['social_instagram'] }}" placeholder="https://instagram.com/..." class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs text-white">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <button type="submit" class="rounded-xl bg-brand-500 px-6 py-2.5 text-xs font-bold text-white shadow-glow hover:bg-brand-600">
                Simpan Tampilan & Tema
            </button>
        </div>
    </form>
</div>
@endsection
