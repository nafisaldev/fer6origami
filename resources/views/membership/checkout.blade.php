@extends('layouts.app')

@section('title', 'Checkout Membership - AnimeStream')

@section('content')
<div class="space-y-6">
    <div class="surface-panel flex flex-col gap-3 rounded-2xl border border-white/10 p-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-brand-400">Checkout</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white">Selesaikan pembayaran</h1>
        </div>
        <a href="{{ route('membership.pricing') }}" class="inline-flex min-h-[44px] items-center rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10 hover:text-white">Ganti paket</a>
    </div>

    <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_340px]">
        <form
            id="checkout-form"
            action="{{ route('membership.checkout.store') }}"
            method="POST"
            class="surface-panel min-w-0 rounded-2xl border border-white/10 p-5 sm:p-6"
            onsubmit="const button=this.querySelector('[type=submit]'); if (button) { button.disabled=true; button.textContent='Memproses...'; }"
        >
            @csrf
            <input type="hidden" name="plan_price_id" value="{{ $price->id }}">
            <input type="hidden" name="voucher_discount" id="voucher-discount" value="0">

            <h2 class="text-sm font-bold text-white">Metode pembayaran</h2>
            <div class="mt-4 space-y-3">
                @if ($manualEnabled)
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-white/10 bg-white/5 p-4 transition hover:border-brand-500/40">
                        <input type="radio" name="gateway" value="manual" class="mt-1 h-4 w-4 accent-[#e53535]" @checked(old('gateway', 'manual') === 'manual')>
                        <span class="min-w-0">
                            <span class="block text-xs font-bold text-white">Transfer manual</span>
                            <span class="mt-1 block text-[11px] text-slate-400">Unggah bukti transfer setelah invoice dibuat. Administrator memverifikasi sebelum paket aktif.</span>
                            <span class="mt-1 block text-[11px] text-slate-300">{{ $bankName }} {{ $bankAccount }} - a.n. {{ $bankHolder }}</span>
                        </span>
                    </label>
                @endif

                @if ($midtransEnabled)
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-white/10 bg-white/5 p-4 transition hover:border-brand-500/40">
                        <input type="radio" name="gateway" value="midtrans" class="mt-1 h-4 w-4 accent-[#e53535]" @checked(old('gateway') === 'midtrans')>
                        <span class="min-w-0">
                            <span class="block text-xs font-bold text-white">Midtrans</span>
                            <span class="mt-1 block text-[11px] text-slate-400">Bayar lewat halaman Midtrans. Membership aktif hanya setelah webhook terverifikasi.</span>
                        </span>
                    </label>
                @endif

                @if (! $manualEnabled && ! $midtransEnabled)
                    <p class="rounded-xl border border-amber-500/30 bg-amber-500/10 p-4 text-xs text-amber-200">Belum ada metode pembayaran yang aktif. Hubungi administrator.</p>
                @endif

                @error('gateway')
                    <p class="text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6">
                <label for="voucher_code" class="mb-2 block text-xs font-bold text-slate-300">Kode voucher</label>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <input
                        id="voucher_code"
                        type="text"
                        name="voucher_code"
                        value="{{ old('voucher_code') }}"
                        placeholder="Masukkan kode voucher"
                        class="min-h-[44px] w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white placeholder:text-slate-500 focus:border-brand-500 focus:outline-none"
                    >
                    <button type="button" id="check-voucher" class="min-h-[44px] shrink-0 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10 hover:text-white">Cek voucher</button>
                </div>
                <p id="voucher-result" class="mt-2 text-[11px] text-slate-400" aria-live="polite">Voucher opsional. Diskon dihitung ulang oleh server.</p>
            </div>

            <div class="mt-7 border-t border-white/10 pt-5">
                <button
                    type="submit"
                    class="min-h-[44px] w-full rounded-xl bg-brand-500 px-5 py-3 text-xs font-bold text-white shadow-subtle transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60"
                    @if (! $manualEnabled && ! $midtransEnabled) disabled @endif
                >
                    Buat Invoice
                </button>
            </div>
        </form>

        <aside class="surface-panel h-fit rounded-2xl border border-white/10 p-5">
            <h2 class="text-sm font-bold text-white">Ringkasan pesanan</h2>
            <div class="mt-4 space-y-3 text-xs">
                <div class="flex justify-between gap-3">
                    <span class="text-slate-400">Paket</span>
                    <span class="text-right font-semibold text-white">{{ $price->plan->name }}</span>
                </div>
                <div class="flex justify-between gap-3">
                    <span class="text-slate-400">Durasi</span>
                    <span class="text-right font-semibold text-white">{{ $price->label }}</span>
                </div>
                <div class="flex justify-between gap-3">
                    <span class="text-slate-400">Harga</span>
                    <span class="text-right font-semibold text-white" id="summary-price">Rp {{ number_format((float) $price->price, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between gap-3">
                    <span class="text-slate-400">Diskon</span>
                    <span class="text-right font-semibold text-brand-300" id="summary-discount">Rp 0</span>
                </div>
                <div class="flex justify-between gap-3 border-t border-white/10 pt-3">
                    <span class="font-bold text-white">Total</span>
                    <span class="font-extrabold text-white" id="summary-total">Rp {{ number_format((float) $price->price, 0, ',', '.') }}</span>
                </div>
            </div>
            <p class="mt-5 rounded-xl border border-white/5 bg-white/5 p-3 text-[11px] leading-relaxed text-slate-400">
                Harga diambil dari database server. Invoice berlaku 24 jam dan akan ditutup otomatis.
            </p>
        </aside>
    </div>
</div>

<script>
    (function () {
        const basePrice = {{ (float) $price->price }};
        const form = document.getElementById('checkout-form');
        const codeInput = document.getElementById('voucher_code');
        const discountInput = document.getElementById('voucher-discount');
        const result = document.getElementById('voucher-result');
        const discountText = document.getElementById('summary-discount');
        const totalText = document.getElementById('summary-total');
        const button = document.getElementById('check-voucher');

        const rupiah = (value) => 'Rp ' + Math.round(value).toLocaleString('id-ID');

        const render = (discount) => {
            discountInput.value = String(discount);
            discountText.textContent = rupiah(discount);
            totalText.textContent = rupiah(Math.max(0, basePrice - discount));
        };

        button.addEventListener('click', async () => {
            const code = codeInput.value.trim();
            if (!code) {
                result.textContent = 'Masukkan kode voucher terlebih dahulu.';
                result.className = 'mt-2 text-[11px] text-amber-300';
                render(0);
                return;
            }

            button.disabled = true;
            button.textContent = 'Mengecek...';
            result.textContent = 'Memeriksa voucher...';
            result.className = 'mt-2 text-[11px] text-slate-400';

            try {
                const response = await fetch('{{ route('membership.voucher.check') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ code: code, plan_price_id: {{ $price->id }} })
                });
                const data = await response.json();

                if (!response.ok || !data.valid) {
                    result.textContent = data.message || 'Voucher tidak dapat dipakai.';
                    result.className = 'mt-2 text-[11px] text-red-400';
                    render(0);
                    return;
                }

                result.textContent = 'Voucher ' + data.code + ' dipakai. Diskon ' + rupiah(data.discount) + '.';
                result.className = 'mt-2 text-[11px] text-emerald-400';
                render(data.discount);
            } catch (error) {
                result.textContent = 'Gagal memeriksa voucher. Coba lagi.';
                result.className = 'mt-2 text-[11px] text-red-400';
                render(0);
            } finally {
                button.disabled = false;
                button.textContent = 'Cek voucher';
            }
        });

        codeInput.addEventListener('input', () => {
            render(0);
            result.textContent = 'Voucher opsional. Diskon dihitung ulang oleh server.';
            result.className = 'mt-2 text-[11px] text-slate-400';
        });
    })();
</script>
@endsection
