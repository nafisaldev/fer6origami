@extends('layouts.app')

@section('title', 'Kelola Membership — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 border-b border-white/10 pb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-brand-400">Membership</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Kelola Membership</h1>
            <p class="mt-1 text-xs text-slate-400">Pantau paket aktif pengguna, status langganan, dan pembayaran yang menunggu verifikasi.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.membership-history.index') }}" class="rounded-xl border border-white/10 px-3.5 py-2 text-xs font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white">Riwayat Aksi</a>
            <a href="{{ route('admin.membership-plans.index') }}" class="rounded-xl border border-white/10 px-3.5 py-2 text-xs font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white">Paket</a>
            <a href="{{ route('admin.vouchers.index') }}" class="rounded-xl border border-white/10 px-3.5 py-2 text-xs font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white">Voucher</a>
            <a href="{{ route('admin.payments.index') }}" class="rounded-xl border border-white/10 px-3.5 py-2 text-xs font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white">Pembayaran</a>
            @can('manage', \App\Models\UserMembership::class)
                <form method="POST" action="{{ route('admin.membership.sync') }}" class="inline">
                    @csrf
                    <button type="submit" class="rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white transition hover:bg-brand-400">Sinkronisasi</button>
                </form>
            @endcan
        </div>
    </div>

    @if ($errors->any())
        <div class="surface-panel rounded-2xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-xs text-rose-200" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- STATISTICS --}}
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        <div class="surface-panel rounded-2xl border border-white/10 p-4">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Paket Free</p>
            <p class="mt-1 font-display text-xl font-bold text-white">{{ number_format((int) ($stats['free'] ?? 0)) }}</p>
        </div>
        <div class="surface-panel rounded-2xl border border-white/10 p-4">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Paket Premium</p>
            <p class="mt-1 font-display text-xl font-bold text-white">{{ number_format((int) ($stats['premium'] ?? 0)) }}</p>
        </div>
        <div class="surface-panel rounded-2xl border border-white/10 p-4">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Paket VIP</p>
            <p class="mt-1 font-display text-xl font-bold text-white">{{ number_format((int) ($stats['vip'] ?? 0)) }}</p>
        </div>
        <div class="surface-panel rounded-2xl border border-white/10 p-4">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Pendapatan Bersih</p>
            <p class="mt-1 font-display text-lg font-bold text-emerald-300">Rp {{ number_format((float) ($stats['revenue'] ?? 0), 0, ',', '.') }}</p>
        </div>
        <div class="surface-panel rounded-2xl border border-white/10 p-4">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Menunggu Verifikasi</p>
            <p class="mt-1 font-display text-xl font-bold text-amber-300">{{ number_format((int) ($stats['pending'] ?? 0)) }}</p>
        </div>
        <div class="surface-panel rounded-2xl border border-white/10 p-4">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Kedaluwarsa 7 Hari</p>
            <p class="mt-1 font-display text-xl font-bold text-white">{{ number_format((int) ($stats['expiring'] ?? 0)) }}</p>
        </div>
    </div>

    {{-- FILTER & SEARCH --}}
    <div class="surface-panel rounded-2xl border border-white/10 p-4 shadow-elevated">
        <form method="GET" action="{{ route('admin.membership.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="sm:col-span-2">
                <label for="membership-search" class="sr-only">Cari nama atau email pengguna</label>
                <input id="membership-search" type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau email pengguna..." class="w-full rounded-xl border border-white/10 bg-[#161924] px-3.5 py-2 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">
            </div>
            <div>
                <label for="membership-status" class="sr-only">Filter status membership</label>
                <select id="membership-status" name="status" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white">
                    <option value="">Semua Status</option>
                    @foreach (['active' => 'Active', 'pending' => 'Pending', 'expired' => 'Expired', 'cancelled' => 'Cancelled'] as $value => $label)
                        <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label for="membership-plan" class="sr-only">Filter paket membership</label>
                <select id="membership-plan" name="plan_id" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white">
                    <option value="">Semua Paket</option>
                    @foreach ($plans as $option)
                        <option value="{{ $option->id }}" @selected((int) $selectedPlan === $option->id)>{{ $option->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="shrink-0 rounded-xl bg-white/10 px-4 py-2 text-xs font-semibold text-white transition hover:bg-white/20">Filter</button>
            </div>
        </form>
    </div>

    {{-- MEMBERSHIP TABLE --}}
    <div class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="border-b border-white/5 bg-[#11131b] text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-4 py-3.5">Pengguna</th>
                        <th class="px-4 py-3.5">Paket</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5">Mulai</th>
                        <th class="px-4 py-3.5">Berakhir</th>
                        <th class="px-4 py-3.5">Sumber</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($memberships as $item)
                        @php
                            $statusStyles = [
                                'active' => 'bg-emerald-500/15 text-emerald-300',
                                'pending' => 'bg-amber-500/15 text-amber-300',
                                'expired' => 'bg-slate-500/15 text-slate-300',
                                'cancelled' => 'bg-rose-500/15 text-rose-300',
                            ];
                        @endphp
                        <tr class="transition hover:bg-white/[0.02]">
                            <td class="px-4 py-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    @if ($item->user)
                                        <img src="{{ $item->user->avatar_url }}" alt="{{ $item->user->name }}" class="h-9 w-9 shrink-0 rounded-xl object-cover ring-1 ring-white/10">
                                        <div class="min-w-0">
                                            <p class="truncate font-bold text-white">{{ $item->user->name }}</p>
                                            <p class="truncate text-[10px] text-slate-500">{{ $item->user->email }}</p>
                                        </div>
                                    @else
                                        <span class="text-slate-500">Pengguna tidak ditemukan</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-bold text-white">{{ $item->plan->name ?? '-' }}</p>
                                <p class="text-[10px] uppercase tracking-wider text-brand-400">{{ $item->plan->slug ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded px-2 py-0.5 text-[10px] font-bold {{ $statusStyles[$item->status] ?? 'bg-slate-500/15 text-slate-300' }}">{{ strtoupper($item->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-400">{{ optional($item->starts_at)->format('d M Y H:i') ?: '-' }}</td>
                            <td class="px-4 py-3">
                                @if ($item->expires_at)
                                    <span class="text-slate-300">{{ optional($item->expires_at)->format('d M Y H:i') }}</span>
                                    @if ($item->expires_at->isPast())
                                        <p class="text-[10px] font-bold text-rose-400">Kedaluwarsa</p>
                                    @endif
                                @else
                                    <span class="text-slate-500">Seumur hidup</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-400">{{ $item->source ?: '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                @if ($item->user)
                                    <a href="{{ route('admin.membership.show', $item->user) }}" class="rounded-lg border border-white/10 px-2.5 py-1 text-[11px] font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white">Kelola</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-xs text-slate-500">Belum ada data membership yang cocok dengan filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/5 p-4">
            {{ $memberships->links() }}
        </div>
    </div>
</div>
@endsection
