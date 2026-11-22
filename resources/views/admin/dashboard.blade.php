@extends('layouts.app')

@section('title', 'Admin Dashboard Overview — Fer6origami')

@section('content')
<div class="space-y-8">
    {{-- TOP BANNER / GREETING --}}
    <div class="surface-panel relative overflow-hidden rounded-2xl border border-white/10 p-6 shadow-elevated">
        <div class="absolute -right-10 -top-10 h-64 w-64 rounded-full bg-brand-500/10 blur-3xl pointer-events-none"></div>
        <div class="flex min-w-0 flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-[11px] font-bold text-emerald-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Sistem Aktif & Terkendali
                    </span>
                    <span class="truncate text-xs text-slate-400">• {{ now()->translatedFormat('l, d F Y') }}</span>
                </div>
                <h1 class="mt-2 font-display text-2xl font-extrabold text-white sm:text-3xl">
                    Pusat Kendali Fer6origami
                </h1>
                <p class="mt-1 text-xs text-slate-300">
                    Kelola seluruh aspek katalog anime, episode, user, laporan, pengumuman, dan infrastruktur sistem dari satu dashboard.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('admin.animes.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-bold text-white shadow-glow transition hover:bg-brand-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Tambah Anime</span>
                </a>
                <a href="{{ route('admin.episodes.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs font-semibold text-slate-200 transition hover:bg-white/10 hover:text-white">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Upload Episode</span>
                </a>
            </div>
        </div>
    </div>

    {{-- 1. OVERVIEW 8 STAT CARDS --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 xl:grid-cols-8">
        {{-- Total Users --}}
        <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-4 transition hover:border-brand-500/30">
            <div class="flex min-w-0 items-center justify-between gap-2">
                <span class="truncate text-[10px] font-bold uppercase tracking-wider text-slate-400">Total User</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-500/10 text-blue-400">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-2 font-display text-2xl font-bold text-white">{{ number_format($stats['total_users']) }}</p>
            <p class="mt-1 text-[10px] text-emerald-400 font-semibold">{{ $stats['active_users'] }} aktif</p>
        </div>

        {{-- Total Anime --}}
        <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-4 transition hover:border-brand-500/30">
            <div class="flex min-w-0 items-center justify-between gap-2">
                <span class="truncate text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Anime</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-500/10 text-brand-400">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-2 font-display text-2xl font-bold text-white">{{ number_format($stats['total_anime']) }}</p>
            <a href="{{ route('admin.animes.index') }}" class="mt-1 inline-block text-[10px] text-brand-400 hover:underline">Kelola &rarr;</a>
        </div>

        {{-- Total Episodes --}}
        <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-4 transition hover:border-brand-500/30">
            <div class="flex min-w-0 items-center justify-between gap-2">
                <span class="truncate text-[10px] font-bold uppercase tracking-wider text-slate-400">Episode</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-2 font-display text-2xl font-bold text-white">{{ number_format($stats['total_episodes']) }}</p>
            <a href="{{ route('admin.episodes.index') }}" class="mt-1 inline-block text-[10px] text-emerald-400 hover:underline">Kelola &rarr;</a>
        </div>

        {{-- Total Genres --}}
        <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-4 transition hover:border-brand-500/30">
            <div class="flex min-w-0 items-center justify-between gap-2">
                <span class="truncate text-[10px] font-bold uppercase tracking-wider text-slate-400">Genre</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-purple-500/10 text-purple-400">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-2 font-display text-2xl font-bold text-white">{{ number_format($stats['total_genres']) }}</p>
            <a href="{{ route('admin.genres.index') }}" class="mt-1 inline-block text-[10px] text-purple-400 hover:underline">Kelola &rarr;</a>
        </div>

        {{-- Total Views --}}
        <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-4 transition hover:border-brand-500/30">
            <div class="flex min-w-0 items-center justify-between gap-2">
                <span class="truncate text-[10px] font-bold uppercase tracking-wider text-slate-400">Tontonan</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500/10 text-amber-400">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-2 font-display text-2xl font-bold text-white">{{ number_format($stats['total_views']) }}</p>
            <p class="mt-1 text-[10px] text-slate-400">Akumulasi</p>
        </div>

        {{-- Total Comments --}}
        <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-4 transition hover:border-brand-500/30">
            <div class="flex min-w-0 items-center justify-between gap-2">
                <span class="truncate text-[10px] font-bold uppercase tracking-wider text-slate-400">Komentar</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-cyan-500/10 text-cyan-400">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-2 font-display text-2xl font-bold text-white">{{ number_format($stats['total_comments']) }}</p>
            <a href="{{ route('admin.comments.index') }}" class="mt-1 inline-block text-[10px] text-cyan-400 hover:underline">Moderasi &rarr;</a>
        </div>

        {{-- Total Reports --}}
        <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-4 transition hover:border-brand-500/30">
            <div class="flex min-w-0 items-center justify-between gap-2">
                <span class="truncate text-[10px] font-bold uppercase tracking-wider text-slate-400">Laporan</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-lg {{ $stats['pending_reports'] > 0 ? 'bg-red-500/20 text-red-400' : 'bg-slate-500/10 text-slate-400' }}">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-2 font-display text-2xl font-bold {{ $stats['pending_reports'] > 0 ? 'text-red-400' : 'text-white' }}">
                {{ number_format($stats['total_reports']) }}
            </p>
            <p class="mt-1 text-[10px] font-bold {{ $stats['pending_reports'] > 0 ? 'text-red-400' : 'text-slate-400' }}">
                {{ $stats['pending_reports'] }} Pending
            </p>
        </div>

        {{-- System Health status --}}
        <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-4 transition hover:border-brand-500/30">
            <div class="flex min-w-0 items-center justify-between gap-2">
                <span class="truncate text-[10px] font-bold uppercase tracking-wider text-slate-400">Database</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                </span>
            </div>
            <p class="mt-2 font-display text-sm font-bold text-white truncate">{{ $systemSummary['db_size'] }}</p>
            <p class="mt-1 text-[10px] text-emerald-400 font-semibold">{{ $systemSummary['db_status'] }}</p>
        </div>
    </div>

    {{-- 1B. MEMBERSHIP SUMMARY --}}
    <div class="space-y-3">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-sm font-bold text-white">Ringkasan Membership</h2>
                <p class="text-[11px] text-slate-400">Distribusi paket dan pendapatan dari data pembayaran terverifikasi.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.membership.index') }}" class="rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-[11px] font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white">Kelola Member</a>
                <a href="{{ route('admin.payments.index') }}" class="rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-[11px] font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white">Pembayaran</a>
                <a href="{{ route('admin.membership-plans.index') }}" class="rounded-lg border border-brand-500/30 bg-brand-500/10 px-3 py-1.5 text-[11px] font-bold text-brand-300 transition hover:bg-brand-500/20">Paket & Harga</a>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-6">
            <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-4">
                <p class="truncate text-[10px] font-bold uppercase tracking-wider text-slate-400">Free</p>
                <p class="mt-2 font-display text-2xl font-bold text-slate-200">{{ number_format((int) data_get($membershipStats, 'free', 0)) }}</p>
                <p class="mt-1 text-[10px] text-slate-500">Paket dasar</p>
            </div>
            <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-4">
                <p class="truncate text-[10px] font-bold uppercase tracking-wider text-slate-400">Premium</p>
                <p class="mt-2 font-display text-2xl font-bold text-brand-300">{{ number_format((int) data_get($membershipStats, 'premium', 0)) }}</p>
                <p class="mt-1 text-[10px] text-slate-500">Member aktif</p>
            </div>
            <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-4">
                <p class="truncate text-[10px] font-bold uppercase tracking-wider text-slate-400">VIP</p>
                <p class="mt-2 font-display text-2xl font-bold text-amber-300">{{ number_format((int) data_get($membershipStats, 'vip', 0)) }}</p>
                <p class="mt-1 text-[10px] text-slate-500">Akses penuh</p>
            </div>
            <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-4">
                <p class="truncate text-[10px] font-bold uppercase tracking-wider text-slate-400">Pendapatan</p>
                <p class="mt-2 truncate font-display text-lg font-bold text-emerald-400" title="Rp {{ number_format((float) data_get($membershipStats, 'revenue', 0), 0, ',', '.') }}">Rp {{ number_format((float) data_get($membershipStats, 'revenue', 0), 0, ',', '.') }}</p>
                <p class="mt-1 text-[10px] text-slate-500">Setelah refund</p>
            </div>
            <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-4">
                <p class="truncate text-[10px] font-bold uppercase tracking-wider text-slate-400">Pending</p>
                <p class="mt-2 font-display text-2xl font-bold text-amber-400">{{ number_format((int) data_get($membershipStats, 'pending', 0)) }}</p>
                <p class="mt-1 text-[10px] text-slate-500">Invoice menunggu</p>
            </div>
            <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-4">
                <p class="truncate text-[10px] font-bold uppercase tracking-wider text-slate-400">Akan Berakhir</p>
                <p class="mt-2 font-display text-2xl font-bold text-rose-400">{{ number_format((int) data_get($membershipStats, 'expiring', 0)) }}</p>
                <p class="mt-1 text-[10px] text-slate-500">Dalam 7 hari</p>
            </div>
        </div>
    </div>

    {{-- 2. CHARTS SECTION: USER GROWTH & WATCH ACTIVITY --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- User Growth Chart --}}
        <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-5 shadow-elevated">
            <div class="mb-4 flex items-center justify-between border-b border-white/5 pb-3">
                <div>
                    <h2 class="text-sm font-bold text-white">Pertumbuhan Pengguna (7 Hari Terakhir)</h2>
                    <p class="text-[11px] text-slate-400">Pendaftaran akun baru setiap hari</p>
                </div>
                <span class="rounded bg-blue-500/20 px-2.5 py-1 text-xs font-bold text-blue-400">User Signups</span>
            </div>
            <div class="flex h-44 min-w-0 items-end gap-1.5 pt-4 sm:gap-3">
                @php
                    $maxUserCount = max(array_column($userGrowthDays, 'count')) ?: 1;
                @endphp
                @foreach ($userGrowthDays as $day)
                    @php
                        $isZero = $day['count'] === 0;
                        $heightPercent = $isZero ? 3 : max(15, round(($day['count'] / $maxUserCount) * 100));
                    @endphp
                    <div class="flex min-w-0 flex-1 flex-col items-center gap-2">
                        <span class="text-[10px] font-bold {{ $isZero ? 'text-slate-600' : 'text-blue-400' }}">{{ $day['count'] }}</span>
                        <div class="w-full rounded-t-lg {{ $isZero ? 'bg-white/5' : 'bg-blue-500/60 transition hover:bg-blue-500' }}" style="height: {{ $heightPercent }}%;"></div>
                        <span class="truncate text-[10px] {{ $isZero ? 'text-slate-600' : 'text-slate-400 font-medium' }}">{{ $day['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Watch Activity Chart --}}
        <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-5 shadow-elevated">
            <div class="mb-4 flex items-center justify-between border-b border-white/5 pb-3">
                <div>
                    <h2 class="text-sm font-bold text-white">Aktivitas Tontonan (7 Hari Terakhir)</h2>
                    <p class="text-[11px] text-slate-400">Total pemutaran video streaming harian</p>
                </div>
                <span class="rounded bg-brand-500/20 px-2.5 py-1 text-xs font-bold text-brand-400">Video Views</span>
            </div>
            <div class="flex h-44 min-w-0 items-end gap-1.5 pt-4 sm:gap-3">
                @php
                    $maxWatchCount = max(array_column($watchActivityDays, 'count')) ?: 1;
                @endphp
                @foreach ($watchActivityDays as $day)
                    @php
                        $isZero = $day['count'] === 0;
                        $heightPercent = $isZero ? 3 : max(15, round(($day['count'] / $maxWatchCount) * 100));
                    @endphp
                    <div class="flex min-w-0 flex-1 flex-col items-center gap-2">
                        <span class="text-[10px] font-bold {{ $isZero ? 'text-slate-600' : 'text-brand-400' }}">{{ $day['count'] }}</span>
                        <div class="w-full rounded-t-lg {{ $isZero ? 'bg-white/5' : 'bg-brand-500/60 transition hover:bg-brand-500' }}" style="height: {{ $heightPercent }}%;"></div>
                        <span class="truncate text-[10px] {{ $isZero ? 'text-slate-600' : 'text-slate-400 font-medium' }}">{{ $day['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 3. VISITORS SUMMARY & SYSTEM / SERVER SUMMARY --}}
    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Visitors Statistics --}}
        <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-5 shadow-elevated">
            <h2 class="mb-4 text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-white/5 pb-2">
                Statistik Pengunjung
            </h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between rounded-xl bg-white/5 p-3">
                    <span class="text-xs text-slate-300 font-medium">Pengunjung Hari Ini</span>
                    <span class="font-display text-base font-bold text-white">{{ number_format($visitors['today']) }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl bg-white/5 p-3">
                    <span class="text-xs text-slate-300 font-medium">Minggu Ini (7 Hari)</span>
                    <span class="font-display text-base font-bold text-white">{{ number_format($visitors['week']) }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl bg-white/5 p-3">
                    <span class="text-xs text-slate-300 font-medium">Bulan Ini (30 Hari)</span>
                    <span class="font-display text-base font-bold text-white">{{ number_format($visitors['month']) }}</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-white/5 space-y-2">
                <a href="{{ route('admin.visitor-logs.index') }}" class="flex items-center justify-between text-xs font-semibold text-brand-400 hover:underline">
                    <span>Lihat Log Nama Perangkat Pengunjung</span>
                    <span class="rounded bg-brand-500/20 px-1.5 py-0.5 text-[10px] font-bold text-brand-300">Live</span>
                </a>
                <a href="{{ route('admin.analytics.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-400 hover:text-white">
                    <span>Lihat Laporan Analytics Lengkap</span>
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- Server & System Summary --}}
        <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-5 shadow-elevated lg:col-span-2">
            <div class="mb-4 flex items-center justify-between border-b border-white/5 pb-2">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Ringkasan Sistem & Server</h2>
                <a href="{{ route('admin.system.monitoring') }}" class="text-xs font-semibold text-brand-400 hover:underline">Detail Monitoring &rarr;</a>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-white/5 bg-white/5 p-3">
                    <span class="text-[10px] uppercase font-bold text-slate-500">PHP Version</span>
                    <p class="mt-1 font-mono text-xs font-bold text-white">{{ $systemSummary['php_version'] }}</p>
                </div>
                <div class="rounded-xl border border-white/5 bg-white/5 p-3">
                    <span class="text-[10px] uppercase font-bold text-slate-500">Laravel Version</span>
                    <p class="mt-1 font-mono text-xs font-bold text-white">{{ $systemSummary['laravel_version'] }}</p>
                </div>
                <div class="rounded-xl border border-white/5 bg-white/5 p-3">
                    <span class="text-[10px] uppercase font-bold text-slate-500">Environment</span>
                    <p class="mt-1 text-xs font-bold text-emerald-400 uppercase">{{ $systemSummary['env'] }}</p>
                </div>
                <div class="rounded-xl border border-white/5 bg-white/5 p-3">
                    <span class="text-[10px] uppercase font-bold text-slate-500">Memory Usage</span>
                    <p class="mt-1 font-mono text-xs font-bold text-white">{{ $systemSummary['memory_usage'] }}</p>
                </div>
                <div class="rounded-xl border border-white/5 bg-white/5 p-3">
                    <span class="text-[10px] uppercase font-bold text-slate-500">Disk Space</span>
                    <p class="mt-1 font-mono text-xs font-bold text-white truncate">{{ $systemSummary['disk_usage'] }}</p>
                </div>
                <div class="rounded-xl border border-white/5 bg-white/5 p-3">
                    <span class="text-[10px] uppercase font-bold text-slate-500">Database Size</span>
                    <p class="mt-1 font-mono text-xs font-bold text-white">{{ $systemSummary['db_size'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. RECENT ACTIVITIES & LATEST ITEMS --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Recent Activity Logs --}}
        <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-5 shadow-elevated">
            <div class="mb-4 flex items-center justify-between border-b border-white/5 pb-2">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Aktivitas Terbaru Website</h2>
                <a href="{{ route('admin.activity-logs.index') }}" class="text-xs font-semibold text-brand-400 hover:underline">Semua Log &rarr;</a>
            </div>
            @if ($recentActivities->isEmpty())
                <p class="py-6 text-center text-xs text-slate-500">Belum ada aktivitas tercatat.</p>
            @else
                <div class="space-y-3">
                    @foreach ($recentActivities as $act)
                        <div class="flex items-start justify-between gap-3 rounded-xl border border-white/5 bg-white/5 p-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="rounded bg-brand-500/20 px-1.5 py-0.5 text-[10px] font-extrabold text-brand-400">
                                        {{ $act->module }}
                                    </span>
                                    <span class="text-xs font-semibold text-white truncate">{{ $act->description }}</span>
                                </div>
                                <p class="mt-1 text-[10px] text-slate-400">
                                    Oleh <strong class="text-slate-300">{{ $act->user->name ?? 'System' }}</strong> • IP: {{ $act->ip_address }}
                                </p>
                            </div>
                            <span class="shrink-0 text-[10px] text-slate-500">{{ $act->created_at->diffForHumans() }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Pending Reports & Recent Feedback --}}
        <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-5 shadow-elevated">
            <div class="mb-4 flex items-center justify-between border-b border-white/5 pb-2">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Laporan Pengguna Terbaru</h2>
                <a href="{{ route('admin.reports.index') }}" class="text-xs font-semibold text-brand-400 hover:underline">Kelola Laporan &rarr;</a>
            </div>
            @if ($latestReports->isEmpty())
                <p class="py-6 text-center text-xs text-slate-500">Tidak ada laporan masalah pending saat ini.</p>
            @else
                <div class="space-y-3">
                    @foreach ($latestReports as $rep)
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-white/5 bg-white/5 p-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="rounded px-2 py-0.5 text-[10px] font-bold {{ $rep->status === 'pending' ? 'bg-red-500/20 text-red-400' : 'bg-emerald-500/20 text-emerald-400' }}">
                                        {{ strtoupper($rep->status) }}
                                    </span>
                                    <span class="text-xs font-semibold text-white truncate">{{ $rep->type }}</span>
                                </div>
                                <p class="mt-1 truncate text-xs text-slate-300">{{ $rep->description }}</p>
                            </div>
                            <a href="{{ route('admin.reports.show', $rep) }}" class="rounded-lg border border-white/10 px-2.5 py-1 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white">
                                Tinjau
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- 5. RECENT VISITOR DEVICES --}}
    <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-5 shadow-elevated">
        <div class="mb-4 flex items-center justify-between border-b border-white/5 pb-2">
            <div class="flex items-center gap-2">
                <span class="rounded-lg bg-brand-500/10 p-1.5 text-brand-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-white">Kunjungan & Nama Perangkat Terbaru</h2>
                    <p class="text-[11px] text-slate-400">Deteksi otomatis nama perangkat, OS, dan browser pengunjung website</p>
                </div>
            </div>
            <a href="{{ route('admin.visitor-logs.index') }}" class="text-xs font-semibold text-brand-400 hover:underline">Semua Log Perangkat &rarr;</a>
        </div>
        @if ($recentVisitors->isEmpty())
            <p class="py-6 text-center text-xs text-slate-500">Belum ada kunjungan tercatat.</p>
        @else
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($recentVisitors as $visit)
                    <div class="flex items-center gap-3 rounded-xl border border-white/5 bg-white/5 p-3 transition hover:border-brand-500/30">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $visit->device_type === 'Mobile' ? 'bg-purple-500/10 text-purple-400' : ($visit->device_type === 'Tablet' ? 'bg-amber-500/10 text-amber-400' : ($visit->device_type === 'Desktop' ? 'bg-blue-500/10 text-blue-400' : 'bg-slate-700/30 text-slate-400')) }}">
                            @if ($visit->device_type === 'Mobile')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            @elseif ($visit->device_type === 'Tablet')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            @else
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            @endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5">
                                <p class="truncate text-xs font-bold text-white">{{ $visit->device_name ?: 'Perangkat Tamu' }}</p>
                                <span class="rounded px-1.5 py-0.5 text-[9px] font-semibold {{ $visit->device_type === 'Mobile' ? 'bg-purple-500/20 text-purple-300' : ($visit->device_type === 'Tablet' ? 'bg-amber-500/20 text-amber-300' : 'bg-blue-500/20 text-blue-300') }}">{{ $visit->device_type ?: 'Unknown' }}</span>
                            </div>
                            <div class="mt-0.5 flex items-center gap-1.5 text-[10px] text-slate-400 truncate">
                                <span>{{ $visit->platform ?: 'OS' }}</span>
                                <span>•</span>
                                <span>{{ $visit->browser ?: 'Browser' }}</span>
                                <span>•</span>
                                <span>{{ $visit->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
