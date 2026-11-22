@extends('layouts.app')

@section('title', $anime->title . ' — Detail Admin Anime')

@section('content')
<div class="space-y-6">
    {{-- TOP BAR --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-white">Admin</a>
            <span>/</span>
            <a href="{{ route('admin.animes.index') }}" class="hover:text-white">Anime</a>
            <span>/</span>
            <span class="text-white truncate">{{ $anime->title }}</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('anime.show', ['animeId' => $anime->slug]) }}" target="_blank" class="rounded-xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white">
                Buka di Frontend &rarr;
            </a>
            <a href="{{ route('admin.animes.edit', $anime) }}" class="rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white transition hover:bg-white/20">
                Edit Anime
            </a>
            <a href="{{ route('admin.episodes.create', ['anime_id' => $anime->id]) }}" class="rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white shadow-glow transition hover:bg-brand-600">
                + Upload Episode
            </a>
        </div>
    </div>

    {{-- HERO DETAIL CARD --}}
    <div class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
        <div class="relative h-48 w-full bg-slate-900 sm:h-64">
            <img src="{{ $anime->banner_image }}" alt="{{ $anime->title }}" class="h-full w-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-[#11131b] via-[#11131b]/60 to-transparent"></div>
        </div>

        <div class="relative -mt-20 p-6 pt-0">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-end">
                <img src="{{ $anime->poster_image }}" alt="{{ $anime->title }}" class="h-44 w-32 shrink-0 rounded-xl object-cover ring-4 ring-[#11131b] shadow-2xl">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded bg-brand-500/20 px-2.5 py-0.5 text-xs font-extrabold text-brand-400 uppercase">
                            {{ $anime->status }}
                        </span>
                        <span class="rounded bg-white/10 px-2.5 py-0.5 text-xs font-bold text-slate-300">
                            {{ $anime->rating }}
                        </span>
                        <span class="text-xs text-amber-400 font-bold">Rating {{ number_format((float) $anime->score, 1) }}</span>
                    </div>
                    <h1 class="mt-2 font-display text-2xl font-extrabold text-white sm:text-3xl">{{ $anime->title }}</h1>
                    <p class="mt-1 text-xs text-slate-400">
                        {{ $anime->studio ?: 'Unknown Studio' }} • {{ $anime->year }} • {{ $anime->type }} • Rilis: {{ ucfirst($anime->release_day ?: 'Belum ditentukan') }}
                    </p>
                </div>
            </div>

            <div class="mt-6 border-t border-white/5 pt-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Sinopsis</h3>
                <p class="mt-2 text-xs leading-relaxed text-slate-300">{{ $anime->synopsis }}</p>
            </div>

            <div class="mt-4 flex flex-wrap gap-1.5 pt-3 border-t border-white/5">
                @foreach ($anime->genres as $genre)
                    <span class="rounded-lg bg-white/5 px-2.5 py-1 text-xs text-slate-300">
                        {{ $genre->name }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- EPISODES LIST --}}
    <div class="surface-panel rounded-2xl border border-white/10 p-5 shadow-elevated">
        <div class="mb-4 flex items-center justify-between border-b border-white/5 pb-3">
            <div>
                <h2 class="text-sm font-bold text-white">Daftar Episode Anime Ini</h2>
                <p class="text-xs text-slate-400">{{ $anime->episodes->count() }} episode terdaftar</p>
            </div>
            <a href="{{ route('admin.episodes.create', ['anime_id' => $anime->id]) }}" class="rounded-xl bg-brand-500 px-3.5 py-1.5 text-xs font-bold text-white shadow-glow hover:bg-brand-600">
                + Tambah Episode
            </a>
        </div>

        @if ($anime->episodes->isEmpty())
            <p class="py-8 text-center text-xs text-slate-500">Belum ada episode yang diunggah untuk anime ini.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="border-b border-white/5 text-[10px] uppercase font-bold text-slate-400">
                        <tr>
                            <th class="py-2.5 px-3">Eps #</th>
                            <th class="py-2.5 px-3">Judul Episode</th>
                            <th class="py-2.5 px-3">Durasi</th>
                            <th class="py-2.5 px-3">Server</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach ($anime->episodes as $ep)
                            <tr class="hover:bg-white/[0.02]">
                                <td class="py-2.5 px-3 font-bold text-white">Eps {{ $ep->episode_number }}</td>
                                <td class="py-2.5 px-3 font-semibold text-slate-200">{{ $ep->title }}</td>
                                <td class="py-2.5 px-3 text-slate-400">{{ $ep->duration_minutes ? $ep->duration_minutes . ' min' : '-' }}</td>
                                <td class="py-2.5 px-3 text-slate-400">{{ is_array($ep->servers) ? count($ep->servers) : 0 }} server</td>
                                <td class="py-2.5 px-3">
                                    <span class="rounded px-2 py-0.5 text-[10px] font-bold {{ $ep->is_published ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-500/20 text-slate-400' }}">
                                        {{ $ep->is_published ? 'Published' : 'Draft' }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.episodes.show', $ep) }}" class="rounded bg-white/10 px-2 py-1 text-[10px] text-white hover:bg-white/20">Detail</a>
                                        <a href="{{ route('admin.episodes.edit', $ep) }}" class="rounded bg-white/10 px-2 py-1 text-[10px] text-white hover:bg-white/20">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
