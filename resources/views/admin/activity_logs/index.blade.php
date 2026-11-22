@extends('layouts.app')

@section('title', 'Log Aktivitas Sistem — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.25em] text-brand-400 font-bold">System Management</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Activity Logs & Security Audit</h1>
            <p class="mt-1 text-xs text-slate-400">Audit trail lengkap seluruh tindakan administratif, login, perubahan data, dan eksekusi sistem.</p>
        </div>
    </div>

    {{-- FILTER BAR --}}
    <div class="surface-panel rounded-2xl border border-white/10 p-4 shadow-elevated">
        <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari aktivitas, admin, atau IP address..." class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2 text-xs text-white placeholder:text-slate-500">
            </div>
            <div>
                <select name="module" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white">
                    <option value="">Semua Modul</option>
                    @foreach ($modules as $m)
                        <option value="{{ $m }}" @selected($selectedModule === $m)>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="admin_id" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white">
                    <option value="">Semua Admin</option>
                    @foreach ($admins as $adm)
                        <option value="{{ $adm->id }}" @selected($selectedAdmin == $adm->id)>{{ $adm->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input type="date" name="date" value="{{ $selectedDate }}" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-1.5 text-xs text-white">
                <button type="submit" class="rounded-xl bg-white/10 px-4 py-2 text-xs font-semibold text-white hover:bg-white/20">Filter</button>
            </div>
        </form>
    </div>

    {{-- LOGS TABLE --}}
    <div class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
        @if ($logs->isEmpty())
            <p class="py-12 text-center text-xs text-slate-500">Belum ada riwayat aktivitas yang sesuai filter.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="border-b border-white/5 bg-[#11131b] text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-4 py-3.5">Waktu</th>
                            <th class="px-4 py-3.5">Admin / Pelaksana</th>
                            <th class="px-4 py-3.5">Modul</th>
                            <th class="px-4 py-3.5">Aksi</th>
                            <th class="px-4 py-3.5">Deskripsi Aktivitas</th>
                            <th class="px-4 py-3.5">IP Address</th>
                            <th class="px-4 py-3.5 text-right">Data Perubahan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach ($logs as $log)
                            <tr class="transition hover:bg-white/[0.02]">
                                <td class="px-4 py-3 text-slate-400 text-[10px] whitespace-nowrap">
                                    {{ $log->created_at->format('d M Y, H:i:s') }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        @if ($log->user)
                                            <img src="{{ $log->user->avatar_url }}" alt="" class="h-6 w-6 rounded-full object-cover">
                                            <span class="font-bold text-white">{{ $log->user->name }}</span>
                                        @else
                                            <span class="text-slate-500 font-semibold">System</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded bg-brand-500/20 px-2 py-0.5 text-[10px] font-extrabold text-brand-400">
                                        {{ $log->module }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-200">
                                    {{ $log->action }}
                                </td>
                                <td class="px-4 py-3 max-w-xs">
                                    <p class="text-xs text-white truncate" title="{{ $log->description }}">{{ $log->description }}</p>
                                </td>
                                <td class="px-4 py-3 font-mono text-[10px] text-slate-400">
                                    {{ $log->ip_address ?: '-' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if ($log->changes)
                                        <button type="button" onclick="showChangesModal({{ json_encode($log->changes) }})" class="rounded bg-white/10 px-2 py-1 text-[10px] text-brand-400 font-bold hover:bg-white/20">
                                            JSON Diff
                                        </button>
                                    @else
                                        <span class="text-[10px] text-slate-600">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-white/5 p-4">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

{{-- MODAL JSON DIFF --}}
<div id="changesModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/80 p-4">
    <div class="surface-panel w-full max-w-xl rounded-2xl border border-white/10 p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-white/5 pb-2">
            <h3 class="font-display text-sm font-bold text-white">Detail Data yang Berubah</h3>
            <button type="button" onclick="closeChangesModal()" class="text-slate-400 hover:text-white">&times;</button>
        </div>
        <pre id="jsonChangesBox" class="max-h-80 overflow-y-auto rounded-xl bg-black/60 p-4 font-mono text-xs text-emerald-400 scrollbar-thin"></pre>
        <div class="flex justify-end pt-2">
            <button type="button" onclick="closeChangesModal()" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10">Tutup</button>
        </div>
    </div>
</div>

<script>
    function showChangesModal(data) {
        document.getElementById('jsonChangesBox').innerText = JSON.stringify(data, null, 2);
        document.getElementById('changesModal').classList.remove('hidden');
    }
    function closeChangesModal() {
        document.getElementById('changesModal').classList.add('hidden');
    }
</script>
@endsection
