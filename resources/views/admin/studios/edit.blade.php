@extends('layouts.app')

@section('title', 'Edit Studio ' . $studio->name . ' — Admin Fer6origami')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center justify-between border-b border-white/10 pb-4">
        <div>
            <p class="text-xs uppercase tracking-wider text-brand-400 font-bold">Studio Management</p>
            <h1 class="font-display text-2xl font-bold text-white">Edit Studio: {{ $studio->name }}</h1>
        </div>
        <a href="{{ route('admin.studios.index') }}" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white">
            &larr; Kembali
        </a>
    </div>

    <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated">
        <form method="POST" action="{{ route('admin.studios.update', $studio) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="flex items-center gap-4 border-b border-white/5 pb-4">
                <img src="{{ $studio->logo_url }}" alt="{{ $studio->name }}" class="h-14 w-14 rounded-xl object-cover bg-slate-900 border border-white/10">
                <div>
                    <h2 class="text-sm font-bold text-white">{{ $studio->name }}</h2>
                    <p class="text-xs text-slate-400">Total {{ $studio->animes()->count() }} anime terhubung</p>
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Nama Studio <span class="text-brand-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $studio->name) }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
                @error('name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Negara Asal</label>
                    <input type="text" name="country" value="{{ old('country', $studio->country) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Tahun Berdiri</label>
                    <input type="number" name="founded_year" value="{{ old('founded_year', $studio->founded_year) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Website Resmi</label>
                <input type="url" name="website" value="{{ old('website', $studio->website) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Ganti Logo</label>
                <input type="file" name="logo_upload" accept="image/*" class="w-full text-xs text-slate-400 file:mr-3 file:rounded-lg file:border-0 file:bg-white/10 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-white/20">
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Deskripsi Studio</label>
                <textarea name="description" rows="4" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">{{ old('description', $studio->description) }}</textarea>
            </div>

            <div class="pt-4 border-t border-white/5 flex justify-end gap-3">
                <a href="{{ route('admin.studios.index') }}" class="rounded-xl border border-white/10 px-5 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10">Batal</a>
                <button type="submit" class="rounded-xl bg-brand-500 px-6 py-2 text-xs font-bold text-white shadow-glow hover:bg-brand-600">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
