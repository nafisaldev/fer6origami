@extends('layouts.app')

@section('title', 'Status Pembayaran - Fer6origami')

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="surface-panel rounded-2xl border border-white/10 p-6 text-center shadow-elevated sm:p-8">
        @if ($payment->status === \App\Models\Payment::STATUS_SUCCESS)
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h1 class="mt-4 font-display text-2xl font-extrabold text-white">Pembayaran berhasil</h1>
            <p class="mt-2 text-sm text-slate-300">Paket {{ $payment->plan->name ?? '-' }} sudah aktif di akun Anda.</p>
        @elseif ($payment->status === \App\Models\Payment::STATUS_PENDING)
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/15 text-amber-300">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h1 class="mt-4 font-display text-2xl font-extrabold text-white">Pembayaran masih diproses</h1>
            <p class="mt-2 text-sm text-slate-300">Invoice {{ $payment->invoice_number }} belum lunas. Status akan berubah setelah pembayaran terverifikasi.</p>
        @else
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-red-500/15 text-red-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <h1 class="mt-4 font-display text-2xl font-extrabold text-white">Pembayaran belum berhasil</h1>
            <p class="mt-2 text-sm text-slate-300">Status invoice: {{ ucfirst($payment->status) }}. Anda dapat membuat invoice baru dari halaman paket.</p>
        @endif

        <div class="mt-6 rounded-xl border border-white/10 bg-white/5 p-4 text-left text-xs text-slate-300">
            <div class="flex justify-between gap-3"><span class="text-slate-500">Invoice</span><span class="font-semibold text-white">{{ $data['invoice'] }}</span></div>
            <div class="mt-2 flex justify-between gap-3"><span class="text-slate-500">Paket</span><span class="font-semibold text-white">{{ $data['plan'] }}</span></div>
            <div class="mt-2 flex justify-between gap-3"><span class="text-slate-500">Total</span><span class="font-semibold text-white">Rp {{ number_format((float) $data['total'], 0, ',', '.') }}</span></div>
            <div class="mt-2 flex justify-between gap-3"><span class="text-slate-500">Status</span><span class="font-semibold text-white">{{ ucfirst($data['status']) }}</span></div>
        </div>

        <div class="mt-6 flex flex-wrap justify-center gap-2">
            <a href="{{ route('membership.show') }}" class="inline-flex min-h-[44px] items-center rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white transition hover:bg-brand-600">Lihat paket saya</a>
            <a href="{{ route('membership.pricing') }}" class="inline-flex min-h-[44px] items-center rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10 hover:text-white">Lihat paket lain</a>
        </div>
    </div>
</div>
@endsection
