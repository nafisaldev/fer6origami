@extends('layouts.app')

@section('title', 'Kirim Notifikasi Broadcast — Admin Fer6origami')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div class="flex items-center justify-between border-b border-white/10 pb-4">
        <div>
            <p class="text-xs uppercase tracking-wider text-brand-400 font-bold">Communication</p>
            <h1 class="font-display text-2xl font-bold text-white">Kirim Notifikasi Broadcast</h1>
        </div>
        <a href="{{ route('admin.notifications.index') }}" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white">&larr; Kembali</a>
    </div>

    <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated">
        <form method="POST" action="{{ route('admin.notifications.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Judul Notifikasi <span class="text-brand-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="Contoh: Server Maintenance Selesai" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
                @error('title') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Kategori Notifikasi</label>
                    <select name="type" class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2 text-xs text-white">
                        @foreach ($types as $k => $v)
                            <option value="{{ $k }}" @selected(old('type') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Target Penerima</label>
                    <select name="target_role" class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2 text-xs text-white">
                        <option value="">Seluruh Pengguna (All Users)</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->slug }}" @selected(old('target_role') === $role->slug)>Khusus: {{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Isi Pesan Notifikasi <span class="text-brand-500">*</span></label>
                <textarea name="message" rows="4" required placeholder="Tuliskan pesan notifikasi yang akan muncul di lonceng pengguna..." class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">{{ old('message') }}</textarea>
                @error('message') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">URL Tindakan (Opsional)</label>
                <input type="url" name="action_url" value="{{ old('action_url') }}" placeholder="https://..." class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
            </div>

            <div class="pt-4 border-t border-white/5 flex justify-end gap-3">
                <a href="{{ route('admin.notifications.index') }}" class="rounded-xl border border-white/10 px-5 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10">Batal</a>
                <button type="submit" class="rounded-xl bg-brand-500 px-6 py-2 text-xs font-bold text-white shadow-glow hover:bg-brand-600">Kirim Sekarang</button>
            </div>
        </form>
    </div>
</div>
@endsection
