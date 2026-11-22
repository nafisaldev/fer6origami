@extends('layouts.app')

@section('title', 'Riwayat Membership - Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="surface-panel flex flex-col gap-3 rounded-2xl border border-white/10 p-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-brand-400">Akun</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white">Riwayat Membership</h1>
        </div>
        <a href="{{ route('membership.show') }}" class="inline-flex min-h-[44px] items-center rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10 hover:text-white">Kembali ke Paket Saya</a>
    </div>

    <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-5">
        <h2 class="text-sm font-bold text-white">Riwayat Langganan</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[640px] text-left text-xs">
                <thead class="border-b border-white/10 text-slate-400">
                    <tr>
                        <th class="px-2 py-2 font-bold">Tanggal</th>
                        <th class="px-2 py-2 font-bold">Aksi</th>
                        <th class="px-2 py-2 font-bold">Paket</th>
                        <th class="px-2 py-2 font-bold">Durasi</th>
                        <th class="px-2 py-2 font-bold">Berlaku Sampai</th>
                        <th class="px-2 py-2 font-bold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($subscriptions as $subscription)
                        <tr class="text-slate-300">
                            <td class="px-2 py-3">{{ optional($subscription->created_at)->translatedFormat('d M Y H:i') }}</td>
                            <td class="px-2 py-3">{{ ucfirst($subscription->action) }}</td>
                            <td class="px-2 py-3">{{ $subscription->plan->name ?? '-' }}</td>
                            <td class="px-2 py-3">{{ $subscription->duration_days ? $subscription->duration_days . ' hari' : '-' }}</td>
                            <td class="px-2 py-3">{{ optional($subscription->expires_at)->translatedFormat('d M Y H:i') ?: '-' }}</td>
                            <td class="px-2 py-3">{{ ucfirst($subscription->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-2 py-8 text-center text-slate-400">Belum ada riwayat langganan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $subscriptions->links() }}</div>
    </div>

    <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-5">
        <h2 class="text-sm font-bold text-white">Perubahan Paket</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[560px] text-left text-xs">
                <thead class="border-b border-white/10 text-slate-400">
                    <tr>
                        <th class="px-2 py-2 font-bold">Tanggal</th>
                        <th class="px-2 py-2 font-bold">Aksi</th>
                        <th class="px-2 py-2 font-bold">Dari</th>
                        <th class="px-2 py-2 font-bold">Ke</th>
                        <th class="px-2 py-2 font-bold">Oleh</th>
                        <th class="px-2 py-2 font-bold">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($changes as $change)
                        <tr class="text-slate-300">
                            <td class="px-2 py-3">{{ optional($change->created_at)->translatedFormat('d M Y H:i') }}</td>
                            <td class="px-2 py-3">{{ ucfirst($change->action) }}</td>
                            <td class="px-2 py-3">{{ $change->fromPlan->name ?? '-' }}</td>
                            <td class="px-2 py-3">{{ $change->toPlan->name ?? '-' }}</td>
                            <td class="px-2 py-3">{{ $change->actor->name ?? 'Sistem' }}</td>
                            <td class="px-2 py-3">{{ $change->reason ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-2 py-8 text-center text-slate-400">Belum ada perubahan paket.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $changes->links() }}</div>
    </div>
</div>
@endsection
