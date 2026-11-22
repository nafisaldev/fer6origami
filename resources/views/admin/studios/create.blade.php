@extends('layouts.app')

@section('title', 'Tambah Studio — Admin Fer6origami')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center justify-between border-b border-white/10 pb-4">
        <div>
            <p class="text-xs uppercase tracking-wider text-brand-400 font-bold">Studio Management</p>
            <h1 class="font-display text-2xl font-bold text-white">Tambah Studio Animasi</h1>
        </div>
        <a href="{{ route('admin.studios.index') }}" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white">
            &larr; Kembali
        </a>
    </div>

    <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated">
        <form method="POST" action="{{ route('admin.studios.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Nama Studio <span class="text-brand-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: MAPPA" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
                @error('name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Negara Asal</label>
                    <input type="text" name="country" value="{{ old('country', 'Japan') }}" placeholder="Japan" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Tahun Berdiri</label>
                    <input type="number" name="founded_year" value="{{ old('founded_year', 2011) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Website Resmi</label>
                <input type="url" name="website" value="{{ old('website') }}" placeholder="https://..." class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Upload Logo</label>
                <input type="file" name="logo_upload" accept="image/*" class="w-full text-xs text-slate-400 file:mr-3 file:rounded-lg file:border-0 file:bg-white/10 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-white/20">
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Deskripsi Studio</label>
                <textarea name="description" rows="4" placeholder="Profil singkat studio..." class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">{{ old('description') }}</textarea>
            </div>

            <div class="pt-4 border-t border-white/5 flex justify-end gap-3">
                <a href="{{ route('admin.studios.index') }}" class="rounded-xl border border-white/10 px-5 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10">Batal</a>
                <button type="submit" class="rounded-xl bg-brand-500 px-6 py-2 text-xs font-bold text-white shadow-glow hover:bg-brand-600">Simpan Studio</button>
            </div>
        </form>
    </div>
</div>
@endsection
