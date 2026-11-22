@extends('layouts.app')

@section('title', 'Buat Pengumuman Baru — Admin Fer6origami')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex items-center justify-between border-b border-white/10 pb-4">
        <div>
            <p class="text-xs uppercase tracking-wider text-brand-400 font-bold">Communication</p>
            <h1 class="font-display text-2xl font-bold text-white">Buat Pengumuman Baru</h1>
        </div>
        <a href="{{ route('admin.announcements.index') }}" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white">&larr; Kembali</a>
    </div>

    <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated">
        <form method="POST" action="{{ route('admin.announcements.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Judul Pengumuman <span class="text-brand-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="Contoh: Pemeliharaan Server Mingguan (Maintenance)" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
                @error('title') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Kategori</label>
                    <select name="category" class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2 text-xs text-white">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}" @selected(old('category') === $cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Status</label>
                    <select name="status" class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2 text-xs text-white">
                        @foreach ($statuses as $k => $v)
                            <option value="{{ $k }}" @selected(old('status', 'published') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Jadwal Tayang (Publish Date)</label>
                    <input type="datetime-local" name="published_at" value="{{ old('published_at', now()->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs text-white">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Tanggal Kedaluwarsa (Opsional)</label>
                    <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs text-white">
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Upload Banner Pengumuman</label>
                <input type="file" name="banner_upload" accept="image/*" class="w-full text-xs text-slate-400 file:mr-3 file:rounded-lg file:border-0 file:bg-white/10 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-white/20">
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Konten Pengumuman <span class="text-brand-500">*</span></label>
                <textarea name="content" rows="6" required placeholder="Tuliskan isi pengumuman lengkap..." class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">{{ old('content') }}</textarea>
                @error('content') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-3 sm:grid-cols-3 pt-2">
                <label class="flex items-center gap-2 rounded-xl border border-white/5 bg-white/5 p-3 text-xs text-slate-300 cursor-pointer">
                    <input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned')) class="rounded border-white/20 bg-[#161924] text-amber-500 focus:ring-0">
                    <span class="font-bold text-white">Sematkan Pengumuman</span>
                </label>
                <label class="flex items-center gap-2 rounded-xl border border-white/5 bg-white/5 p-3 text-xs text-slate-300 cursor-pointer">
                    <input type="checkbox" name="is_popup" value="1" @checked(old('is_popup')) class="rounded border-white/20 bg-[#161924] text-purple-500 focus:ring-0">
                    <span class="font-bold text-white">Tampilkan Modal Popup</span>
                </label>
                <label class="flex items-center gap-2 rounded-xl border border-white/5 bg-white/5 p-3 text-xs text-slate-300 cursor-pointer">
                    <input type="checkbox" name="send_notification_bell" value="1" @checked(old('send_notification_bell', true)) class="rounded border-white/20 bg-[#161924] text-brand-500 focus:ring-0">
                    <span class="font-bold text-white">Kirim ke Lonceng Notifikasi</span>
                </label>
            </div>

            <div class="pt-4 border-t border-white/5 flex justify-end gap-3">
                <a href="{{ route('admin.announcements.index') }}" class="rounded-xl border border-white/10 px-5 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10">Batal</a>
                <button type="submit" class="rounded-xl bg-brand-500 px-6 py-2 text-xs font-bold text-white shadow-glow hover:bg-brand-600">Terbitkan Pengumuman</button>
            </div>
        </form>
    </div>
</div>
@endsection
