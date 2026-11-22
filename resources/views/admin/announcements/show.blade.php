@extends('layouts.app')

@section('title', 'Lihat Pengumuman: ' . $announcement->title . ' — Admin Fer6origami')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex items-center justify-between border-b border-white/10 pb-4">
        <div>
            <p class="text-xs uppercase tracking-wider text-brand-400 font-bold">Communication</p>
            <h1 class="font-display text-2xl font-bold text-white">{{ $announcement->title }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.announcements.edit', $announcement) }}" class="rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white hover:bg-white/20">Edit</a>
            <a href="{{ route('admin.announcements.index') }}" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white">&larr; Kembali</a>
        </div>
    </div>

    <div class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
        @if ($announcement->banner_url)
            <div class="h-64 w-full bg-slate-900">
                <img src="{{ $announcement->banner_url }}" alt="" class="h-full w-full object-cover">
            </div>
        @endif

        <div class="p-6 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-white/5 pb-3">
                <div class="flex items-center gap-2">
                    <span class="rounded bg-brand-500/20 px-2.5 py-0.5 text-xs font-bold text-brand-400">
                        {{ $announcement->category }}
                    </span>
                    <span class="rounded px-2.5 py-0.5 text-xs font-bold {{ $announcement->status === 'published' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-500/20 text-slate-400' }}">
                        {{ ucfirst($announcement->status) }}
                    </span>
                    @if ($announcement->is_pinned)
                        <span class="rounded bg-amber-500/20 px-2.5 py-0.5 text-xs font-bold text-amber-400">Pinned</span>
                    @endif
                    @if ($announcement->is_popup)
                        <span class="rounded bg-purple-500/20 px-2.5 py-0.5 text-xs font-bold text-purple-400">Popup</span>
                    @endif
                </div>
                <div class="text-xs text-slate-400">
                    Dibaca: <strong class="text-white">{{ number_format($announcement->reads_count) }} kali</strong>
                </div>
            </div>

            <div class="text-xs text-slate-400 flex flex-wrap gap-4">
                <span>Diterbitkan: <strong class="text-slate-300">{{ $announcement->published_at ? $announcement->published_at->translatedFormat('d F Y, H:i') : '-' }}</strong></span>
                <span>Oleh: <strong class="text-slate-300">{{ $announcement->creator->name ?? 'Admin' }}</strong></span>
            </div>

            <div class="border-t border-white/5 pt-4 text-xs leading-relaxed text-slate-200 whitespace-pre-wrap">
                {{ $announcement->content }}
            </div>
        </div>
    </div>
</div>
@endsection
