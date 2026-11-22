@extends('layouts.app')

@section('title', 'Kelola Pembayaran — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 border-b border-white/10 pb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-brand-400">Membership</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Kelola Pembayaran</h1>
            <p class="mt-1 text-xs text-slate-400">Verifikasi pembayaran manual dan pantau seluruh invoice membership.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.membership.index') }}" class="rounded-xl border border-white/10 px-3.5 py-2 text-xs font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white">Membership</a>
            <a href="{{ route('admin.vouchers.index') }}" class="rounded-xl border border-white/10 px-3.5 py-2 text-xs font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white">Voucher</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="surface-panel rounded-2xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-xs text-rose-200" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- SUMMARY --}}
    <div class="grid gap-3 sm:grid-cols-2">
        <div class="surface-panel rounded-2xl border border-white/10 p-4">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Pendapatan Bersih</p>
            <p class="mt-1 font-display text-xl font-bold text-emerald-300">Rp {{ number_format((float) $revenue, 0, ',', '.') }}</p>
            <p class="mt-1 text-[10px] text-slate-500">Total pembayaran berhasil dikurangi refund.</p>
        </div>
        <div class="surface-panel rounded-2xl border border-white/10 p-4">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Menunggu Verifikasi</p>
            <p class="mt-1 font-display text-xl font-bold text-amber-300">{{ number_format((int) $pendingCount) }}</p>
            <p class="mt-1 text-[10px] text-slate-500">Invoice berstatus pending.</p>
        </div>
    </div>

    {{-- FILTER & SEARCH --}}
    <div class="surface-panel rounded-2xl border border-white/10 p-4 shadow-elevated">
        <form method="GET" action="{{ route('admin.payments.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <label for="payment-search" class="sr-only">Cari nomor invoice atau nama pengguna</label>
                <input id="payment-search" type="text" name="search" value="{{ $search }}" placeholder="Cari invoice atau nama pengguna..." class="w-full rounded-xl border border-white/10 bg-[#161924] px-3.5 py-2 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">
            </div>
            <div>
                <label for="payment-status" class="sr-only">Filter status pembayaran</label>
                <select id="payment-status" name="status" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white">
                    <option value="">Semua Status</option>
                    @foreach (['pending' => 'Pending', 'success' => 'Success', 'failed' => 'Failed', 'expired' => 'Expired', 'refunded' => 'Refunded'] as $value => $label)
                        <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label for="payment-gateway" class="sr-only">Filter gateway pembayaran</label>
                <select id="payment-gateway" name="gateway" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white">
                    <option value="">Semua Gateway</option>
                    @foreach (['midtrans' => 'Midtrans', 'manual' => 'Manual'] as $value => $label)
                        <option value="{{ $value }}" @selected($selectedGateway === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="shrink-0 rounded-xl bg-white/10 px-4 py-2 text-xs font-semibold text-white transition hover:bg-white/20">Filter</button>
            </div>
        </form>
    </div>

    {{-- PAYMENT TABLE --}}
    <div class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="border-b border-white/5 bg-[#11131b] text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-4 py-3.5">Invoice</th>
                        <th class="px-4 py-3.5">Pengguna</th>
                        <th class="px-4 py-3.5">Paket</th>
                        <th class="px-4 py-3.5">Total</th>
                        <th class="px-4 py-3.5">Gateway</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5">Tanggal</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($payments as $payment)
                        @php
                            $statusStyles = [
                                'pending' => 'bg-amber-500/15 text-amber-300',
                                'success' => 'bg-emerald-500/15 text-emerald-300',
                                'failed' => 'bg-rose-500/15 text-rose-300',
                                'expired' => 'bg-slate-500/15 text-slate-300',
                                'refunded' => 'bg-blue-500/15 text-blue-300',
                            ];
                        @endphp
                        <tr class="transition hover:bg-white/[0.02]">
                            <td class="px-4 py-3">
                                <p class="font-bold text-white">{{ $payment->invoice_number }}</p>
                                <p class="text-[10px] text-slate-500">{{ $payment->gateway_order_id ?: '-' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @if ($payment->user)
                                    <p class="font-bold text-white">{{ $payment->user->name }}</p>
                                    <p class="text-[10px] text-slate-500">{{ $payment->user->email }}</p>
                                @else
                                    <span class="text-slate-500">Pengguna tidak ditemukan</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-white">{{ $payment->plan->name ?? '-' }}</p>
                                <p class="text-[10px] text-slate-500">{{ $payment->price->label ?? '-' }}</p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-bold text-white">Rp {{ number_format((float) $payment->total_amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded border border-white/10 px-2 py-0.5 text-[10px] font-bold uppercase text-slate-300">{{ $payment->gateway }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded px-2 py-0.5 text-[10px] font-bold {{ $statusStyles[$payment->status] ?? 'bg-slate-500/15 text-slate-300' }}">{{ strtoupper($payment->status) }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-400">{{ optional($payment->created_at)->format('d M Y H:i') ?: '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.payments.show', $payment) }}" class="rounded-lg border border-white/10 px-2.5 py-1 text-[11px] font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-xs text-slate-500">Belum ada pembayaran yang cocok dengan filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/5 p-4">
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection
