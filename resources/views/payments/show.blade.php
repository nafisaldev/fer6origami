@extends('layouts.app')

@section('title', 'Invoice ' . $payment->invoice_number . ' - AnimeStream')

@section('content')
<div class="space-y-6">
    <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated sm:p-7">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-wider text-brand-400">Pembayaran</p>
                <h1 class="mt-1 font-display text-2xl font-extrabold text-white">Invoice {{ $payment->invoice_number }}</h1>
                <p class="mt-2 text-xs text-slate-400">{{ $payment->plan->name ?? '-' }} - {{ optional($payment->price)->label ?: '-' }}</p>
            </div>
            @php
                $statusClass = match ($payment->status) {
                    \App\Models\Payment::STATUS_SUCCESS => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                    \App\Models\Payment::STATUS_PENDING => 'bg-amber-500/10 text-amber-300 border-amber-500/30',
                    \App\Models\Payment::STATUS_FAILED, \App\Models\Payment::STATUS_EXPIRED => 'bg-red-500/10 text-red-400 border-red-500/30',
                    default => 'bg-white/5 text-slate-300 border-white/10',
                };
            @endphp
            <span class="w-fit rounded-lg border px-3 py-1.5 text-[11px] font-bold {{ $statusClass }}">{{ ucfirst($payment->status) }}</span>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total tagihan</p>
                <p class="mt-1 text-sm font-extrabold text-white">Rp {{ number_format((float) $payment->total_amount, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Diskon</p>
                <p class="mt-1 text-sm font-extrabold text-white">Rp {{ number_format((float) $payment->discount_amount, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Metode</p>
                <p class="mt-1 text-sm font-extrabold text-white">{{ ucfirst($payment->gateway) }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Batas bayar</p>
                <p class="mt-1 text-sm font-extrabold text-white">{{ optional($payment->expired_at)->translatedFormat('d M Y H:i') ?: '-' }}</p>
            </div>
        </div>

        @if ($payment->status !== \App\Models\Payment::STATUS_SUCCESS)
            <p class="mt-5 rounded-xl border border-white/10 bg-white/5 p-3 text-xs text-slate-300">
                Paket belum aktif. Membership hanya berubah setelah pembayaran terverifikasi, bukan saat halaman sukses dibuka.
            </p>
        @endif
    </div>

    @if ($payment->gateway === \App\Models\Payment::GATEWAY_MIDTRANS && $payment->checkout_url)
        <div class="surface-panel rounded-2xl border border-white/10 p-5">
            <h2 class="text-sm font-bold text-white">Lanjutkan pembayaran Midtrans</h2>
            <p class="mt-2 text-xs text-slate-400">Halaman Midtrans akan memproses pembayaran dan mengirim notifikasi ke server secara otomatis.</p>
            <a href="{{ $payment->checkout_url }}" target="_blank" rel="noopener" class="mt-4 inline-flex min-h-[44px] items-center rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white transition hover:bg-brand-600">Buka Halaman Pembayaran</a>
        </div>
    @endif

    @if ($payment->gateway === \App\Models\Payment::GATEWAY_MANUAL)
        <div class="grid gap-4 lg:grid-cols-2">
            <div class="surface-panel rounded-2xl border border-white/10 p-5">
                <h2 class="text-sm font-bold text-white">Instruksi transfer</h2>
                <div class="mt-4 space-y-2 text-xs text-slate-300">
                    <p><span class="text-slate-500">Bank:</span> {{ $bankName }}</p>
                    <p><span class="text-slate-500">Nomor rekening:</span> {{ $bankAccount ?: 'Belum diatur administrator' }}</p>
                    <p><span class="text-slate-500">Atas nama:</span> {{ $bankHolder }}</p>
                    <p><span class="text-slate-500">Nominal:</span> Rp {{ number_format((float) $payment->total_amount, 0, ',', '.') }}</p>
                    <p><span class="text-slate-500">Catatan transfer:</span> {{ $payment->invoice_number }}</p>
                </div>
            </div>

            <div class="surface-panel rounded-2xl border border-white/10 p-5">
                <h2 class="text-sm font-bold text-white">Bukti transfer</h2>
                @if ($payment->proof_path)
                    <p class="mt-3 text-xs text-emerald-400">Bukti sudah diunggah. Menunggu verifikasi administrator.</p>
                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($payment->proof_path) }}" target="_blank" rel="noopener" class="mt-3 inline-flex min-h-[44px] items-center rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10 hover:text-white">Lihat bukti</a>
                @endif

                @if ($payment->status === \App\Models\Payment::STATUS_PENDING)
                    <form action="{{ route('payments.proof', $payment->invoice_number) }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-3">
                        @csrf
                        <label for="proof" class="mb-2 block text-xs font-bold text-slate-300">Unggah bukti transfer</label>
                        <input id="proof" type="file" name="proof" accept="image/jpeg,image/png,image/webp" required class="block w-full rounded-xl border border-white/10 bg-[#161924] p-2.5 text-xs text-slate-300 focus:border-brand-500">
                        @error('proof') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        <button type="submit" class="min-h-[44px] w-full rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white transition hover:bg-brand-600">Kirim bukti</button>
                    </form>
                @endif
            </div>
        </div>
    @endif

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('payments.callback', ['invoice' => $payment->invoice_number]) }}" class="inline-flex min-h-[44px] items-center rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10 hover:text-white">Lihat status pembayaran</a>
        <a href="{{ route('membership.show') }}" class="inline-flex min-h-[44px] items-center rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10 hover:text-white">Paket saya</a>
    </div>
</div>
@endsection
