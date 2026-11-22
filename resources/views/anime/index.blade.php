@extends('layouts.app')

@section('title', 'Complete Anime Catalog — Fer6origami')

@section('content')
    <div class="space-y-6">
        {{-- CATALOG HEADER --}}
        <div class="surface-panel flex flex-col justify-between gap-4 rounded-xl p-6 md:flex-row md:items-center">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-brand-400">Collection Library</span>
                <h1 class="mt-1 font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">
                    Anime Catalog
                </h1>
                <p class="mt-1 text-xs text-slate-400">
                    Showing {{ $animes->count() }} titles of {{ number_format($catalogTotal) }} anime available.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 text-xs">
                <a href="{{ route('anime.index') }}"
                   class="rounded-lg px-3 py-1.5 font-semibold transition {{ !request('status') ? 'bg-brand-500 font-bold text-white' : 'border border-white/10 bg-[#161924] text-slate-300 hover:bg-white/10' }}">
                    All
                </a>
                <a href="{{ route('anime.index', ['status' => 'ongoing']) }}"
                   class="rounded-lg px-3 py-1.5 font-semibold transition {{ request('status') === 'ongoing' ? 'bg-brand-500 font-bold text-white' : 'border border-white/10 bg-[#161924] text-slate-300 hover:bg-white/10' }}">
                    Ongoing
                </a>
                <a href="{{ route('anime.index', ['status' => 'completed']) }}"
                   class="rounded-lg px-3 py-1.5 font-semibold transition {{ request('status') === 'completed' ? 'bg-brand-500 font-bold text-white' : 'border border-white/10 bg-[#161924] text-slate-300 hover:bg-white/10' }}">
                    Completed
                </a>
            </div>
        </div>

        {{-- MAIN LAYOUT: FILTER SIDEBAR & ANIME GRID --}}
        <section class="grid gap-6 lg:grid-cols-12">
            {{-- FILTER SIDEBAR (3 COLS) --}}
            <aside class="lg:col-span-3">
                <div class="surface-panel rounded-xl p-5 sticky top-20">
                    <div class="mb-4 flex items-center justify-between border-b border-white/5 pb-2">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-white">Search Filters</h2>
                        @if (!empty(array_filter($selectedFilters)))
                            <a href="{{ route('anime.index') }}" class="text-[11px] font-semibold text-brand-400 hover:underline">
                                Reset
                            </a>
                        @endif
                    </div>

                    <form action="{{ route('anime.index') }}" method="GET" class="space-y-4 text-xs">
                        <div>
                            <label class="mb-1.5 block font-semibold text-slate-300">Keywords</label>
                            <input
                                type="text"
                                name="search"
                                value="{{ $selectedFilters['search'] ?? '' }}"
                                placeholder="Anime title or studio..."
                                class="w-full rounded-lg border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white placeholder:text-slate-500 focus:border-brand-500 focus:outline-none"
                            >
                        </div>

                        <div>
                            <label class="mb-1.5 block font-semibold text-slate-300">Genre</label>
                            <select name="genre" class="w-full rounded-lg border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white focus:border-brand-500 focus:outline-none">
                                <option value="">All Genres</option>
                                @foreach ($genres as $genre)
                                    <option value="{{ $genre->id }}" @selected(($selectedFilters['genre'] ?? '') === $genre->id)>{{ $genre->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="mb-1.5 block font-semibold text-slate-300">Year</label>
                                <select name="year" class="w-full rounded-lg border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white focus:border-brand-500 focus:outline-none">
                                    <option value="">All</option>
                                    @foreach ($years as $year)
                                        <option value="{{ $year }}" @selected((string) ($selectedFilters['year'] ?? '') === (string) $year)>{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block font-semibold text-slate-300">Status</label>
                                <select name="status" class="w-full rounded-lg border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white focus:border-brand-500 focus:outline-none">
                                    <option value="">All</option>
                                    @foreach ($statuses as $value => $label)
                                        <option value="{{ $value }}" @selected(($selectedFilters['status'] ?? '') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1.5 block font-semibold text-slate-300">Minimum Score</label>
                            <select name="rating" class="w-full rounded-lg border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white focus:border-brand-500 focus:outline-none">
                                <option value="">All Scores</option>
                                @foreach ($ratings as $value => $label)
                                    <option value="{{ $value }}" @selected(($selectedFilters['rating'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="w-full rounded-lg bg-brand-500 py-2.5 font-bold text-white shadow-subtle transition hover:bg-brand-600">
                            Apply Filters
                        </button>
                    </form>
                </div>
            </aside>

            {{-- ANIME GRID (9 COLS) --}}
            <div class="lg:col-span-9">
                @if ($animes->count())
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4">
                        @foreach ($animes as $anime)
                            <x-anime-card :anime="$anime" />
                        @endforeach
                    </div>

                    {{-- PAGINATION --}}
                    <div class="mt-8">
                        {{ $pagination->paginator->links() }}
                    </div>

                    @if ($pagination->previous_page || $pagination->next_page)
                        <div class="mt-6 flex items-center justify-center gap-3 text-xs">
                            @if ($pagination->previous_page)
                                <a
                                    href="{{ route('anime.index', array_merge(request()->query(), ['page' => $pagination->previous_page])) }}"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-[#161924] px-4 py-2 font-semibold text-white transition hover:bg-white/10"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                                    Previous
                                </a>
                            @endif

                            <span class="rounded-lg bg-white/5 px-3 py-2 font-bold text-slate-400">
                                Page {{ $pagination->current_page }} of {{ $pagination->paginator->lastPage() }}
                            </span>

                            @if ($pagination->next_page)
                                <a
                                    href="{{ route('anime.index', array_merge(request()->query(), ['page' => $pagination->next_page])) }}"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 font-bold text-white transition hover:bg-brand-600"
                                >
                                    Next
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </a>
                            @endif
                        </div>
                    @endif
                @else
                    <x-empty-state
                        title="No Matching Anime Found"
                        message="Try adjusting your search keywords or resetting the genre, year, and status filters to explore other titles."
                        :actionUrl="route('anime.index')"
                        actionText="Reset All Filters"
                    />
                @endif
            </div>
        </section>
    </div>
@endsection
