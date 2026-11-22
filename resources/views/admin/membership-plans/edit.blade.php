@extends('layouts.app')

@section('title', 'Edit Paket ' . $plan->name . ' — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 border-b border-white/10 pb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-brand-400">Membership</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Edit Paket: {{ $plan->name }}</h1>
            <p class="mt-1 text-xs text-slate-400">Perbarui detail paket, benefit, dan daftar harga langganan.</p>
        </div>
        <a href="{{ route('admin.membership-plans.index') }}" class="self-start rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white">&larr; Kembali</a>
    </div>

    <form method="POST" action="{{ route('admin.membership-plans.update', $plan) }}" class="surface-panel space-y-6 rounded-2xl border border-white/10 p-6 shadow-elevated">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-xs text-rose-200" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-5">
                <div>
                    <label for="slug" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Slug <span class="text-brand-500">*</span></label>
                    <input id="slug" type="text" name="slug" value="{{ old('slug', $plan->slug) }}" required pattern="[a-z0-9-]+" placeholder="contoh: premium" class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2.5 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">
                    @error('slug') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="name" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Nama Paket <span class="text-brand-500">*</span></label>
                    <input id="name" type="text" name="name" value="{{ old('name', $plan->name) }}" required placeholder="Contoh: Premium" class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-brand-500">
                    @error('name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="tagline" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Tagline</label>
                    <input id="tagline" type="text" name="tagline" value="{{ old('tagline', $plan->tagline) }}" maxlength="160" placeholder="Tagline singkat paket" class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2.5 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">
                    @error('tagline') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Deskripsi</label>
                    <textarea id="description" name="description" rows="4" maxlength="1000" placeholder="Jelaskan keunggulan paket ini..." class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2.5 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">{{ old('description', $plan->description) }}</textarea>
                    @error('description') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="space-y-5">
                <div>
                    <h2 class="mb-1.5 text-xs font-bold uppercase tracking-wider text-slate-300">Benefit Paket</h2>
                    <p class="mb-3 text-[11px] text-slate-500">Isi maksimal empat poin benefit. Baris kosong akan diabaikan.</p>
                    <div class="space-y-3">
                        @for ($index = 0; $index < 4; $index++)
                            <div>
                                <label for="benefit-{{ $index }}" class="mb-1 block text-[11px] font-semibold text-slate-400">Benefit {{ $index + 1 }}</label>
                                <input id="benefit-{{ $index }}" type="text" name="benefits[]" value="{{ old('benefits.' . $index, $plan->benefits[$index] ?? '') }}" maxlength="160" placeholder="Contoh: Akses episode tanpa iklan" class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">
                            </div>
                        @endfor
                    </div>
                    @error('benefits') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    @error('benefits.*') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="sort_order" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Urutan Tampil</label>
                    <input id="sort_order" type="number" name="sort_order" min="0" max="999" value="{{ old('sort_order', $plan->sort_order) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2.5 text-xs text-white focus:border-brand-500">
                    @error('sort_order') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-3">
                    <label class="flex items-start gap-3 rounded-xl border border-white/10 bg-white/[0.02] p-3.5 text-xs text-slate-300">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan->is_active)) class="mt-0.5 h-4 w-4 shrink-0 rounded border-white/20 bg-[#161924] text-brand-500 focus:ring-brand-500">
                        <span>
                            <span class="block font-bold text-white">Aktif</span>
                            <span class="text-[11px] text-slate-400">Paket ditampilkan di halaman harga dan dapat dipilih pengguna.</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 rounded-xl border border-white/10 bg-white/[0.02] p-3.5 text-xs text-slate-300">
                        <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $plan->is_default)) class="mt-0.5 h-4 w-4 shrink-0 rounded border-white/20 bg-[#161924] text-brand-500 focus:ring-brand-500">
                        <span>
                            <span class="block font-bold text-white">Paket Default</span>
                            <span class="text-[11px] text-slate-400">Jadikan paket bawaan untuk pengguna baru. Paket default tidak dapat dihapus.</span>
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap justify-end gap-3 border-t border-white/10 pt-5">
            <a href="{{ route('admin.membership-plans.index') }}" class="rounded-xl border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white">Batal</a>
            <button type="submit" class="rounded-xl bg-brand-500 px-6 py-2.5 text-xs font-bold text-white shadow-glow transition hover:bg-brand-600">Simpan Perubahan</button>
        </div>
    </form>

    {{-- PLAN PRICES --}}
    <section class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
        <div class="border-b border-white/5 px-5 py-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-300">Harga Paket</h2>
            <p class="mt-1 text-[11px] text-slate-500">Kelola pilihan durasi dan harga. Harga yang memiliki riwayat pembayaran tidak dapat dihapus.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="border-b border-white/5 bg-[#11131b] text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Durasi</th>
                        <th class="px-4 py-3">Label</th>
                        <th class="px-4 py-3">Harga</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Edit Harga</th>
                        <th class="px-4 py-3 text-right">Hapus</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($plan->prices as $price)
                        <tr>
                            <td class="px-4 py-3 font-bold text-white">{{ $price->duration_days }} hari</td>
                            <td class="px-4 py-3 text-slate-300">{{ $price->label }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-white">Rp {{ number_format((float) $price->price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                @if ($price->is_active)
                                    <span class="rounded bg-emerald-500/15 px-2 py-0.5 text-[10px] font-bold text-emerald-300">Aktif</span>
                                @else
                                    <span class="rounded bg-slate-500/15 px-2 py-0.5 text-[10px] font-bold text-slate-300">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('admin.membership-prices.update', [$plan, $price]) }}" class="grid min-w-[420px] grid-cols-2 gap-2 lg:grid-cols-4">
                                    @csrf
                                    @method('PATCH')
                                    <div>
                                        <label for="duration-{{ $price->id }}" class="sr-only">Durasi harga {{ $price->label }} dalam hari</label>
                                        <input id="duration-{{ $price->id }}" type="number" name="duration_days" min="1" max="3650" value="{{ old('duration_days', $price->duration_days) }}" required class="w-full rounded-lg border border-white/10 bg-[#161924] px-2.5 py-1.5 text-[11px] text-white">
                                    </div>
                                    <div>
                                        <label for="label-{{ $price->id }}" class="sr-only">Label harga {{ $price->label }}</label>
                                        <input id="label-{{ $price->id }}" type="text" name="label" value="{{ old('label', $price->label) }}" maxlength="80" required class="w-full rounded-lg border border-white/10 bg-[#161924] px-2.5 py-1.5 text-[11px] text-white">
                                    </div>
                                    <div>
                                        <label for="price-{{ $price->id }}" class="sr-only">Nominal harga {{ $price->label }}</label>
                                        <input id="price-{{ $price->id }}" type="number" name="price" min="0" step="0.01" value="{{ old('price', $price->price) }}" required class="w-full rounded-lg border border-white/10 bg-[#161924] px-2.5 py-1.5 text-[11px] text-white">
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <label class="flex items-center gap-1.5 text-[10px] text-slate-400">
                                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $price->is_active)) class="h-3.5 w-3.5 rounded border-white/20 bg-[#161924] text-brand-500 focus:ring-brand-500">
                                            Aktif
                                        </label>
                                        <button type="submit" class="shrink-0 rounded-lg bg-white/10 px-2.5 py-1.5 text-[11px] font-semibold text-white transition hover:bg-white/20">Simpan</button>
                                    </div>
                                    @if ($errors->any())
                                        <div class="col-span-full rounded-lg border border-rose-500/30 bg-rose-500/10 px-2.5 py-1.5 text-[11px] text-rose-200" role="alert">
                                            @foreach ($errors->all() as $error)
                                                <p>{{ $error }}</p>
                                            @endforeach
                                        </div>
                                    @endif
                                </form>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.membership-prices.destroy', [$plan, $price]) }}" onsubmit="return confirm('Hapus harga {{ addslashes($price->label) }}?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg bg-rose-500/10 px-2.5 py-1.5 text-[11px] font-semibold text-rose-300 transition hover:bg-rose-500 hover:text-white">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-xs text-slate-500">Belum ada harga untuk paket ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- ADD PRICE --}}
    <form method="POST" action="{{ route('admin.membership-prices.store', $plan) }}" class="surface-panel space-y-4 rounded-2xl border border-white/10 p-6 shadow-elevated">
        @csrf
        <div>
            <h2 class="text-xs font-bold uppercase tracking-wider text-brand-400">Tambah Harga Baru</h2>
            <p class="mt-1 text-[11px] text-slate-400">Tentukan durasi dan nominal harga untuk paket {{ $plan->name }}.</p>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-[11px] text-rose-200" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="new-duration" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-300">Durasi (Hari) <span class="text-brand-500">*</span></label>
                <input id="new-duration" type="number" name="duration_days" min="1" max="3650" value="{{ old('duration_days') }}" placeholder="Contoh: 30" required class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">
                @error('duration_days') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="new-label" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-300">Label <span class="text-brand-500">*</span></label>
                <input id="new-label" type="text" name="label" value="{{ old('label') }}" maxlength="80" placeholder="Contoh: 1 Bulan" required class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">
                @error('label') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="new-price" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-300">Harga (IDR) <span class="text-brand-500">*</span></label>
                <input id="new-price" type="number" name="price" min="0" step="0.01" value="{{ old('price') }}" placeholder="Contoh: 25000" required class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">
                @error('price') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-end">
                <label class="flex w-full items-center gap-2 rounded-xl border border-white/10 bg-white/[0.02] px-3 py-2.5 text-xs text-slate-300">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="h-4 w-4 shrink-0 rounded border-white/20 bg-[#161924] text-brand-500 focus:ring-brand-500">
                    Aktif
                </label>
            </div>
        </div>

        <div class="flex justify-end border-t border-white/10 pt-4">
            <button type="submit" class="rounded-xl bg-brand-500 px-5 py-2 text-xs font-bold text-white transition hover:bg-brand-400">Tambah Harga</button>
        </div>
    </form>
</div>
@endsection
