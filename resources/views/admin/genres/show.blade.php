@extends('layouts.app')

@section('title', 'Genre: ' . $genre->name . ' — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between border-b border-white/10 pb-4">
        <div>
            <p class="text-xs uppercase tracking-wider text-brand-400 font-bold">Genre Management</p>
            <h1 class="font-display text-2xl font-bold text-white">Genre: {{ $genre->name }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.genres.edit', $genre) }}" class="rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white hover:bg-white/20">Edit Genre</a>
            <a href="{{ route('admin.genres.index') }}" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white">&larr; Daftar Genre</a>
        </div>
    </div>

    <div class="surface-panel rounded-2xl border border-white/10 p-5">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-white/5 pb-2">
            Anime dengan Genre Ini ({{ $genre->animes->count() }})
        </h2>
        @if ($genre->animes->isEmpty())
            <p class="py-8 text-center text-xs text-slate-500">Belum ada anime dengan genre {{ $genre->name }}.</p>
        @else
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($genre->animes as $anime)
                    <div class="flex items-center gap-3 rounded-xl border border-white/5 bg-white/5 p-3">
                        <img src="{{ $anime->poster_image }}" alt="" class="h-12 w-9 rounded object-cover">
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('admin.animes.show', $anime) }}" class="font-bold text-xs text-white hover:text-brand-400 truncate block">
                                {{ $anime->title }}
                            </a>
                            <p class="text-[10px] text-slate-400">{{ $anime->studio ?: 'Anime' }} • {{ $anime->year }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
