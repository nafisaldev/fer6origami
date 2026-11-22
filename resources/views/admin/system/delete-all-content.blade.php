@extends('layouts.app')

@section('title', 'Hapus Semua Konten — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-rose-400">System Management</p>
        <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Hapus Semua Konten</h1>
        <p class="mt-1 text-xs text-slate-400">Kosongkan seluruh konten website sekaligus. Aksi ini permanen dan hanya tersedia untuk Super Admin.</p>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-xs text-rose-200">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="surface-panel rounded-2xl border border-rose-500/30 bg-rose-500/5 p-5">
        <div class="flex gap-3">
            <svg class="h-6 w-6 shrink-0 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <h2 class="text-sm font-bold text-rose-200">Tindakan berisiko tinggi</h2>
                <p class="mt-1 text-xs leading-relaxed text-slate-300">
                    Seluruh anime, episode, genre, studio, banner, pengumuman, notifikasi, halaman, komentar,
                    review, laporan, watchlist, riwayat tonton, log pengunjung, dan snapshot scraper akan dihapus.
                    File gambar yang tersimpan di storage juga ikut dihapus. Judul anime tetap dimasukkan ke daftar hitam
                    agar tidak muncul kembali dari API atau scraper.
                </p>
            </div>
        </div>
    </div>

    <div class="surface-panel rounded-2xl border border-white/10 p-5 shadow-elevated">
        <h2 class="text-sm font-bold text-white">Ringkasan konten saat ini</h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($counts as $item)
                <div class="flex items-center justify-between rounded-xl border border-white/5 bg-white/[0.02] px-4 py-3">
                    <span class="text-[11px] font-semibold text-slate-400">{{ $item['label'] }}</span>
                    <span class="font-display text-sm font-bold text-white">{{ number_format($item['count']) }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="surface-panel rounded-2xl border border-white/10 p-5">
        <h2 class="text-sm font-bold text-white">Yang tidak ikut terhapus</h2>
        <p class="mt-2 text-xs leading-relaxed text-slate-400">
            Akun pengguna, role &amp; permission, pengaturan website (termasuk SEO, tema, homepage, dan keamanan),
            log aktivitas admin, daftar hitam anime, serta file backup database sengaja dipertahankan.
        </p>
    </div>

    <form method="POST" action="{{ route('admin.system.delete-all-content.destroy') }}" class="surface-panel space-y-4 rounded-2xl border border-white/10 p-5">
        @csrf
        @method('DELETE')

        <div>
            <label for="confirmation" class="block text-xs font-bold text-slate-300">
                Ketik <code class="rounded bg-rose-500/15 px-1.5 py-0.5 font-mono text-rose-300">{{ $confirmationPhrase }}</code> untuk mengonfirmasi
            </label>
            <input
                type="text"
                id="confirmation"
                name="confirmation"
                autocomplete="off"
                spellcheck="false"
                placeholder="{{ $confirmationPhrase }}"
                class="mt-2 w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2.5 text-sm text-white placeholder:text-slate-600 focus:border-rose-500 focus:ring-0"
                required
            >
        </div>

        <div class="flex flex-col gap-3 border-t border-white/5 pt-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-[11px] text-slate-500">Pastikan Anda sudah membuat backup database sebelum melanjutkan.</p>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-600 px-5 py-2.5 text-xs font-bold text-white shadow-lg shadow-rose-900/30 transition hover:bg-rose-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus Semua Konten
            </button>
        </div>
    </form>
</div>
@endsection
