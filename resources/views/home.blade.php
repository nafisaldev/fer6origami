@extends('layouts.app')

@section('title', 'Fer6origami — Watch Anime Online & Complete Anime Catalog')

@section('content')
    @php
        // Prepare featured anime for the rotating Hero Carousel
        $heroSlides = isset($heroSlides) && $heroSlides->isNotEmpty()
            ? $heroSlides
            : ($banners->isNotEmpty()
                ? $banners
                : $trendingAnimes->concat($popularAnimes)->unique('id')->take(5)->values());
    @endphp

    <div class="space-y-14 sm:space-y-16 lg:space-y-20">
        {{-- INTERACTIVE ROTATING HERO SPOTLIGHT CAROUSEL --}}
        <section id="hero-carousel" class="relative overflow-hidden rounded-2xl border border-white/5 bg-[#11131b] animate-gradient-border" data-reveal="fade" data-active-slide="0" data-total-slides="{{ $heroSlides->count() }}">
            {{-- Ambient light orbs --}}
            <div class="absolute -top-16 -left-16 hidden w-32 h-32 rounded-full bg-brand-500/10 blur-3xl pointer-events-none sm:block" aria-hidden="true"></div>
            <div class="absolute -bottom-10 -right-20 hidden w-32 h-32 rounded-full bg-brand-500/10 blur-3xl pointer-events-none sm:block" aria-hidden="true"></div>

            <div class="relative min-h-[470px] sm:min-h-[540px] lg:min-h-[560px]">
                {{-- CAROUSEL SLIDES --}}
                @foreach ($heroSlides as $index => $anime)
                    <div
                        data-hero-slide="{{ $index }}"
                        class="hero-slide absolute inset-0 transition-opacity duration-700 ease-in-out {{ $index === 0 ? 'opacity-100 z-10' : 'opacity-0 pointer-events-none z-0' }}"
                    >
                        {{-- BACKDROP HERO IMAGE --}}
                        <img
                            src="{{ $anime->banner_image ?? ($anime->image_url ?? $anime->poster_image) }}"
                            alt="{{ $anime->title }}"
                            data-fallback-src="{{ asset('images/poster-placeholder.svg') }}"
                            loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                            @if ($index === 0) fetchpriority="high" @endif
                            decoding="async"
                            class="absolute inset-0 h-full w-full object-cover object-[center_35%] opacity-35 sm:object-center"
                        >
                        <div class="cinematic-hero-scrim absolute inset-0"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-[#11131b] via-[#11131b]/40 to-transparent"></div>

                        {{-- SLIDE CONTENT GRID --}}
                        <div class="relative grid min-h-[470px] items-center gap-8 px-5 pb-24 pt-8 sm:min-h-[540px] sm:px-10 sm:pb-24 sm:pt-10 lg:min-h-[560px] lg:grid-cols-12 lg:gap-10 lg:pb-10">
                            {{-- LEFT INFO (ENGLISH) --}}
                            <div class="flex min-w-0 flex-col justify-center lg:col-span-7">
                                {{-- BADGES --}}
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 rounded-md bg-brand-500/20 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-brand-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-brand-500 animate-pulse"></span>
                                        Featured Spotlight #{{ $index + 1 }}
                                    </span>
                                    <span class="hidden rounded-md bg-white/10 px-2.5 py-1 text-[11px] font-semibold text-slate-200 capitalize sm:inline-flex">
                                        {{ $anime->status ?: 'Available' }}
                                    </span>
                                    @if (!empty($anime->year))
                                        <span class="hidden rounded-md bg-white/5 px-2.5 py-1 text-[11px] font-semibold text-slate-300 sm:inline-flex">
                                            {{ $anime->year }}
                                        </span>
                                    @endif
                                    @php
                                        $slideScore = !empty($anime->score) && (float) $anime->score > 0
                                            ? (float) $anime->score
                                            : round(7.6 + (abs(crc32($anime->id ?? '')) % 110) / 100, 1);
                                    @endphp
                                    <span class="hidden items-center gap-1.5 rounded-md border border-amber-400/20 bg-amber-400/15 px-2.5 py-0.5 text-xs font-bold text-amber-300 sm:inline-flex">
                                        <svg class="h-3 w-3 fill-amber-400" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        <span>{{ number_format($slideScore, 1) }}</span>
                                    </span>
                                </div>

                                {{-- TITLE --}}
                                <h1 class="mt-4 line-clamp-2 break-words font-display text-2xl font-extrabold leading-tight tracking-tight text-white sm:text-3xl lg:text-4xl xl:text-5xl">
                                    {{ $anime->title }}
                                </h1>

                                {{-- ABOUT THIS ANIME (ENGLISH) --}}
                                <div class="mt-3.5 max-w-xl">
                                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1.5">
                                        <span>ABOUT THIS ANIME</span>
                                        <span>•</span>
                                        <span>{{ $anime->studio ?: 'Animation Studio' }}</span>
                                        @if (!empty($anime->episode_label))
                                            <span>•</span>
                                            <span class="text-brand-400">{{ $anime->episode_label }}</span>
                                        @endif
                                    </div>
                                    <p class="line-clamp-2 text-sm leading-relaxed text-slate-300 sm:line-clamp-3">
                                        {{ $anime->synopsis && !str_starts_with($anime->synopsis, 'Detail') && !str_starts_with($anime->synopsis, 'Episode ')
                                            ? $anime->synopsis
                                            : ($anime->title . ' is currently featured in Fer6origami. Watch the latest episodes in high definition with fast streaming servers.') }}
                                    </p>
                                </div>

                                {{-- GENRE PILLS --}}
                                @if (!empty($anime->genres) && $anime->genres->count())
                                    <div class="mt-4 flex flex-wrap gap-1.5">
                                        @foreach ($anime->genres->take(3) as $genre)
                                            <span class="rounded-md border border-white/10 bg-white/5 px-2.5 py-1 text-[11px] font-medium text-slate-300">
                                                {{ $genre->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- CTAS (ENGLISH) --}}
                                <div class="mt-6 flex flex-wrap items-center gap-3">
                                    <a href="{{ route('anime.show', ['animeId' => $anime->route_id ?? $anime->id]) }}"
                                       class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-bold text-white shadow-subtle transition hover:bg-brand-600 hover:scale-[1.02] sm:w-auto">
                                        <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z" />
                                        </svg>
                                        <span>Watch Now</span>
                                    </a>

                                    <a href="{{ route('anime.index', ['status' => 'ongoing']) }}"
                                       class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-semibold text-slate-200 transition hover:bg-white/10 hover:text-white sm:w-auto">
                                        <span>View Ongoing Schedule</span>
                                    </a>
                                </div>
                            </div>

                            {{-- RIGHT PREVIEW CARD --}}
                            <div class="hidden lg:col-span-5 lg:block">
                                <div class="surface-panel overflow-hidden rounded-xl p-4 shadow-elevated border border-white/10">
                                    <div class="relative aspect-[16/10] w-full overflow-hidden rounded-lg bg-black">
                                        <img
                                            src="{{ $anime->poster_image }}"
                                            alt="{{ $anime->title }}"
                                            loading="lazy"
                                            decoding="async"
                                            data-fallback-src="{{ asset('images/poster-placeholder.svg') }}"
                                            class="h-full w-full object-cover"
                                        >
                                        <div class="cinematic-card-scrim absolute inset-0"></div>
                                        <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-xs text-white">
                                            <span class="rounded bg-black/70 px-2 py-0.5 font-bold uppercase tracking-wider text-slate-200 backdrop-blur-sm">Spotlight Anime</span>
                                            <span class="inline-flex items-center gap-1 font-bold text-amber-300">
                                                <svg class="h-3 w-3 fill-amber-400" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                                <span>{{ number_format($slideScore, 1) }}</span>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mt-3.5 space-y-2">
                                        <div class="flex items-center justify-between">
                                            <h3 class="truncate text-base font-bold text-white">{{ $anime->title }}</h3>
                                            <span class="shrink-0 text-xs text-brand-400 font-semibold">{{ $anime->episode_label ?: ($anime->status ?: 'Series') }}</span>
                                        </div>
                                        <div class="flex items-center justify-between border-t border-white/5 pt-2 text-xs text-slate-400">
                                            <span>Studio: <strong class="text-slate-300 font-semibold">{{ $anime->studio ?: 'Unknown' }}</strong></span>
                                            <span>Year: <strong class="text-slate-300 font-semibold">{{ $anime->year ?: 'TBA' }}</strong></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- CAROUSEL CONTROLS & THUMBNAILS (BOTTOM BAR) --}}
                <div class="absolute bottom-4 left-6 right-6 z-20 flex flex-wrap items-center justify-between gap-4 sm:bottom-6 sm:left-10 sm:right-10">
                    {{-- DOT / SLIDE INDICATORS --}}
                    <div class="flex items-center gap-2">
                        @foreach ($heroSlides as $i => $item)
                            <button
                                type="button"
                                data-slide-indicator="{{ $i }}"
                                class="h-2 rounded-full transition-all duration-300 {{ $i === 0 ? 'w-8 bg-brand-500' : 'w-2 bg-white/20 hover:bg-white/40' }}"
                                aria-label="Go to slide {{ $i + 1 }}"
                            ></button>
                        @endforeach
                    </div>

                    {{-- PREV & NEXT BUTTONS --}}
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            id="hero-prev"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 bg-black/50 text-slate-200 transition hover:bg-brand-500 hover:text-white"
                            aria-label="Previous Spotlight"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            id="hero-next"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 bg-black/50 text-slate-200 transition hover:bg-brand-500 hover:text-white"
                            aria-label="Next Spotlight"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        {{-- QUICK GENRE BAR --}}
        <section class="surface-panel rounded-xl p-4" data-reveal="up">
            <div class="mb-3 flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Browse Popular Genres</span>
                <a href="{{ route('anime.index') }}" class="text-xs font-semibold text-brand-400 hover:underline">All Genres</a>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach ($filters['genres']->take(12) as $genre)
                    <a href="{{ route('anime.index', ['genre' => $genre->id]) }}"
                       class="rounded-lg border border-white/5 bg-[#161924] px-3 py-1.5 text-xs font-semibold text-slate-300 transition hover:border-brand-500/30 hover:bg-white/10 hover:text-white hover:shadow-[0_0_12px_rgba(229,53,53,0.15)] hover:scale-105">
                        {{ $genre->name }}
                    </a>
                @endforeach
            </div>
        </section>

        {{-- TRENDING ANIME (4 COLUMNS) --}}
        @if ($sectionSettings['section_trending'] ?? true)
            <section>
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="font-display text-2xl font-bold tracking-tight text-white">Trending Now</h2>
                        <p class="text-xs text-slate-400">Most watched anime right now</p>
                    </div>
                    <a href="{{ route('anime.index') }}" class="text-xs font-semibold text-brand-400 transition hover:underline">
                        View All
                    </a>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
                    @foreach ($trendingAnimes as $anime)
                        <x-anime-card :anime="$anime" />
                    @endforeach
                </div>
            </section>
        @endif

        {{-- POPULAR THIS WEEK --}}
        <section>
            <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="font-display text-2xl font-bold tracking-tight text-white">Popular This Week</h2>
                    <p class="text-xs text-slate-400">Titles with the most views in the current catalog</p>
                </div>
                <a href="{{ route('anime.index') }}" class="text-xs font-semibold text-brand-400 transition hover:underline">
                    View Full Catalog
                </a>
            </div>

            @if ($popularAnimes->isNotEmpty())
                <div class="grid min-w-0 grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-6">
                    @foreach ($popularAnimes as $anime)
                        <x-anime-card :anime="$anime" />
                    @endforeach
                </div>
            @else
                <div class="surface-panel rounded-xl p-4 text-sm text-slate-400">Belum ada anime populer yang tersedia.</div>
            @endif
        </section>

        {{-- CONTINUE WATCHING --}}
        <section class="surface-panel min-w-0 rounded-2xl border border-white/5 p-5 shadow-elevated sm:p-6">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-3 border-b border-white/5 pb-4">
                <div>
                    <h2 class="font-display text-xl font-bold tracking-tight text-white sm:text-2xl">Continue Watching</h2>
                    <p class="text-xs text-slate-400">Pick up from your last episode</p>
                </div>
                @auth
                    <a href="{{ route('profile.show') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-400 transition hover:underline">
                        <span>Full History</span>
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @endauth
            </div>

            @guest
                <p class="text-sm text-slate-400">Masuk untuk menyimpan riwayat tonton dan melanjutkan episode terakhir kamu.</p>
                <a href="{{ route('login') }}" class="mt-4 inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-brand-600">
                    Masuk
                </a>
            @endguest

            @auth
                @if ($continueWatching->isNotEmpty())
                    <div class="grid min-w-0 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($continueWatching as $history)
                            @php
                                $cwPoster = $history->anime?->poster_image ?: ($history->anime_poster ?: null);
                                $cwTitle = $history->anime?->title ?: ($history->anime_title ?: 'Anime');
                                $cwSlug = $history->anime_slug ?: ($history->anime?->slug ?? null);
                                $cwEpisode = $history->episode_title ?: ('Episode ' . ($history->episode?->episode_number ?? '1'));
                                $cwUrl = $cwSlug
                                    ? ($history->episode_route_id
                                        ? route('watch.show', ['animeId' => $cwSlug, 'episodeId' => $history->episode_route_id])
                                        : route('anime.show', $cwSlug))
                                    : null;
                            @endphp
                            @if ($cwUrl)
                                <a href="{{ $cwUrl }}" class="surface-card-hover group flex min-w-0 gap-3 rounded-xl border border-white/5 bg-white/5 p-3">
                                    <div class="relative aspect-[3/4] w-16 shrink-0 overflow-hidden rounded-lg bg-slate-900">
                                        @if ($cwPoster)
                                            <img
                                                src="{{ $cwPoster }}"
                                                alt="{{ $cwTitle }}"
                                                loading="lazy"
                                                decoding="async"
                                                data-fallback-src="{{ asset('images/poster-placeholder.svg') }}"
                                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                            >
                                        @else
                                            <span class="flex h-full w-full items-center justify-center text-[10px] font-bold text-slate-500">Anime</span>
                                        @endif
                                    </div>
                                    <div class="flex min-w-0 flex-1 flex-col justify-between gap-2 py-0.5">
                                        <div class="min-w-0">
                                            <h3 class="truncate text-sm font-bold text-white transition group-hover:text-brand-400">{{ $cwTitle }}</h3>
                                            <p class="mt-1 truncate text-xs font-semibold text-brand-400">{{ $cwEpisode }}</p>
                                        </div>
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-400">
                                            Lanjutkan
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </span>
                                    </div>
                                </a>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-400">Belum ada riwayat tonton. Episode yang kamu tonton akan muncul di sini.</p>
                    <a href="{{ route('anime.index') }}" class="mt-4 inline-flex items-center justify-center rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-slate-200 transition hover:bg-white/10 hover:text-white">
                        Jelajahi Anime
                    </a>
                @endif
            @endauth
        </section>

        {{-- LATEST EPISODES & TOP PICKS (2-COLUMN EDITORIAL SECTION) --}}
        @if ($sectionSettings['section_latest'] ?? true)
        <section class="grid gap-8 lg:grid-cols-12">
            {{-- LATEST EPISODES (8 COLS) --}}
            <div class="min-w-0 lg:col-span-8">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="font-display text-2xl font-bold tracking-tight text-white">Latest Episode Updates</h2>
                        <p class="text-xs text-slate-400">Fresh releases streamed directly from servers</p>
                    </div>
                    <span class="rounded-md bg-white/5 px-2.5 py-1 text-xs font-medium text-slate-400">
                        {{ $latestEpisodes->count() }} Episodes
                    </span>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($latestEpisodes as $episode)
                        <a href="{{ route('watch.show', ['animeId' => $episode->anime_route_id, 'episodeId' => $episode->route_id]) }}"
                           class="surface-panel surface-card-hover group flex min-w-0 gap-3.5 rounded-xl p-3">
                            <div class="relative aspect-[3/4] w-20 shrink-0 overflow-hidden rounded-lg bg-slate-900">
                                <img
                                    src="{{ $episode->anime->poster_image }}"
                                    alt="{{ $episode->anime->title }}"
                                    loading="lazy"
                                    decoding="async"
                                    data-fallback-src="{{ asset('images/poster-placeholder.svg') }}"
                                    class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                >
                                <div class="cinematic-card-scrim absolute inset-0"></div>
                                <span class="absolute bottom-1 left-1 rounded bg-brand-500/90 px-1.5 py-0.5 text-[9px] font-bold text-white">
                                    EP {{ $episode->episode_number }}
                                </span>
                            </div>

                            <div class="flex min-w-0 flex-1 flex-col justify-between py-0.5">
                                <div>
                                    <span class="text-[10px] font-medium text-slate-400">{{ $episode->uploaded_on ?: 'Just now' }}</span>
                                    <h3 class="line-clamp-2 text-sm font-bold text-white transition group-hover:text-brand-400">
                                        {{ $episode->anime->title }}
                                    </h3>
                                    <p class="mt-1 truncate text-xs text-slate-400">{{ $episode->title }}</p>
                                </div>
                                <div class="flex items-center gap-1 text-[11px] font-semibold text-brand-400">
                                    <svg class="h-3 w-3 fill-current" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                    <span>Watch</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- ONGOING HIGHLIGHTS (4 COLS) --}}
            <div class="min-w-0 space-y-6 lg:col-span-4">
                {{-- ONGOING LIST --}}
                @if ($sectionSettings['show_ongoing'] ?? true)
                <div class="surface-panel rounded-xl p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-display text-lg font-bold text-white">Ongoing Anime</h3>
                        <a href="{{ route('anime.index', ['status' => 'ongoing']) }}" class="text-xs font-semibold text-brand-400 hover:underline">
                            View All
                        </a>
                    </div>

                    <div class="space-y-3">
                        @foreach ($ongoingAnimes->take(5) as $index => $anime)
                            <a href="{{ route('anime.show', ['animeId' => $anime->route_id]) }}"
                               class="group flex items-center gap-3 rounded-lg p-1.5 transition hover:bg-white/5">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md font-display text-xs font-bold {{ $index === 0 ? 'bg-brand-500 text-white' : 'bg-white/10 text-slate-400' }}">
                                    {{ $index + 1 }}
                                </span>
                                <div class="relative aspect-square w-10 shrink-0 overflow-hidden rounded-md bg-slate-900">
                                    <img
                                        src="{{ $anime->poster_image }}"
                                        alt="{{ $anime->title }}"
                                        loading="lazy"
                                        decoding="async"
                                        data-fallback-src="{{ asset('images/poster-placeholder.svg') }}"
                                        class="h-full w-full object-cover"
                                    >
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-1">
                                        <h4 class="truncate text-xs font-bold text-white transition group-hover:text-brand-400">{{ $anime->title }}</h4>
                                        <span class="hidden shrink-0 text-[11px] font-bold text-amber-300 sm:inline">Rating {{ number_format((float) ($anime->score > 0 ? $anime->score : 7.8), 1) }}</span>
                                    </div>
                                    <p class="truncate text-[10px] text-slate-400">{{ $anime->episode_label ?: ($anime->status ?: 'Ongoing') }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- COMPLETED LIST --}}
                @if ($sectionSettings['show_completed'] ?? true)
                <div class="surface-panel rounded-xl p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-display text-lg font-bold text-white">Top Completed Anime</h3>
                        <a href="{{ route('anime.index', ['status' => 'completed']) }}" class="text-xs font-semibold text-brand-400 hover:underline">
                            View All
                        </a>
                    </div>

                    <div class="space-y-3">
                        @foreach ($completeAnimes->take(5) as $index => $anime)
                            <a href="{{ route('anime.show', ['animeId' => $anime->route_id]) }}"
                               class="group flex items-center gap-3 rounded-lg p-1.5 transition hover:bg-white/5">
                                <div class="relative aspect-square w-10 shrink-0 overflow-hidden rounded-md bg-slate-900">
                                    <img
                                        src="{{ $anime->poster_image }}"
                                        alt="{{ $anime->title }}"
                                        loading="lazy"
                                        decoding="async"
                                        data-fallback-src="{{ asset('images/poster-placeholder.svg') }}"
                                        class="h-full w-full object-cover"
                                    >
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-1">
                                        <h4 class="truncate text-xs font-bold text-white transition group-hover:text-brand-400">{{ $anime->title }}</h4>
                                        <span class="hidden shrink-0 text-[11px] font-bold text-amber-300 sm:inline">Rating {{ number_format((float) ($anime->score > 0 ? $anime->score : 7.8), 1) }}</span>
                                    </div>
                                    <p class="truncate text-[10px] text-slate-400">{{ $anime->studio ?: 'Completed' }} • {{ $anime->year ?: '' }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </section>
        @endif

        {{-- WEEKLY RELEASE SCHEDULE (FROM ADMIN SCHEDULE MANAGEMENT) --}}
        @if (($sectionSettings['show_schedule'] ?? true) && $hasScheduledAnime)
            <section class="surface-panel rounded-2xl border border-white/5 p-6 shadow-elevated">
                <div class="mb-5 flex items-center justify-between border-b border-white/5 pb-4">
                    <div>
                        <h2 class="font-display text-2xl font-bold tracking-tight text-white">Weekly Release Schedule</h2>
                        <p class="text-xs text-slate-400">Scheduled broadcasting days managed directly by administrator</p>
                    </div>
                    <span class="rounded-lg bg-brand-500/20 px-3 py-1 text-xs font-bold text-brand-400">Weekly Airing</span>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-7">
                    @foreach ($scheduleDays as $dayKey => $dayLabel)
                        @php
                            $animesForDay = $weeklySchedule[$dayKey] ?? collect();
                            $shortDay = strtoupper(substr($dayKey, 0, 3));
                        @endphp
                        <div class="rounded-xl border border-white/5 bg-[#161924]/60 p-3">
                            <div class="mb-3 flex items-center justify-between border-b border-white/5 pb-2">
                                <span class="font-display text-xs font-bold text-white">{{ $shortDay }}</span>
                                <span class="text-[10px] text-slate-500 font-semibold">{{ $animesForDay->count() }}</span>
                            </div>

                            @if ($animesForDay->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach ($animesForDay as $anime)
                                        <a href="{{ route('anime.show', ['animeId' => $anime->route_id]) }}" class="group block rounded-lg bg-white/5 p-2 transition hover:bg-white/10">
                                            <p class="line-clamp-2 text-xs font-semibold text-slate-200 transition group-hover:text-brand-400">{{ $anime->title }}</p>
                                            <span class="mt-1 block text-[10px] text-brand-400 font-medium">Rating {{ number_format((float) ($anime->score > 0 ? $anime->score : 8.0), 1) }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <p class="py-4 text-center text-[10px] text-slate-600">No releases</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- COMMUNITY REVIEWS --}}
        <section>
            <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="font-display text-2xl font-bold tracking-tight text-white">From the Community</h2>
                    <p class="text-xs text-slate-400">Recent reviews from members</p>
                </div>
            </div>

            @if ($recentReviews->isNotEmpty())
                <div class="grid min-w-0 gap-4 md:grid-cols-2">
                    @foreach ($recentReviews as $review)
                        <article class="surface-panel flex min-w-0 gap-4 rounded-xl p-4">
                            <a href="{{ route('anime.show', $review->anime->route_id) }}" class="relative aspect-[3/4] w-16 shrink-0 overflow-hidden rounded-lg bg-slate-900">
                                <img
                                    src="{{ $review->anime->poster_image }}"
                                    alt="{{ $review->anime->title }}"
                                    loading="lazy"
                                    decoding="async"
                                    data-fallback-src="{{ asset('images/poster-placeholder.svg') }}"
                                    class="h-full w-full object-cover"
                                >
                            </a>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-1 rounded-md bg-amber-400/15 px-2 py-0.5 text-xs font-bold text-amber-300">
                                        <svg class="h-3 w-3 fill-amber-400" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        {{ number_format((float) $review->rating, 1) }}
                                    </span>
                                    @if ($review->is_recommended)
                                        <span class="rounded-md bg-brand-500/15 px-2 py-0.5 text-[11px] font-semibold text-brand-400">Recommended</span>
                                    @endif
                                    <span class="text-[11px] text-slate-500">{{ $review->created_at?->diffForHumans() }}</span>
                                </div>
                                <h3 class="mt-2 truncate text-sm font-bold text-white">{{ $review->title ?: $review->anime->title }}</h3>
                                <p class="mt-1 truncate text-[11px] text-slate-400">
                                    by <span class="font-semibold text-slate-300">{{ $review->user?->name ?: 'Member' }}</span>
                                    on <a href="{{ route('anime.show', $review->anime->route_id) }}" class="font-semibold text-brand-400 hover:underline">{{ $review->anime->title }}</a>
                                </p>
                                @if ($review->content)
                                    <p class="mt-2 line-clamp-2 text-xs leading-relaxed text-slate-300">{{ $review->content }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="surface-panel rounded-xl p-4 text-sm text-slate-400">Belum ada review yang dipublikasikan.</div>
            @endif
        </section>

        {{-- ANNOUNCEMENTS --}}
        <section>
            <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="font-display text-2xl font-bold tracking-tight text-white">Announcements</h2>
                    <p class="text-xs text-slate-400">Updates from the site admin</p>
                </div>
            </div>

            @if ($announcements->isNotEmpty())
                <div class="grid min-w-0 gap-4 md:grid-cols-3">
                    @foreach ($announcements as $announcement)
                        <a href="{{ route('announcements.show', $announcement->slug) }}" class="surface-panel surface-card-hover flex min-w-0 flex-col rounded-xl p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="rounded-md bg-brand-500/15 px-2 py-0.5 text-[11px] font-bold text-brand-400">
                                    {{ $announcement->category ?: 'Information' }}
                                </span>
                                @if ($announcement->is_pinned)
                                    <span class="text-[10px] font-semibold text-slate-500">Pinned</span>
                                @endif
                            </div>
                            <h3 class="mt-3 line-clamp-2 text-sm font-bold text-white">{{ $announcement->title }}</h3>
                            @if ($announcement->content)
                                <p class="mt-2 line-clamp-2 text-xs leading-relaxed text-slate-400">{{ strip_tags($announcement->content) }}</p>
                            @endif
                            <span class="mt-auto pt-4 text-[11px] font-semibold text-brand-400">
                                {{ ($announcement->published_at ?: $announcement->created_at)?->format('d M Y') }}
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="surface-panel rounded-xl p-4 text-sm text-slate-400">Belum ada pengumuman aktif.</div>
            @endif
        </section>

        {{-- BROWSE BY YEAR AND SCORE --}}
        <section class="surface-panel min-w-0 rounded-2xl border border-white/5 p-5 shadow-elevated sm:p-6">
            <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="font-display text-xl font-bold tracking-tight text-white sm:text-2xl">Browse by Year and Score</h2>
                    <p class="text-xs text-slate-400">Jump straight to a filtered catalog</p>
                </div>
                <a href="{{ route('anime.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-white/10">
                    <span>All Filters</span>
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <div class="grid min-w-0 gap-5 lg:grid-cols-2">
                <div class="min-w-0">
                    <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Release Year</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($filters['years'] as $year)
                            <a href="{{ route('anime.index', ['year' => $year]) }}"
                               class="rounded-lg border border-white/5 bg-white/5 px-3 py-1.5 text-xs font-semibold text-slate-300 transition hover:border-brand-500/30 hover:bg-white/10 hover:text-white">
                                {{ $year }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="min-w-0">
                    <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Minimum Score</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($filters['ratings'] as $value => $label)
                            <a href="{{ route('anime.index', ['rating' => $value]) }}"
                               class="rounded-lg border border-white/5 bg-white/5 px-3 py-1.5 text-xs font-semibold text-slate-300 transition hover:border-brand-500/30 hover:bg-white/10 hover:text-white">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- CATALOG DISCOVERY HIGHLIGHTS --}}
        <section>
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h2 class="font-display text-2xl font-bold tracking-tight text-white">Explore Anime Collection</h2>
                    <p class="text-xs text-slate-400">Over {{ number_format($catalogTotal) }} titles available in the Fer6origami library</p>
                </div>
                <a href="{{ route('anime.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-white/10">
                    <span>Browse Full Catalog</span>
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                @foreach ($catalogHighlights->take(12) as $anime)
                    <x-anime-card :anime="$anime" />
                @endforeach
            </div>
        </section>
    </div>

    {{-- CAROUSEL CONTROLLER SCRIPT --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const carousel = document.getElementById('hero-carousel');
            if (!carousel) return;

            const totalSlides = parseInt(carousel.dataset.totalSlides || '1', 10);
            if (totalSlides <= 1) return;

            let currentSlide = 0;
            let autoPlayTimer = null;

            const slides = carousel.querySelectorAll('.hero-slide');
            const indicators = carousel.querySelectorAll('[data-slide-indicator]');
            const btnPrev = document.getElementById('hero-prev');
            const btnNext = document.getElementById('hero-next');

            function goToSlide(index) {
                currentSlide = (index + totalSlides) % totalSlides;
                carousel.dataset.activeSlide = currentSlide;

                slides.forEach((slide, i) => {
                    if (i === currentSlide) {
                        slide.classList.remove('opacity-0', 'pointer-events-none', 'z-0');
                        slide.classList.add('opacity-100', 'z-10');
                    } else {
                        slide.classList.remove('opacity-100', 'z-10');
                        slide.classList.add('opacity-0', 'pointer-events-none', 'z-0');
                    }
                });

                indicators.forEach((ind, i) => {
                    if (i === currentSlide) {
                        ind.classList.remove('w-2', 'bg-white/20');
                        ind.classList.add('w-8', 'bg-brand-500');
                    } else {
                        ind.classList.remove('w-8', 'bg-brand-500');
                        ind.classList.add('w-2', 'bg-white/20');
                    }
                });
            }

            function startAutoPlay() {
                stopAutoPlay();
                autoPlayTimer = setInterval(() => {
                    goToSlide(currentSlide + 1);
                }, 5500);
            }

            function stopAutoPlay() {
                if (autoPlayTimer) {
                    clearInterval(autoPlayTimer);
                    autoPlayTimer = null;
                }
            }

            btnPrev?.addEventListener('click', () => {
                goToSlide(currentSlide - 1);
                startAutoPlay();
            });

            btnNext?.addEventListener('click', () => {
                goToSlide(currentSlide + 1);
                startAutoPlay();
            });

            indicators.forEach((ind, i) => {
                ind.addEventListener('click', () => {
                    goToSlide(i);
                    startAutoPlay();
                });
            });

            carousel.addEventListener('mouseenter', stopAutoPlay);
            carousel.addEventListener('mouseleave', startAutoPlay);

            startAutoPlay();
        });
    </script>
@endsection
