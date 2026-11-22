@extends('layouts.app')

@section('title', 'Tambah Paket Membership — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 border-b border-white/10 pb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-brand-400">Membership</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Tambah Paket Membership</h1>
            <p class="mt-1 text-xs text-slate-400">Buat tier baru beserta benefit yang akan ditampilkan di halaman harga.</p>
        </div>
        <a href="{{ route('admin.membership-plans.index') }}" class="self-start rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white">&larr; Kembali</a>
    </div>

    <form method="POST" action="{{ route('admin.membership-plans.store') }}" class="surface-panel space-y-6 rounded-2xl border border-white/10 p-6 shadow-elevated">
        @csrf

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
                    <input id="slug" type="text" name="slug" value="{{ old('slug', $plan->slug ?? null) }}" required pattern="[a-z0-9-]+" placeholder="contoh: premium" class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2.5 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">
                    <p class="mt-1 text-[11px] text-slate-500">Huruf kecil, angka, dan tanda hubung saja.</p>
                    @error('slug') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="name" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Nama Paket <span class="text-brand-500">*</span></label>
                    <input id="name" type="text" name="name" value="{{ old('name', $plan->name ?? null) }}" required placeholder="Contoh: Premium" class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-brand-500">
                    @error('name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="tagline" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Tagline</label>
                    <input id="tagline" type="text" name="tagline" value="{{ old('tagline', $plan->tagline ?? null) }}" maxlength="160" placeholder="Contoh: Nonton tanpa batas dengan kualitas terbaik" class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2.5 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">
                    @error('tagline') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Deskripsi</label>
                    <textarea id="description" name="description" rows="4" maxlength="1000" placeholder="Jelaskan keunggulan paket ini..." class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2.5 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">{{ old('description', $plan->description ?? null) }}</textarea>
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
                    <input id="sort_order" type="number" name="sort_order" min="0" max="999" value="{{ old('sort_order', $plan->sort_order ?? 0) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2.5 text-xs text-white focus:border-brand-500">
                    <p class="mt-1 text-[11px] text-slate-500">Angka lebih kecil tampil lebih awal.</p>
                    @error('sort_order') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-3">
                    <label class="flex items-start gap-3 rounded-xl border border-white/10 bg-white/[0.02] p-3.5 text-xs text-slate-300">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan->is_active ?? false)) class="mt-0.5 h-4 w-4 shrink-0 rounded border-white/20 bg-[#161924] text-brand-500 focus:ring-brand-500">
                        <span>
                            <span class="block font-bold text-white">Aktif</span>
                            <span class="text-[11px] text-slate-400">Paket ditampilkan di halaman harga dan dapat dipilih pengguna.</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 rounded-xl border border-white/10 bg-white/[0.02] p-3.5 text-xs text-slate-300">
                        <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $plan->is_default ?? false)) class="mt-0.5 h-4 w-4 shrink-0 rounded border-white/20 bg-[#161924] text-brand-500 focus:ring-brand-500">
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
            <button type="submit" class="rounded-xl bg-brand-500 px-6 py-2.5 text-xs font-bold text-white shadow-glow transition hover:bg-brand-600">Simpan Paket</button>
        </div>
    </form>
</div>
@endsection
