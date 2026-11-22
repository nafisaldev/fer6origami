@extends('layouts.app')

@section('title', 'Paket Saya - Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated sm:p-7">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-wider text-brand-400">Membership Anda</p>
                <h1 class="mt-2 font-display text-2xl font-extrabold text-white">{{ $membership->plan->name }}</h1>
                <p class="mt-1 text-xs text-slate-400">Status: <span class="font-bold {{ $membership->status === 'active' ? 'text-emerald-400' : 'text-amber-400' }}">{{ ucfirst($membership->status) }}</span></p>
                @if ($membership->expires_at)
                    <p class="mt-1 text-xs text-slate-300">
                        Berlaku sampai {{ $membership->expires_at->translatedFormat('d M Y H:i') }}
                        @if ($membership->expires_at->isPast())
                            <span class="text-red-400">(kedaluwarsa)</span>
                        @endif
                    </p>
                @else
                    <p class="mt-1 text-xs text-slate-300">Tanggal kedaluwarsa: tidak ada</p>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('membership.pricing') }}" class="inline-flex min-h-[44px] items-center rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white transition hover:bg-brand-600">Ubah Paket</a>
                @if ($membership->plan->slug !== \App\Models\MembershipPlan::FREE)
                    <a href="{{ route('membership.export') }}" class="inline-flex min-h-[44px] items-center rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10 hover:text-white">Ekspor Riwayat CSV</a>
                @endif
            </div>
        </div>

        @if (! empty($membership->plan->benefits))
            <div class="mt-6 border-t border-white/10 pt-5">
                <h2 class="text-xs font-bold text-white">Akses yang tersedia</h2>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($membership->plan->benefits as $benefit)
                        <span class="rounded-lg border border-white/10 bg-white/5 px-2.5 py-1.5 text-[11px] text-slate-300">{{ $benefit }}</span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-5">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-sm font-bold text-white">Langganan Terakhir</h2>
                <a href="{{ route('membership.history') }}" class="text-[11px] font-semibold text-brand-400 hover:underline">Lihat semua</a>
            </div>
            <div class="mt-4 space-y-2">
                @forelse ($subscriptions as $subscription)
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-white/5 bg-white/5 px-3 py-2.5 text-xs">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-white">{{ $subscription->plan->name ?? '-' }} <span class="text-slate-500">({{ $subscription->action }})</span></p>
                            <p class="mt-0.5 text-[11px] text-slate-400">{{ optional($subscription->created_at)->translatedFormat('d M Y H:i') }}</p>
                        </div>
                        <span class="shrink-0 rounded-lg bg-white/5 px-2 py-1 text-[10px] font-bold text-slate-300">{{ ucfirst($subscription->status) }}</span>
                    </div>
                @empty
                    <p class="rounded-xl border border-dashed border-white/10 px-3 py-6 text-center text-xs text-slate-400">Belum ada riwayat langganan.</p>
                @endforelse
            </div>
        </div>

        <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-5">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-sm font-bold text-white">Pembayaran Terakhir</h2>
                <a href="{{ route('membership.history') }}" class="text-[11px] font-semibold text-brand-400 hover:underline">Riwayat penuh</a>
            </div>
            <div class="mt-4 space-y-2">
                @forelse ($payments as $payment)
                    <a href="{{ route('payments.show', $payment->invoice_number) }}" class="flex items-center justify-between gap-3 rounded-xl border border-white/5 bg-white/5 px-3 py-2.5 text-xs transition hover:border-brand-500/30">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-white">{{ $payment->invoice_number }}</p>
                            <p class="mt-0.5 text-[11px] text-slate-400">{{ $payment->plan->name ?? '-' }} - Rp {{ number_format((float) $payment->total_amount, 0, ',', '.') }}</p>
                        </div>
                        <span class="shrink-0 rounded-lg bg-white/5 px-2 py-1 text-[10px] font-bold text-slate-300">{{ ucfirst($payment->status) }}</span>
                    </a>
                @empty
                    <p class="rounded-xl border border-dashed border-white/10 px-3 py-6 text-center text-xs text-slate-400">Belum ada pembayaran.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="surface-panel rounded-2xl border border-white/10 p-5">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-sm font-bold text-white">Notifikasi Membership</h2>
            <a href="{{ route('membership.notifications') }}" class="text-[11px] font-semibold text-brand-400 hover:underline">Lihat semua</a>
        </div>
        <div class="mt-4 space-y-2">
            @forelse ($notifications as $notification)
                <div class="rounded-xl border border-white/5 bg-white/5 px-3 py-2.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-xs font-bold text-white">{{ $notification->title }}</p>
                        <span class="text-[10px] text-slate-500">{{ optional($notification->created_at)->diffForHumans() }}</span>
                    </div>
                    <p class="mt-1 text-[11px] text-slate-300">{{ $notification->message }}</p>
                </div>
            @empty
                <p class="rounded-xl border border-dashed border-white/10 px-3 py-6 text-center text-xs text-slate-400">Belum ada notifikasi membership.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
