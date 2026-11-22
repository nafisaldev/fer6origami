@extends('layouts.app')

@section('title', 'Tambah Voucher — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 border-b border-white/10 pb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-brand-400">Membership</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Tambah Voucher</h1>
            <p class="mt-1 text-xs text-slate-400">Buat kode diskon beserta batasan paket, durasi, dan masa berlakunya.</p>
        </div>
        <a href="{{ route('admin.vouchers.index') }}" class="self-start rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white">&larr; Kembali</a>
    </div>

    <form method="POST" action="{{ route('admin.vouchers.store') }}" class="surface-panel space-y-6 rounded-2xl border border-white/10 p-6 shadow-elevated">
        @csrf

        @if ($errors->any())
            <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-xs text-rose-200" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- BASIC --}}
            <div class="space-y-5">
                <div>
                    <h2 class="border-b border-white/5 pb-2 text-xs font-bold uppercase tracking-wider text-slate-300">Informasi Voucher</h2>
                </div>
                <div>
                    <label for="code" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Kode Voucher <span class="text-brand-500">*</span></label>
                    <input id="code" type="text" name="code" value="{{ old('code', $voucher->code ?? null) }}" required maxlength="50" pattern="[A-Z0-9_-]+" placeholder="Contoh: HEMAT30" class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2.5 text-sm font-bold uppercase tracking-wider text-white placeholder:text-slate-500 focus:border-brand-500">
                    <p class="mt-1 text-[11px] text-slate-500">Huruf kapital, angka, tanda hubung, dan garis bawah saja.</p>
                    @error('code') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="name" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Nama Voucher <span class="text-brand-500">*</span></label>
                    <input id="name" type="text" name="name" value="{{ old('name', $voucher->name ?? null) }}" required maxlength="120" placeholder="Contoh: Diskon Awal Bulan" class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2.5 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">
                    @error('name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="description" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Deskripsi</label>
                    <textarea id="description" name="description" rows="3" maxlength="500" placeholder="Keterangan singkat tentang voucher ini..." class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2.5 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">{{ old('description', $voucher->description ?? null) }}</textarea>
                    @error('description') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="type" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Tipe Diskon <span class="text-brand-500">*</span></label>
                        <select id="type" name="type" required class="w-full rounded-xl border border-white/10 bg-[#161924] px-3.5 py-2.5 text-xs text-white focus:border-brand-500">
                            <option value="percent" @selected(old('type', $voucher->type ?? 'percent') === 'percent')>Persentase (%)</option>
                            <option value="fixed" @selected(old('type', $voucher->type ?? 'percent') === 'fixed')>Nominal Tetap (Rp)</option>
                        </select>
                        @error('type') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="value" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Nilai Diskon <span class="text-brand-500">*</span></label>
                        <input id="value" type="number" name="value" min="0" step="0.01" value="{{ old('value', $voucher->value ?? null) }}" required placeholder="Contoh: 20" class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2.5 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">
                        <p class="mt-1 text-[11px] text-slate-500">Isi persentase atau nominal sesuai tipe diskon.</p>
                        @error('value') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="max_discount" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Maksimal Diskon (Rp)</label>
                        <input id="max_discount" type="number" name="max_discount" min="0" step="0.01" value="{{ old('max_discount', $voucher->max_discount ?? null) }}" placeholder="Kosongkan jika tidak ada" class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2.5 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">
                        @error('max_discount') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="min_purchase" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Minimum Pembelian (Rp)</label>
                        <input id="min_purchase" type="number" name="min_purchase" min="0" step="0.01" value="{{ old('min_purchase', $voucher->min_purchase ?? null) }}" placeholder="Kosongkan jika tidak ada" class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2.5 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">
                        @error('min_purchase') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- LIMITS & SCOPE --}}
            <div class="space-y-5">
                <div>
                    <h2 class="border-b border-white/5 pb-2 text-xs font-bold uppercase tracking-wider text-slate-300">Batasan Penggunaan</h2>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="usage_limit" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Total Kuota</label>
                        <input id="usage_limit" type="number" name="usage_limit" min="1" value="{{ old('usage_limit', $voucher->usage_limit ?? null) }}" placeholder="Kosongkan tanpa batas" class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2.5 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">
                        @error('usage_limit') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="per_user_limit" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Kuota per Pengguna <span class="text-brand-500">*</span></label>
                        <input id="per_user_limit" type="number" name="per_user_limit" min="1" max="100" value="{{ old('per_user_limit', $voucher->per_user_limit ?? 1) }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2.5 text-xs text-white focus:border-brand-500">
                        @error('per_user_limit') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <fieldset class="space-y-2">
                    <legend class="mb-1.5 text-xs font-bold uppercase tracking-wider text-slate-300">Berlaku untuk Paket</legend>
                    <p class="text-[11px] text-slate-500">Kosongkan semua untuk berlaku pada semua paket.</p>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach (['free' => 'Free', 'premium' => 'Premium', 'vip' => 'VIP'] as $slug => $label)
                            <label class="flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/[0.02] px-2 py-2.5 text-[11px] font-semibold text-slate-300">
                                <input type="checkbox" name="applies_to_plans[]" value="{{ $slug }}" @checked(in_array($slug, (array) old('applies_to_plans', (array) optional($voucher)->applies_to_plans))) class="h-4 w-4 rounded border-white/20 bg-[#161924] text-brand-500 focus:ring-brand-500">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('applies_to_plans') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    @error('applies_to_plans.*') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </fieldset>

                <fieldset class="space-y-2">
                    <legend class="mb-1.5 text-xs font-bold uppercase tracking-wider text-slate-300">Berlaku untuk Durasi</legend>
                    <p class="text-[11px] text-slate-500">Kosongkan semua untuk berlaku pada semua durasi harga.</p>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach ([7, 30, 90, 365] as $days)
                            <label class="flex items-center justify-center gap-1.5 rounded-xl border border-white/10 bg-white/[0.02] px-1 py-2.5 text-[11px] font-semibold text-slate-300">
                                <input type="checkbox" name="applies_to_durations[]" value="{{ $days }}" @checked(in_array($days, (array) old('applies_to_durations', (array) optional($voucher)->applies_to_durations))) class="h-4 w-4 rounded border-white/20 bg-[#161924] text-brand-500 focus:ring-brand-500">
                                {{ $days }}h
                            </label>
                        @endforeach
                    </div>
                    @error('applies_to_durations') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    @error('applies_to_durations.*') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </fieldset>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="valid_from" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Mulai Berlaku</label>
                        <input id="valid_from" type="datetime-local" name="valid_from" value="{{ old('valid_from', optional($voucher->valid_from ?? null)->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2.5 text-xs text-white focus:border-brand-500">
                        @error('valid_from') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="valid_until" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Berlaku Hingga</label>
                        <input id="valid_until" type="datetime-local" name="valid_until" value="{{ old('valid_until', optional($voucher->valid_until ?? null)->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2.5 text-xs text-white focus:border-brand-500">
                        @error('valid_until') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <label class="flex items-start gap-3 rounded-xl border border-white/10 bg-white/[0.02] p-3.5 text-xs text-slate-300">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $voucher->is_active ?? true)) class="mt-0.5 h-4 w-4 shrink-0 rounded border-white/20 bg-[#161924] text-brand-500 focus:ring-brand-500">
                    <span>
                        <span class="block font-bold text-white">Voucher Aktif</span>
                        <span class="text-[11px] text-slate-400">Voucher dapat dipakai pengguna saat checkout.</span>
                    </span>
                </label>
            </div>
        </div>

        <div class="flex flex-wrap justify-end gap-3 border-t border-white/10 pt-5">
            <a href="{{ route('admin.vouchers.index') }}" class="rounded-xl border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white">Batal</a>
            <button type="submit" class="rounded-xl bg-brand-500 px-6 py-2.5 text-xs font-bold text-white shadow-glow transition hover:bg-brand-600">Simpan Voucher</button>
        </div>
    </form>
</div>
@endsection
