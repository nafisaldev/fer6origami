@extends('layouts.app')

@section('title', 'Tontonan & Analytics — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    {{-- HEADER & CONTROLS --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.25em] text-brand-400 font-bold">Watch History & Analytics</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Statistik & Analisis Tontonan</h1>
            <p class="mt-1 text-xs text-slate-400">Pantau performa tayangan, retensi penonton, dan konten anime paling diminati.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            {{-- Period Filter Pills --}}
            <div class="flex items-center rounded-xl border border-white/10 bg-white/5 p-1 text-xs font-semibold">
                <a href="{{ route('admin.analytics.index', ['period' => '7']) }}" class="rounded-lg px-3 py-1.5 transition {{ $period === '7' ? 'bg-brand-500 text-white font-bold' : 'text-slate-400 hover:text-white' }}">7 Hari</a>
                <a href="{{ route('admin.analytics.index', ['period' => '30']) }}" class="rounded-lg px-3 py-1.5 transition {{ $period === '30' ? 'bg-brand-500 text-white font-bold' : 'text-slate-400 hover:text-white' }}">30 Hari</a>
                <a href="{{ route('admin.analytics.index', ['period' => '90']) }}" class="rounded-lg px-3 py-1.5 transition {{ $period === '90' ? 'bg-brand-500 text-white font-bold' : 'text-slate-400 hover:text-white' }}">90 Hari</a>
                <a href="{{ route('admin.analytics.index', ['period' => 'all']) }}" class="rounded-lg px-3 py-1.5 transition {{ $period === 'all' ? 'bg-brand-500 text-white font-bold' : 'text-slate-400 hover:text-white' }}">Semua</a>
            </div>

            {{-- Export CSV --}}
            <a href="{{ route('admin.analytics.export') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white transition hover:bg-emerald-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    {{-- METRIC CARDS --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="surface-panel rounded-2xl border border-white/10 p-5">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Pemutaran</span>
            <p class="mt-2 font-display text-3xl font-extrabold text-white">{{ number_format($totalViews) }}</p>
            <p class="mt-1 text-xs text-brand-400 font-semibold">Video streams</p>
        </div>
        <div class="surface-panel rounded-2xl border border-white/10 p-5">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Estimasi Durasi Ditonton</span>
            <p class="mt-2 font-display text-3xl font-extrabold text-white">{{ number_format($totalDurationHours) }}</p>
            <p class="mt-1 text-xs text-blue-400 font-semibold">Jam pemutaran</p>
        </div>
        <div class="surface-panel rounded-2xl border border-white/10 p-5">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Penonton Unik Terdaftar</span>
            <p class="mt-2 font-display text-3xl font-extrabold text-white">{{ number_format($uniqueUsers) }}</p>
            <p class="mt-1 text-xs text-purple-400 font-semibold">Akun aktif menonton</p>
        </div>
        <div class="surface-panel rounded-2xl border border-white/10 p-5">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Penyelesaian Episode</span>
            <p class="mt-2 font-display text-3xl font-extrabold text-white">
                {{ $totalViews > 0 ? round(($completedViews / $totalViews) * 100, 1) : 0 }}%
            </p>
            <p class="mt-1 text-xs text-emerald-400 font-semibold">{{ number_format($completedViews) }} selesai tonton</p>
        </div>
    </div>

    {{-- TREND CHART --}}
    <div class="surface-panel rounded-2xl border border-white/10 p-5 shadow-elevated">
        <div class="mb-4 flex items-center justify-between border-b border-white/5 pb-3">
            <div>
                <h2 class="text-sm font-bold text-white">Grafik Tren Tontonan Harian</h2>
                <p class="text-xs text-slate-400">Distribusi pemutaran video harian selama periode terpilih</p>
            </div>
            <span class="rounded bg-brand-500/20 px-2.5 py-1 text-xs font-bold text-brand-400">Live Trend</span>
        </div>
        @php
            $maxTrend = max(array_column($trendData, 'views')) ?: 1;
        @endphp
        <div class="flex h-52 items-end gap-1.5 overflow-x-auto pt-6">
            @foreach ($trendData as $point)
                @php
                    $pct = max(6, round(($point['views'] / $maxTrend) * 100));
                @endphp
                <div class="flex min-w-[32px] flex-1 flex-col items-center gap-2">
                    <span class="text-[9px] font-bold text-slate-300">{{ $point['views'] }}</span>
                    <div class="w-full rounded-t bg-brand-500/50 transition hover:bg-brand-500" style="height: {{ $pct }}%;"></div>
                    <span class="text-[9px] text-slate-500 whitespace-nowrap">{{ $point['date'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- 2 TABLES: POPULAR ANIME & POPULAR EPISODES --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Popular Anime --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-5 shadow-elevated">
            <div class="mb-4 flex items-center justify-between border-b border-white/5 pb-2">
                <h2 class="text-sm font-bold text-white">Top 10 Anime Paling Populer</h2>
                <span class="text-xs text-slate-400">Berdasarkan pemutaran</span>
            </div>
            @if ($popularAnime->isEmpty())
                <p class="py-8 text-center text-xs text-slate-500">Belum ada data pemutaran anime.</p>
            @else
                <div class="space-y-3">
                    @foreach ($popularAnime as $index => $anime)
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-white/5 bg-white/5 p-2.5">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-white/10 text-xs font-bold {{ $index < 3 ? 'text-brand-400 bg-brand-500/20' : 'text-slate-400' }}">
                                    #{{ $index + 1 }}
                                </span>
                                <img src="{{ $anime->poster_image }}" alt="{{ $anime->title }}" class="h-10 w-8 shrink-0 rounded object-cover">
                                <div class="min-w-0 flex-1">
                                    <h3 class="truncate text-xs font-bold text-white">{{ $anime->title }}</h3>
                                    <p class="text-[10px] text-slate-400">{{ $anime->studio ?: 'Anime' }} • {{ $anime->year }}</p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="font-display text-sm font-bold text-white">{{ number_format($anime->watch_histories_count) }}</span>
                                <p class="text-[10px] text-slate-400">views</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Popular Episodes --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-5 shadow-elevated">
            <div class="mb-4 flex items-center justify-between border-b border-white/5 pb-2">
                <h2 class="text-sm font-bold text-white">Top 10 Episode Paling Ditonton</h2>
                <span class="text-xs text-slate-400">Berdasarkan views</span>
            </div>
            @if ($popularEpisodes->isEmpty())
                <p class="py-8 text-center text-xs text-slate-500">Belum ada data pemutaran episode.</p>
            @else
                <div class="space-y-3">
                    @foreach ($popularEpisodes as $index => $ep)
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-white/5 bg-white/5 p-2.5">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-white/10 text-xs font-bold {{ $index < 3 ? 'text-emerald-400 bg-emerald-500/20' : 'text-slate-400' }}">
                                    #{{ $index + 1 }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <h3 class="truncate text-xs font-bold text-white">{{ $ep->anime->title ?? 'Anime' }}</h3>
                                    <p class="text-[10px] text-emerald-400 font-semibold">Episode {{ $ep->episode_number }} — {{ $ep->title }}</p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="font-display text-sm font-bold text-white">{{ number_format($ep->watch_histories_count) }}</span>
                                <p class="text-[10px] text-slate-400">views</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- TOP VISITING DEVICES --}}
    <div class="surface-panel rounded-2xl border border-white/10 p-5 shadow-elevated">
        <div class="mb-4 flex items-center justify-between border-b border-white/5 pb-2">
            <div>
                <h2 class="text-sm font-bold text-white">Distribusi & Nama Perangkat Pengunjung</h2>
                <p class="text-xs text-slate-400">Peringkat perangkat yang digunakan penonton untuk mengakses Fer6origami</p>
            </div>
            <a href="{{ route('admin.visitor-logs.index') }}" class="text-xs font-semibold text-brand-400 hover:underline">Detail Visitor Logs &rarr;</a>
        </div>
        @if ($topDevices->isEmpty())
            <p class="py-8 text-center text-xs text-slate-500">Belum ada data perangkat pengunjung pada periode ini.</p>
        @else
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                @foreach ($topDevices as $index => $device)
                    <div class="rounded-xl border border-white/5 bg-white/5 p-3.5 transition hover:border-brand-500/30">
                        <div class="flex items-center justify-between">
                            <span class="flex h-5 w-5 items-center justify-center rounded text-[10px] font-black {{ $index < 3 ? 'bg-brand-500/20 text-brand-400' : 'bg-white/10 text-slate-400' }}">
                                #{{ $index + 1 }}
                            </span>
                            <span class="rounded px-1.5 py-0.5 text-[9px] font-semibold {{ $device->device_type === 'Mobile' ? 'bg-purple-500/20 text-purple-300' : ($device->device_type === 'Tablet' ? 'bg-amber-500/20 text-amber-300' : 'bg-blue-500/20 text-blue-300') }}">
                                {{ $device->device_type }}
                            </span>
                        </div>
                        <p class="mt-2 truncate font-bold text-xs text-white" title="{{ $device->device_name }}">{{ $device->device_name }}</p>
                        <p class="mt-1 text-xs font-extrabold text-brand-400">{{ number_format($device->count) }} <span class="text-[10px] font-normal text-slate-400">kunjungan</span></p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
