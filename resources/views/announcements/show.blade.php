@extends('layouts.app')

@section('title', $announcement->title . ' — Fer6origami Announcement')
@section('meta_title', $announcement->title . ' — Fer6origami')
@section('meta_description', Str::limit(strip_tags($announcement->content), 150))
@section('meta_image', $announcement->banner_image ?? asset('images/emblem.png'))

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        {{-- BREADCRUMBS --}}
        <nav class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('home') }}" class="transition hover:text-white">Home</a>
            <span>/</span>
            <span class="text-slate-300">Announcement</span>
            <span>/</span>
            <span class="truncate text-white">{{ $announcement->title }}</span>
        </nav>

        <article class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
            @if ($announcement->banner_image)
                <div class="aspect-video max-h-80 w-full overflow-hidden border-b border-white/10 sm:aspect-[21/9]">
                    <img src="{{ $announcement->banner_image }}" alt="{{ $announcement->title }}" class="h-full w-full object-cover">
                </div>
            @endif

            <div class="p-6 sm:p-8">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="rounded bg-brand-500/20 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-brand-400">
                        {{ $announcement->category }}
                    </span>
                    @if ($announcement->is_pinned)
                        <span class="rounded bg-amber-500/20 px-2.5 py-1 text-xs font-bold text-amber-300">
                            Pinned
                        </span>
                    @endif
                    <span class="text-xs text-slate-400">
                        Published on {{ $announcement->published_at?->format('M d, Y') ?? $announcement->created_at->format('M d, Y') }}
                    </span>
                    <span class="text-xs text-slate-500">•</span>
                    <span class="text-xs text-slate-400">
                        {{ number_format($announcement->reads_count) }} views
                    </span>
                </div>

                <h1 class="mt-4 font-display text-2xl font-extrabold text-white sm:text-3xl">
                    {{ $announcement->title }}
                </h1>

                <div class="mt-6 border-t border-white/5 pt-6 leading-relaxed text-slate-200">
                    <div class="prose prose-invert max-w-none text-sm space-y-4">
                        {!! nl2br(e($announcement->content)) !!}
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-between border-t border-white/5 pt-4 text-xs text-slate-400">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 font-semibold text-brand-400 hover:underline">
                        &larr; Back to Home
                    </a>
                </div>
            </div>
        </article>
    </div>
@endsection
