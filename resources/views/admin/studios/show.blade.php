@extends('layouts.app')

@section('title', 'Detail Studio: ' . $studio->name . ' — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between border-b border-white/10 pb-4">
        <div>
            <p class="text-xs uppercase tracking-wider text-brand-400 font-bold">Studio Management</p>
            <h1 class="font-display text-2xl font-bold text-white">{{ $studio->name }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.studios.edit', $studio) }}" class="rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white hover:bg-white/20">Edit Studio</a>
            <a href="{{ route('admin.studios.index') }}" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white">&larr; Daftar Studio</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="surface-panel rounded-2xl border border-white/10 p-5 space-y-4">
            <div class="flex items-center gap-4">
                <img src="{{ $studio->logo_url }}" alt="{{ $studio->name }}" class="h-16 w-16 rounded-xl object-cover bg-slate-900 border border-white/10">
                <div>
                    <h2 class="text-base font-bold text-white">{{ $studio->name }}</h2>
                    <p class="text-xs text-slate-400">{{ $studio->country }} • Berdiri {{ $studio->founded_year ?: '-' }}</p>
                </div>
            </div>
            @if ($studio->website)
                <a href="{{ $studio->website }}" target="_blank" class="block text-xs font-semibold text-brand-400 hover:underline">
                    {{ $studio->website }} &rarr;
                </a>
            @endif
            <p class="text-xs text-slate-300 leading-relaxed border-t border-white/5 pt-3">{{ $studio->description ?: 'Belum ada deskripsi studio.' }}</p>
        </div>

        <div class="surface-panel rounded-2xl border border-white/10 p-5 lg:col-span-2">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-white/5 pb-2">
                Anime yang Diproduksi Studio Ini ({{ $studio->animes->count() }})
            </h2>
            @if ($studio->animes->isEmpty())
                <p class="py-8 text-center text-xs text-slate-500">Belum ada anime yang terhubung dengan studio ini.</p>
            @else
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($studio->animes as $anime)
                        <div class="flex items-center gap-3 rounded-xl border border-white/5 bg-white/5 p-3">
                            <img src="{{ $anime->poster_image }}" alt="" class="h-12 w-9 rounded object-cover">
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('admin.animes.show', $anime) }}" class="font-bold text-xs text-white hover:text-brand-400 truncate block">
                                    {{ $anime->title }}
                                </a>
                                <p class="text-[10px] text-slate-400">{{ $anime->year }} • {{ ucfirst($anime->status) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
