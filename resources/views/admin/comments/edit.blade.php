@extends('layouts.app')

@section('title', 'Edit Komentar — Admin Fer6origami')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div class="flex items-center justify-between border-b border-white/10 pb-4">
        <div>
            <p class="text-xs uppercase tracking-wider text-brand-400 font-bold">Comment Moderation</p>
            <h1 class="font-display text-2xl font-bold text-white">Edit Komentar</h1>
        </div>
        <a href="{{ route('admin.comments.index') }}" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white">&larr; Kembali</a>
    </div>

    <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated">
        <form method="POST" action="{{ route('admin.comments.update', $comment) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="rounded-xl bg-white/5 p-3.5 border border-white/5 text-xs space-y-1">
                <p class="text-slate-400">Penulis: <strong class="text-white">{{ $comment->user->name ?? 'User' }}</strong> ({{ $comment->user->email ?? '' }})</p>
                <p class="text-slate-400">Target Anime: <strong class="text-white">{{ $comment->anime->title ?? 'N/A' }}</strong> {{ $comment->episode ? '• Ep ' . $comment->episode->episode_number : '' }}</p>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Isi Komentar</label>
                <textarea name="content" rows="5" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">{{ old('content', $comment->content) }}</textarea>
                @error('content') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Status Komentar</label>
                <select name="status" class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2 text-xs text-white">
                    @foreach ($statuses as $k => $v)
                        <option value="{{ $k }}" @selected(old('status', $comment->status) === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>

            <div class="pt-4 border-t border-white/5 flex justify-end gap-3">
                <a href="{{ route('admin.comments.index') }}" class="rounded-xl border border-white/10 px-5 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10">Batal</a>
                <button type="submit" class="rounded-xl bg-brand-500 px-6 py-2 text-xs font-bold text-white shadow-glow hover:bg-brand-600">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
