@extends('layouts.app')

@section('title', 'Tambah Halaman Baru — Admin Fer6origami')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex items-center justify-between border-b border-white/10 pb-4">
        <div>
            <p class="text-xs uppercase tracking-wider text-brand-400 font-bold">Pages</p>
            <h1 class="font-display text-2xl font-bold text-white">Buat Halaman Baru</h1>
        </div>
        <a href="{{ route('admin.pages.index') }}" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white">&larr; Kembali</a>
    </div>

    <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated">
        <form method="POST" action="{{ route('admin.pages.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Judul Halaman <span class="text-brand-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="Contoh: Privacy Policy & Cookies" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
                @error('title') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Konten Halaman (HTML/Rich Text) <span class="text-brand-500">*</span></label>
                <textarea name="content" rows="10" required placeholder="Tuliskan konten halaman lengkap..." class="w-full rounded-xl border border-white/10 bg-white/5 p-4 text-xs font-mono text-white leading-relaxed">{{ old('content') }}</textarea>
                @error('content') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Custom Meta Title</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title') }}" placeholder="SEO Meta Title" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs text-white">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Meta Description</label>
                    <input type="text" name="meta_description" value="{{ old('meta_description') }}" placeholder="SEO Description..." class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs text-white">
                </div>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-2.5 rounded-xl border border-white/5 bg-white/5 p-3 text-xs text-slate-300 cursor-pointer">
                    <input type="checkbox" name="is_published" value="1" @checked(old('is_published', true)) class="rounded border-white/20 bg-[#161924] text-brand-500 focus:ring-0">
                    <span class="font-bold text-white">Publikasikan Halaman Ini Sekarang</span>
                </label>
            </div>

            <div class="pt-4 border-t border-white/5 flex justify-end gap-3">
                <a href="{{ route('admin.pages.index') }}" class="rounded-xl border border-white/10 px-5 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10">Batal</a>
                <button type="submit" class="rounded-xl bg-brand-500 px-6 py-2 text-xs font-bold text-white shadow-glow hover:bg-brand-600">Simpan Halaman</button>
            </div>
        </form>
    </div>
</div>
@endsection
