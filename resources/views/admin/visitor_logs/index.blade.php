@extends('layouts.app')

@section('title', 'Visitor Logs & Perangkat — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    {{-- HEADER --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.25em] text-brand-400 font-bold">Overview & Analytics</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Visitor Logs & Nama Perangkat</h1>
            <p class="mt-1 text-xs text-slate-400">Monitoring pengunjung website real-time beserta deteksi nama perangkat, sistem operasi, browser, dan IP address.</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('admin.visitor-logs.clear-old') }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data log pengunjung yang berusia lebih dari 30 hari?');">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-rose-500/30 bg-rose-500/10 px-3.5 py-2 text-xs font-semibold text-rose-300 hover:bg-rose-500/20 hover:text-white transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Bersihkan Log > 30 Hari
                </button>
            </form>
        </div>
    </div>

    @include('admin.partials.delete-all-button', ['resource' => 'visitor-logs', 'label' => 'Log Pengunjung'])

    {{-- METRIC CARDS --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="surface-panel rounded-2xl border border-white/10 p-4 shadow-elevated">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">Total Kunjungan</span>
                <span class="rounded-lg bg-brand-500/10 p-1.5 text-brand-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-black text-white">{{ number_format($metrics['total']) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">Tercatat di sistem</p>
        </div>

        <div class="surface-panel rounded-2xl border border-white/10 p-4 shadow-elevated">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">Pengunjung Hari Ini</span>
                <span class="rounded-lg bg-emerald-500/10 p-1.5 text-emerald-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-black text-emerald-400">{{ number_format($metrics['today_unique']) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">IP Unik hari ini</p>
        </div>

        <div class="surface-panel rounded-2xl border border-white/10 p-4 shadow-elevated">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">Desktop / PC</span>
                <span class="rounded-lg bg-blue-500/10 p-1.5 text-blue-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-black text-blue-400">{{ number_format($metrics['desktop']) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">{{ $metrics['desktop_pct'] }}% dari total</p>
        </div>

        <div class="surface-panel rounded-2xl border border-white/10 p-4 shadow-elevated">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">Smartphone / HP</span>
                <span class="rounded-lg bg-purple-500/10 p-1.5 text-purple-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-black text-purple-400">{{ number_format($metrics['mobile']) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">{{ $metrics['mobile_pct'] }}% dari total</p>
        </div>

        <div class="surface-panel rounded-2xl border border-white/10 p-4 shadow-elevated col-span-2 sm:col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">Tablet & Bot</span>
                <span class="rounded-lg bg-amber-500/10 p-1.5 text-amber-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-black text-amber-400">{{ number_format($metrics['tablet']) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">Tablet device</p>
        </div>
    </div>

    {{-- TOP DEVICE CARDS --}}
    @if ($topDevices->isNotEmpty())
        <div class="surface-panel rounded-2xl border border-white/10 p-4 shadow-elevated">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-2">
                <svg class="h-4 w-4 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                </svg>
                Perangkat Terpopuler Mengunjungi Website
            </h3>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5">
                @foreach ($topDevices as $td)
                    <div class="flex items-center gap-3 rounded-xl border border-white/5 bg-[#141722] p-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $td->device_type === 'Mobile' ? 'bg-purple-500/10 text-purple-400' : ($td->device_type === 'Tablet' ? 'bg-amber-500/10 text-amber-400' : ($td->device_type === 'Desktop' ? 'bg-blue-500/10 text-blue-400' : 'bg-slate-700/30 text-slate-400')) }}">
                            @if ($td->device_type === 'Mobile')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            @elseif ($td->device_type === 'Tablet')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            @elseif ($td->device_type === 'Bot')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            @else
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-bold text-white">{{ $td->device_name }}</p>
                            <div class="flex items-center gap-1.5 text-[10px] text-slate-400">
                                <span>{{ $td->device_type }}</span>
                                <span>•</span>
                                <span class="font-semibold text-brand-400">{{ number_format($td->count) }}x</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- FILTER BAR --}}
    <div class="surface-panel rounded-2xl border border-white/10 p-4 shadow-elevated">
        <form method="GET" action="{{ route('admin.visitor-logs.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama perangkat, IP, browser, atau URL..." class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2 text-xs text-white placeholder:text-slate-500">
            </div>
            <div>
                <select name="device_type" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white">
                    <option value="">Semua Tipe Perangkat</option>
                    <option value="Desktop" @selected($deviceType === 'Desktop')>Desktop / PC</option>
                    <option value="Mobile" @selected($deviceType === 'Mobile')>Smartphone / Mobile</option>
                    <option value="Tablet" @selected($deviceType === 'Tablet')>Tablet</option>
                    <option value="Bot" @selected($deviceType === 'Bot')>Bot / Crawler</option>
                </select>
            </div>
            <div>
                <select name="date" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white">
                    <option value="">Semua Waktu</option>
                    <option value="today" @selected($dateFilter === 'today')>Hari Ini</option>
                    <option value="week" @selected($dateFilter === 'week')>7 Hari Terakhir</option>
                    <option value="month" @selected($dateFilter === 'month')>30 Hari Terakhir</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 rounded-xl bg-brand-600 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-500 transition">Filter</button>
                @if ($search || $deviceType || $dateFilter)
                    <a href="{{ route('admin.visitor-logs.index') }}" class="rounded-xl border border-white/10 px-3 py-2 text-xs text-slate-400 hover:text-white">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- VISITOR LOGS TABLE --}}
    <div class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
        @if ($visitorLogs->isEmpty())
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <p class="mt-4 text-sm font-semibold text-slate-300">Belum ada riwayat kunjungan yang sesuai kriteria.</p>
                <p class="mt-1 text-xs text-slate-500">Log pengunjung akan tercatat otomatis saat pengguna menjelajahi website Fer6origami.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="border-b border-white/5 bg-[#11131b] text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-4 py-3.5">Waktu Kunjungan</th>
                            <th class="px-4 py-3.5">Nama Perangkat & OS</th>
                            <th class="px-4 py-3.5">Tipe Perangkat</th>
                            <th class="px-4 py-3.5">Browser</th>
                            <th class="px-4 py-3.5">IP Address</th>
                            <th class="px-4 py-3.5">Pengguna</th>
                            <th class="px-4 py-3.5">Halaman / URL</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach ($visitorLogs as $log)
                            <tr class="hover:bg-white/[0.02] transition">
                                <td class="whitespace-nowrap px-4 py-3">
                                    <div class="font-medium text-white">{{ $log->created_at->format('d M Y, H:i:s') }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $log->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg {{ $log->device_type === 'Mobile' ? 'bg-purple-500/10 text-purple-400' : ($log->device_type === 'Tablet' ? 'bg-amber-500/10 text-amber-400' : ($log->device_type === 'Desktop' ? 'bg-blue-500/10 text-blue-400' : 'bg-slate-700/30 text-slate-400')) }}">
                                            @if ($log->device_type === 'Mobile')
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                </svg>
                                            @elseif ($log->device_type === 'Tablet')
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                </svg>
                                            @elseif ($log->device_type === 'Bot')
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                </svg>
                                            @else
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                </svg>
                                            @endif
                                        </span>
                                        <div>
                                            <p class="font-bold text-white">{{ $log->device_name ?: 'Perangkat Tidak Dikenal' }}</p>
                                            <p class="text-[10px] text-slate-400">{{ $log->platform ?: 'Unknown OS' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-semibold {{ $log->device_type === 'Mobile' ? 'bg-purple-500/10 text-purple-400 border border-purple-500/20' : ($log->device_type === 'Tablet' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : ($log->device_type === 'Desktop' ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' : 'bg-slate-700/40 text-slate-400 border border-slate-600/30')) }}">
                                        {{ $log->device_type ?: 'Unknown' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 text-slate-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                        {{ $log->browser ?: 'Unknown' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-mono text-[11px] text-slate-400 whitespace-nowrap">
                                    {{ $log->ip_address }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($log->user)
                                        <div class="flex items-center gap-2">
                                            <img src="{{ $log->user->avatar_url }}" alt="{{ $log->user->name }}" class="h-6 w-6 rounded-full object-cover">
                                            <div>
                                                <p class="font-medium text-white">{{ $log->user->name }}</p>
                                                <p class="text-[10px] text-slate-400">{{ $log->user->email }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center rounded-md bg-slate-800 px-2 py-0.5 text-[10px] font-medium text-slate-400">
                                            Tamu (Guest)
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 max-w-xs truncate" title="{{ $log->url }}">
                                    <a href="{{ $log->url }}" target="_blank" class="text-slate-300 hover:text-brand-400 underline decoration-slate-600 underline-offset-2">
                                        {{ $log->url ?: '/' }}
                                    </a>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <form method="POST" action="{{ route('admin.visitor-logs.destroy', $log) }}" onsubmit="return confirm('Hapus log kunjungan ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg p-1.5 text-slate-400 hover:bg-rose-500/10 hover:text-rose-400 transition" title="Hapus log">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($visitorLogs->hasPages())
                <div class="border-t border-white/5 p-4">
                    {{ $visitorLogs->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
