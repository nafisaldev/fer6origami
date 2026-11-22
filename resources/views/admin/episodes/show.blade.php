@extends('layouts.app')

@section('title', 'Detail Episode ' . $episode->episode_number . ' — ' . ($episode->anime->title ?? 'Anime'))

@section('content')
<div class="space-y-6">
    {{-- BREADCRUMBS --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-white">Admin</a>
            <span>/</span>
            <a href="{{ route('admin.episodes.index') }}" class="hover:text-white">Episode</a>
            <span>/</span>
            <span class="text-white truncate">{{ $episode->anime->title ?? 'Anime' }} - Ep {{ $episode->episode_number }}</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.episodes.edit', $episode) }}" class="rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white transition hover:bg-white/20">
                Edit Episode
            </a>
            <a href="{{ route('admin.animes.show', $episode->anime_id) }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white">
                Lihat Seri Anime &rarr;
            </a>
        </div>
    </div>

    {{-- PLAYER PREVIEW & METADATA --}}
    <div class="grid gap-6 lg:grid-cols-12">
        <div class="space-y-5 lg:col-span-8">
            <div class="surface-panel overflow-hidden rounded-2xl border border-white/10 bg-black shadow-elevated">
                @if (!empty($episode->video_url))
                    @if (preg_match('/\.(mp4|m3u8|webm)(\?.*)?$/i', $episode->video_url))
                        <video controls class="aspect-video w-full bg-black object-contain">
                            <source src="{{ $episode->video_url }}" type="video/mp4">
                            Browser tidak mendukung pemutaran HTML5.
                        </video>
                    @else
                        <iframe src="{{ $episode->video_url }}" class="aspect-video w-full bg-black" allowfullscreen></iframe>
                    @endif
                @else
                    <div class="flex aspect-video w-full flex-col items-center justify-center p-6 text-center bg-[#0d0f17]">
                        <p class="text-xs text-slate-500">Tidak ada link video streaming langsung yang dimasukkan.</p>
                    </div>
                @endif
            </div>

            <div class="surface-panel rounded-2xl border border-white/10 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-brand-400">Episode {{ $episode->episode_number }}</span>
                        <h1 class="font-display text-xl font-bold text-white">{{ $episode->title }}</h1>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $episode->anime->title ?? '' }}</p>
                    </div>
                    <span class="rounded px-2.5 py-1 text-xs font-bold {{ $episode->is_published ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-500/20 text-slate-400' }}">
                        {{ $episode->is_published ? 'Published' : 'Draft' }}
                    </span>
                </div>
                @if ($episode->synopsis)
                    <p class="mt-3 text-xs leading-relaxed text-slate-300 border-t border-white/5 pt-3">{{ $episode->synopsis }}</p>
                @endif
            </div>
        </div>

        {{-- SERVERS LIST & INFO --}}
        <div class="space-y-5 lg:col-span-4">
            <div class="surface-panel rounded-2xl border border-white/10 p-5">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-white/5 pb-2">
                    Daftar Server Streaming ({{ is_array($episode->servers) ? count($episode->servers) : 0 }})
                </h2>
                @if (empty($episode->servers))
                    <p class="py-4 text-center text-xs text-slate-500">Hanya server video utama.</p>
                @else
                    <div class="mt-3 space-y-2">
                        @foreach ($episode->servers as $srv)
                            <div class="rounded-xl border border-white/5 bg-white/5 p-3">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-xs text-white">{{ $srv['name'] ?? 'Server' }}</span>
                                    <span class="text-[10px] rounded bg-brand-500/20 px-2 py-0.5 text-brand-400 font-bold">{{ $srv['quality'] ?? '720p' }}</span>
                                </div>
                                <p class="mt-1 font-mono text-[10px] text-slate-400 truncate">{{ $srv['url'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="surface-panel rounded-2xl border border-white/10 p-5">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-white/5 pb-2">
                    Informasi Detail
                </h2>
                <div class="mt-3 space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-white/5">
                        <span class="text-slate-400">Durasi</span>
                        <span class="font-semibold text-white">{{ $episode->duration_minutes ? $episode->duration_minutes . ' menit' : '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-white/5">
                        <span class="text-slate-400">Tanggal Tayang</span>
                        <span class="font-semibold text-white">{{ $episode->aired_at ? $episode->aired_at->translatedFormat('d M Y, H:i') : '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-white/5">
                        <span class="text-slate-400">Komentar</span>
                        <span class="font-semibold text-white">{{ $episode->comments->count() }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-400">Slug</span>
                        <span class="font-mono text-slate-300 text-[10px]">{{ $episode->slug }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
