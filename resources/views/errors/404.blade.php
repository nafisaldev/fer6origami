@extends('layouts.app')

@section('title', '404 — Halaman Tidak Ditemukan | Fer6origami')

@section('content')
<div class="flex min-h-[60vh] flex-col items-center justify-center py-12 text-center">
    <div class="relative mb-6">
        <span class="font-display text-8xl font-black tracking-tight text-white/5 sm:text-9xl select-none">
            404
        </span>
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl border border-brand-500/30 bg-brand-500/10 shadow-glow">
                <svg class="h-8 w-8 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">
        Halaman Tidak Ditemukan
    </h1>
    <p class="mt-3 max-w-md text-sm leading-relaxed text-slate-400">
        Anime atau halaman yang Anda cari mungkin telah dipindahkan, berganti judul, atau tautan tidak lagi valid.
    </p>

    {{-- QUICK SEARCH BAR --}}
    <form action="{{ route('anime.index') }}" method="GET" class="mt-6 w-full max-w-md">
        <div class="relative">
            <input
                type="text"
                name="search"
                placeholder="Cari judul anime lain di Fer6origami..."
                class="w-full rounded-xl border border-white/10 bg-[#161924] py-3 pl-4 pr-24 text-xs text-white placeholder:text-slate-500 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
            >
            <button
                type="submit"
                class="absolute right-1.5 top-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-bold text-white transition hover:bg-brand-600"
            >
                Cari
            </button>
        </div>
    </form>

    {{-- ACTION BUTTONS --}}
    <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-xs font-bold text-white shadow-subtle transition hover:bg-brand-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Kembali ke Beranda</span>
        </a>
        <a href="{{ route('anime.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white">
            <span>Buka Katalog Lengkap</span>
        </a>
    </div>
</div>
@endsection
