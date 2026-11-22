@extends('layouts.app')

@section('title', 'System Monitoring & Server — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.25em] text-brand-400 font-bold">System Management</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">System & Server Monitoring</h1>
            <p class="mt-1 text-xs text-slate-400">Status kesehatan aplikasi, database, penggunaan penyimpanan, dan log error Laravel.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.system.cache') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white">
                Kelola Cache &rarr;
            </a>
            <a href="{{ route('admin.system.backup') }}" class="rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white shadow-glow hover:bg-brand-600">
                Backup Database &rarr;
            </a>
        </div>
    </div>

    {{-- SYSTEM & SERVER STATS CARDS --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Application Status --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-5">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Aplikasi & Framework</span>
            <div class="mt-2 space-y-1">
                <p class="font-display text-lg font-bold text-white">Laravel {{ $systemInfo['laravel_version'] }}</p>
                <p class="text-xs text-slate-400">PHP {{ $systemInfo['php_version'] }} • {{ $systemInfo['os'] }}</p>
                <span class="inline-block rounded bg-emerald-500/20 px-2 py-0.5 text-[10px] font-bold text-emerald-400 uppercase">
                    Env: {{ $systemInfo['app_env'] }}
                </span>
            </div>
        </div>

        {{-- Database Status --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-5">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Database MySQL</span>
            <div class="mt-2 space-y-1">
                <p class="font-display text-lg font-bold text-white">{{ $databaseInfo['database'] }}</p>
                <p class="text-xs text-slate-400">{{ $databaseInfo['table_count'] }} tabel • {{ $databaseInfo['size'] }}</p>
                <span class="inline-block rounded bg-emerald-500/20 px-2 py-0.5 text-[10px] font-bold text-emerald-400">
                    {{ $databaseInfo['status'] }}
                </span>
            </div>
        </div>

        {{-- Memory Usage --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-5">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Penggunaan Memori</span>
            <div class="mt-2 space-y-1">
                <p class="font-display text-lg font-bold text-white">{{ $memoryInfo['current'] }}</p>
                <p class="text-xs text-slate-400">Peak: {{ $memoryInfo['peak'] }} • Limit: {{ $memoryInfo['limit'] }}</p>
                <span class="inline-block rounded bg-blue-500/20 px-2 py-0.5 text-[10px] font-bold text-blue-400">
                    Memory Normal
                </span>
            </div>
        </div>

        {{-- Disk Space --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-5">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Disk Storage Server</span>
            <div class="mt-2 space-y-1">
                <p class="font-display text-lg font-bold text-white">{{ $diskInfo['used_percent'] }}% Terpakai</p>
                <p class="text-xs text-slate-400">Bebas: {{ $diskInfo['free'] }} dari {{ $diskInfo['total'] }}</p>
                <div class="w-full bg-white/10 rounded-full h-1.5 mt-2">
                    <div class="bg-brand-500 h-1.5 rounded-full" style="width: {{ min(100, $diskInfo['used_percent']) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- STORAGE BREAKDOWN --}}
    <div class="surface-panel rounded-2xl border border-white/10 p-5 shadow-elevated">
        <h2 class="text-xs font-bold uppercase tracking-wider text-brand-400 border-b border-white/5 pb-2 mb-4">
            Monitoring Penggunaan Direktori Storage
        </h2>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-xl border border-white/5 bg-white/5 p-3.5">
                <span class="text-[10px] text-slate-400 uppercase font-bold">Storage / App</span>
                <p class="mt-1 font-mono text-sm font-bold text-white">{{ $storageUsage['app'] }}</p>
            </div>
            <div class="rounded-xl border border-white/5 bg-white/5 p-3.5">
                <span class="text-[10px] text-slate-400 uppercase font-bold">Storage / Logs</span>
                <p class="mt-1 font-mono text-sm font-bold text-white">{{ $storageUsage['logs'] }}</p>
            </div>
            <div class="rounded-xl border border-white/5 bg-white/5 p-3.5">
                <span class="text-[10px] text-slate-400 uppercase font-bold">Storage / Framework</span>
                <p class="mt-1 font-mono text-sm font-bold text-white">{{ $storageUsage['framework'] }}</p>
            </div>
            <div class="rounded-xl border border-white/5 bg-white/5 p-3.5">
                <span class="text-[10px] text-slate-400 uppercase font-bold">Public / Uploads</span>
                <p class="mt-1 font-mono text-sm font-bold text-white">{{ $storageUsage['public'] }}</p>
            </div>
        </div>
    </div>

    {{-- LARAVEL ERROR LOG VIEWER --}}
    <div class="surface-panel rounded-2xl border border-white/10 p-5 shadow-elevated">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-white/5 pb-3 mb-4">
            <div>
                <h2 class="text-sm font-bold text-white">Laravel Error & Activity Logs</h2>
                <p class="text-xs text-slate-400">Membaca berkas storage/logs/laravel.log</p>
            </div>
            <div class="flex items-center gap-2">
                <form method="GET" action="{{ route('admin.system.monitoring') }}" class="flex items-center gap-2">
                    <select name="level" onchange="this.form.submit()" class="rounded-xl border border-white/10 bg-[#161924] px-3 py-1.5 text-xs text-white">
                        <option value="">Semua Level</option>
                        <option value="ERROR" @selected($selectedLevel === 'ERROR')>ERROR</option>
                        <option value="WARNING" @selected($selectedLevel === 'WARNING')>WARNING</option>
                        <option value="INFO" @selected($selectedLevel === 'INFO')>INFO</option>
                    </select>
                </form>
                <form method="POST" action="{{ route('admin.system.monitoring.clear-logs') }}" onsubmit="return confirm('Kosongkan file log Laravel?');">
                    @csrf
                    <button type="submit" class="rounded-xl border border-red-500/30 bg-red-500/10 px-3 py-1.5 text-xs font-semibold text-red-400 hover:bg-red-500 hover:text-white">
                        Bersihkan Log
                    </button>
                </form>
            </div>
        </div>

        @if (empty($logs))
            <p class="py-12 text-center text-xs text-slate-500">Tidak ada log error yang tercatat saat ini. Sistem berjalan lancar!</p>
        @else
            <div class="space-y-2.5 max-h-[500px] overflow-y-auto pr-1 scrollbar-thin">
                @foreach ($logs as $item)
                    <div class="rounded-xl border border-white/5 bg-black/40 p-3 font-mono text-xs">
                        <div class="flex items-center justify-between text-[11px] mb-1">
                            <span class="rounded px-2 py-0.5 font-bold {{ $item['level'] === 'ERROR' ? 'bg-red-500/20 text-red-400' : ($item['level'] === 'WARNING' ? 'bg-amber-500/20 text-amber-400' : 'bg-blue-500/20 text-blue-400') }}">
                                {{ $item['level'] }}
                            </span>
                            <span class="text-slate-500">{{ $item['timestamp'] }}</span>
                        </div>
                        <p class="text-slate-300 leading-relaxed whitespace-pre-wrap break-all">{{ $item['message'] }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
