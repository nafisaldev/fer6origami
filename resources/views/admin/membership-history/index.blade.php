@extends('layouts.app')

@section('title', 'Riwayat Membership — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 border-b border-white/10 pb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-brand-400">Membership</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Riwayat Perubahan Membership</h1>
            <p class="mt-1 text-xs text-slate-400">Audit seluruh pemberian, perpanjangan, perubahan paket, dan pencabutan membership.</p>
        </div>
        <a href="{{ route('admin.membership.index') }}" class="self-start rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white">&larr; Kelola Membership</a>
    </div>

    @if ($errors->any())
        <div class="surface-panel rounded-2xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-xs text-rose-200" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- FILTER & SEARCH --}}
    <div class="surface-panel rounded-2xl border border-white/10 p-4 shadow-elevated">
        <form method="GET" action="{{ route('admin.membership-history.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="sm:col-span-2">
                <label for="history-search" class="sr-only">Cari nama atau email pengguna</label>
                <input id="history-search" type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau email pengguna..." class="w-full rounded-xl border border-white/10 bg-[#161924] px-3.5 py-2 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">
            </div>
            <div>
                <label for="history-action" class="sr-only">Filter aksi</label>
                <select id="history-action" name="action" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white">
                    <option value="">Semua Aksi</option>
                    @foreach (['grant' => 'Grant', 'renew' => 'Renew', 'change' => 'Change', 'upgrade' => 'Upgrade', 'downgrade' => 'Downgrade', 'revoke' => 'Revoke', 'expiry' => 'Expiry'] as $value => $label)
                        <option value="{{ $value }}" @selected($selectedAction === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="w-full rounded-xl bg-white/10 px-4 py-2 text-xs font-semibold text-white transition hover:bg-white/20">Filter</button>
            </div>
        </form>
    </div>

    {{-- HISTORY TABLE --}}
    <div class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="border-b border-white/5 bg-[#11131b] text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-4 py-3.5">Waktu</th>
                        <th class="px-4 py-3.5">Pengguna</th>
                        <th class="px-4 py-3.5">Aksi</th>
                        <th class="px-4 py-3.5">Dari Paket</th>
                        <th class="px-4 py-3.5">Ke Paket</th>
                        <th class="px-4 py-3.5">Durasi</th>
                        <th class="px-4 py-3.5">Petugas</th>
                        <th class="px-4 py-3.5">Alasan</th>
                        <th class="px-4 py-3.5">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($histories as $history)
                        <tr class="transition hover:bg-white/[0.02]">
                            <td class="whitespace-nowrap px-4 py-3 text-slate-400">{{ optional($history->created_at)->format('d M Y H:i') ?: '-' }}</td>
                            <td class="px-4 py-3">
                                @if ($history->user)
                                    <p class="font-bold text-white">{{ $history->user->name }}</p>
                                    <p class="text-[10px] text-slate-500">{{ $history->user->email }}</p>
                                @else
                                    <span class="text-slate-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded bg-brand-500/15 px-2 py-0.5 text-[10px] font-bold uppercase text-brand-300">{{ $history->action }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-400">{{ $history->fromPlan->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ $history->toPlan->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ $history->duration_days ? $history->duration_days . ' hari' : '-' }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ $history->actor->name ?? 'Sistem' }}</td>
                            <td class="max-w-xs px-4 py-3 text-slate-400">{{ $history->reason ?: '-' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $history->ip_address ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-xs text-slate-500">Belum ada riwayat perubahan membership.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/5 p-4">
            {{ $histories->links() }}
        </div>
    </div>
</div>
@endsection
