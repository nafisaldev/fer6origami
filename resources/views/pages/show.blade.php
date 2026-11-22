@extends('layouts.app')

@section('title', ($page->meta_title ?: $page->title) . ' — Fer6origami')
@section('meta_description', $page->meta_description ?: Str::limit(strip_tags($page->content), 160))

@section('content')
    <div class="mx-auto max-w-4xl py-6">
        <div class="surface-panel rounded-2xl border border-white/5 p-8 shadow-elevated sm:p-12">
            <div class="border-b border-white/10 pb-6">
                <span class="text-xs font-bold uppercase tracking-wider text-brand-400">Fer6origami Information</span>
                <h1 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                    {{ $page->title }}
                </h1>
                <p class="mt-2 text-xs text-slate-400">
                    Last updated on {{ $page->updated_at->format('F d, Y') }}
                </p>
            </div>

            <article class="prose prose-invert max-w-none mt-8 text-sm leading-relaxed text-slate-200">
                {!! nl2br(e($page->content)) !!}
            </article>
        </div>
    </div>
@endsection
