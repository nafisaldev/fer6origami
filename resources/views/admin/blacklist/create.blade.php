@extends('layouts.app')

@section('title', 'Tambah Blacklist Anime — Admin Fer6origami')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div>
        <p class="text-xs uppercase tracking-[0.35em] text-slate-500">Content Management</p>
        <h1 class="mt-2 font-display text-3xl font-bold text-white">Tambah Blacklist Anime</h1>
        <p class="mt-2 text-sm text-slate-400">Judul yang diblokir tidak bisa dibuka meski masih tersedia di API.</p>
    </div>

    <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated">
        <form method="POST" action="{{ route('admin.blacklist.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="title" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Judul Anime</label>
                <input id="title" type="text" name="title" value="{{ old('title') }}" required maxlength="255" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-brand-500" placeholder="Contoh: Nippon Sangoku">
                @error('title') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="slug" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Slug / Identifier</label>
                <input id="slug" type="text" name="slug" value="{{ old('slug') }}" maxlength="255" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-brand-500" placeholder="Dibuat otomatis dari judul jika kosong">
                <p class="mt-1 text-[11px] text-slate-500">Gunakan slug yang sama dengan URL anime di katalog agar pemblokiran langsung berlaku.</p>
                @error('slug') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-wrap justify-end gap-2 border-t border-white/5 pt-4">
                <a href="{{ route('admin.blacklist.index') }}" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/5">Batal</a>
                <button type="submit" class="rounded-xl bg-brand-500 px-5 py-2 text-xs font-bold text-white hover:bg-brand-400">Simpan Blacklist</button>
            </div>
        </form>
    </div>
</div>
@endsection
