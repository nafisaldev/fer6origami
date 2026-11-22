@extends('layouts.app')

@section('title', '419 — Sesi Kedaluwarsa | Fer6origami')

@section('content')
<div class="flex min-h-[60vh] flex-col items-center justify-center py-12 text-center">
    <div class="relative mb-6">
        <span class="font-display text-8xl font-black tracking-tight text-white/5 sm:text-9xl select-none">
            419
        </span>
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl border border-white/20 bg-white/10 shadow-glow">
                <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">
        Sesi Anda Telah Berakhir
    </h1>
    <p class="mt-3 max-w-md text-sm leading-relaxed text-slate-400">
        Halaman telah terbuka terlalu lama sehingga token keamanan formulir kedaluwarsa. Silakan muat ulang halaman untuk melanjutkan.
    </p>

    {{-- ACTION BUTTONS --}}
    <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
        <button onclick="window.location.reload()" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-xs font-bold text-white shadow-subtle transition hover:bg-brand-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>Muat Ulang Halaman</span>
        </button>
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white">
            <span>Kembali ke Beranda</span>
        </a>
    </div>
</div>
@endsection
