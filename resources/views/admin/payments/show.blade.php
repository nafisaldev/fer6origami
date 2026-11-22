@extends('layouts.app')

@section('title', 'Pembayaran ' . $payment->invoice_number . ' — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 border-b border-white/10 pb-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-white">Admin</a>
            <span>/</span>
            <a href="{{ route('admin.payments.index') }}" class="hover:text-white">Pembayaran</a>
            <span>/</span>
            <span class="text-white">{{ $payment->invoice_number }}</span>
        </div>
        <a href="{{ route('admin.payments.index') }}" class="self-start rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white">&larr; Kembali</a>
    </div>

    <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-brand-400">Detail Invoice</p>
                <h1 class="mt-1 font-display text-2xl font-bold text-white">{{ $payment->invoice_number }}</h1>
                <p class="mt-1 text-xs text-slate-400">Dibuat {{ optional($payment->created_at)->format('d M Y H:i') ?: '-' }}</p>
            </div>
            @php
                $statusStyles = [
                    'pending' => 'bg-amber-500/15 text-amber-300',
                    'success' => 'bg-emerald-500/15 text-emerald-300',
                    'failed' => 'bg-rose-500/15 text-rose-300',
                    'expired' => 'bg-slate-500/15 text-slate-300',
                    'refunded' => 'bg-blue-500/15 text-blue-300',
                ];
            @endphp
            <span class="self-start rounded px-3 py-1 text-[11px] font-bold {{ $statusStyles[$payment->status] ?? 'bg-slate-500/15 text-slate-300' }}">{{ strtoupper($payment->status) }}</span>
        </div>

        <dl class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-white/5 bg-white/[0.02] p-3.5">
                <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Pengguna</dt>
                <dd class="mt-1 text-xs font-bold text-white">{{ $payment->user->name ?? '-' }}</dd>
                <dd class="mt-0.5 text-[10px] text-slate-400">{{ $payment->user->email ?? '-' }}</dd>
            </div>
            <div class="rounded-xl border border-white/5 bg-white/[0.02] p-3.5">
                <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Paket & Durasi</dt>
                <dd class="mt-1 text-xs font-bold text-white">{{ $payment->plan->name ?? '-' }}</dd>
                <dd class="mt-0.5 text-[10px] text-slate-400">{{ $payment->price->label ?? '-' }}</dd>
            </div>
            <div class="rounded-xl border border-white/5 bg-white/[0.02] p-3.5">
                <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Gateway</dt>
                <dd class="mt-1 text-xs font-bold uppercase text-white">{{ $payment->gateway ?? '-' }}</dd>
                <dd class="mt-0.5 text-[10px] text-slate-400">{{ $payment->gateway_type ?: 'Tipe tidak tersedia' }}</dd>
            </div>
            <div class="rounded-xl border border-white/5 bg-white/[0.02] p-3.5">
                <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Status</dt>
                <dd class="mt-1 text-xs font-bold uppercase text-white">{{ $payment->status }}</dd>
                <dd class="mt-0.5 text-[10px] text-slate-400">{{ $payment->failure_reason ?: 'Tidak ada keterangan gagal' }}</dd>
            </div>
        </dl>

        <div class="mt-4 grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl border border-white/5 bg-white/[0.02] p-3.5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Subtotal</p>
                <p class="mt-1 text-sm font-bold text-white">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-white/5 bg-white/[0.02] p-3.5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Diskon</p>
                <p class="mt-1 text-sm font-bold text-amber-300">- Rp {{ number_format((float) $payment->discount_amount, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/5 p-3.5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-300/70">Total Dibayar</p>
                <p class="mt-1 font-display text-lg font-bold text-emerald-300">Rp {{ number_format((float) $payment->total_amount, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <section class="surface-panel rounded-2xl border border-white/10 p-5 shadow-elevated">
                <h2 class="border-b border-white/5 pb-3 text-xs font-bold uppercase tracking-wider text-slate-300">Informasi Transaksi</h2>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Order ID Gateway</dt>
                        <dd class="mt-1 text-xs text-slate-200">{{ $payment->gateway_order_id ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Transaction ID Gateway</dt>
                        <dd class="mt-1 text-xs text-slate-200">{{ $payment->gateway_transaction_id ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Dibayar Pada</dt>
                        <dd class="mt-1 text-xs text-slate-200">{{ optional($payment->paid_at)->format('d M Y H:i') ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Kedaluwarsa Pada</dt>
                        <dd class="mt-1 text-xs text-slate-200">{{ optional($payment->expired_at)->format('d M Y H:i') ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Diverifikasi Oleh</dt>
                        <dd class="mt-1 text-xs text-slate-200">{{ $payment->verifier->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Waktu Verifikasi</dt>
                        <dd class="mt-1 text-xs text-slate-200">{{ optional($payment->verified_at)->format('d M Y H:i') ?: '-' }}</dd>
                    </div>
                </dl>
                <div class="mt-4 flex flex-wrap items-center gap-3 border-t border-white/5 pt-4">
                    @if ($payment->checkout_url)
                        <a href="{{ $payment->checkout_url }}" target="_blank" rel="noopener" class="rounded-lg border border-white/10 px-3 py-1.5 text-[11px] font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white">Buka Halaman Checkout</a>
                    @endif
                    @if ($payment->proof_path)
                        <a href="{{ asset('storage/' . $payment->proof_path) }}" target="_blank" rel="noopener" class="rounded-lg border border-white/10 px-3 py-1.5 text-[11px] font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white">Lihat Bukti Pembayaran</a>
                    @else
                        <span class="text-[11px] text-slate-500">Tidak ada bukti pembayaran yang diunggah.</span>
                    @endif
                </div>
                @if ($payment->failure_reason)
                    <p class="mt-3 rounded-lg border border-rose-500/20 bg-rose-500/5 px-3 py-2 text-[11px] text-rose-200">Alasan gagal: {{ $payment->failure_reason }}</p>
                @endif
            </section>

            <section class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
                <h2 class="border-b border-white/5 px-5 py-4 text-xs font-bold uppercase tracking-wider text-slate-300">Voucher yang Dipakai</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="border-b border-white/5 bg-[#11131b] text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Kode</th>
                                <th class="px-4 py-3">Nama</th>
                                <th class="px-4 py-3">Diskon</th>
                                <th class="px-4 py-3">Waktu Pakai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse ($payment->voucherRedemptions as $redemption)
                                <tr>
                                    <td class="px-4 py-3 font-bold text-brand-300">{{ $redemption->voucher->code ?? '-' }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ $redemption->voucher->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-white">Rp {{ number_format((float) $redemption->discount_amount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ optional($redemption->used_at)->format('d M Y H:i') ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-8 text-center text-xs text-slate-500">Tidak ada voucher yang dipakai pada pembayaran ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        {{-- PAYMENT ACTIONS --}}
        <div class="space-y-6 xl:col-span-1">
            @if ($payment->status === 'pending')
                <form method="POST" action="{{ route('admin.payments.verify', $payment) }}" class="surface-panel space-y-3 rounded-2xl border border-emerald-500/20 p-5 shadow-elevated">
                    @csrf
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-300">Setujui Pembayaran</h2>
                        <p class="mt-1 text-[11px] text-slate-400">Aktifkan membership pengguna setelah pembayaran manual terverifikasi.</p>
                    </div>
                    @if ($errors->any())
                        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-[11px] text-rose-200" role="alert">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif
                    <button type="submit" class="w-full rounded-xl bg-emerald-500/15 px-4 py-2 text-xs font-bold text-emerald-300 transition hover:bg-emerald-500 hover:text-white">Verifikasi Pembayaran</button>
                </form>

                <form method="POST" action="{{ route('admin.payments.failed', $payment) }}" class="surface-panel space-y-3 rounded-2xl border border-white/10 p-5 shadow-elevated">
                    @csrf
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-rose-300">Tolak Pembayaran</h2>
                        <p class="mt-1 text-[11px] text-slate-400">Tandai invoice sebagai gagal dan sertakan alasan penolakan.</p>
                    </div>
                    @if ($errors->any())
                        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-[11px] text-rose-200" role="alert">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif
                    <div>
                        <label for="failure-reason" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-300">Alasan Penolakan</label>
                        <textarea id="failure-reason" name="reason" rows="3" placeholder="Contoh: Bukti transfer tidak terbaca" class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs text-white placeholder:text-slate-500">{{ old('reason') }}</textarea>
                    </div>
                    <button type="submit" class="w-full rounded-xl bg-rose-500/15 px-4 py-2 text-xs font-bold text-rose-300 transition hover:bg-rose-500 hover:text-white">Tandai Gagal</button>
                </form>

                <form method="POST" action="{{ route('admin.payments.expired', $payment) }}" class="surface-panel space-y-3 rounded-2xl border border-white/10 p-5 shadow-elevated">
                    @csrf
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-300">Tandai Kedaluwarsa</h2>
                        <p class="mt-1 text-[11px] text-slate-400">Gunakan jika invoice tidak dibayar sampai batas waktu yang ditentukan.</p>
                    </div>
                    @if ($errors->any())
                        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-[11px] text-rose-200" role="alert">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif
                    <button type="submit" class="w-full rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white transition hover:bg-white/20">Tandai Kedaluwarsa</button>
                </form>
            @elseif ($payment->status === 'success')
                <form method="POST" action="{{ route('admin.payments.refunded', $payment) }}" class="surface-panel space-y-3 rounded-2xl border border-blue-500/20 p-5 shadow-elevated">
                    @csrf
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-blue-300">Tandai Refund</h2>
                        <p class="mt-1 text-[11px] text-slate-400">Catat pembayaran yang telah dikembalikan kepada pengguna.</p>
                    </div>
                    @if ($errors->any())
                        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-[11px] text-rose-200" role="alert">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif
                    <button type="submit" class="w-full rounded-xl bg-blue-500/15 px-4 py-2 text-xs font-bold text-blue-300 transition hover:bg-blue-500 hover:text-white">Tandai Refund</button>
                </form>
            @else
                <div class="surface-panel rounded-2xl border border-white/10 p-5 text-xs text-slate-400">
                    Tidak ada aksi lanjutan untuk pembayaran dengan status ini.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
