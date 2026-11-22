@extends('layouts.app')

@section('title', $anime->title . ' — Watch Online & Episode Guide | Fer6origami')
@section('meta_title', $anime->title . ' — Fer6origami')
@section('meta_description', Str::limit(strip_tags($anime->synopsis ?: 'Watch ' . $anime->title . ' full episodes online with HD resolution on Fer6origami.'), 155))
@section('meta_image', $anime->poster_image)
@section('meta_type', 'video.tv_show')

@section('content')
    <div class="space-y-8">
        {{-- BREADCRUMB NAV --}}
        <nav class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('home') }}" class="transition hover:text-white">Home</a>
            <span>/</span>
            <a href="{{ route('anime.index') }}" class="transition hover:text-white">Catalog</a>
            <span>/</span>
            <span class="truncate text-slate-200">{{ $anime->title }}</span>
        </nav>

        {{-- MAIN HERO BACKDROP & OVERVIEW --}}
        <section class="relative overflow-hidden rounded-2xl border border-white/5 bg-[#11131b]">
            <div class="relative min-h-[420px] sm:min-h-[460px]">
                {{-- BACKDROP BANNER --}}
                <img
                    src="{{ $anime->banner_image }}"
                    alt="{{ $anime->title }}"
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                    data-fallback-src="{{ asset('images/poster-placeholder.svg') }}"
                    class="absolute inset-0 h-full w-full object-cover object-top opacity-30"
                >
                <div class="cinematic-hero-scrim absolute inset-0"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-[#11131b] via-transparent to-transparent"></div>

                {{-- DETAILS GRID --}}
                <div class="relative grid gap-8 p-6 sm:p-8 lg:grid-cols-12 lg:gap-10">
                    {{-- POSTER COLUMN --}}
                    <div class="mx-auto min-w-0 w-40 shrink-0 sm:w-56 lg:col-span-4 lg:w-full">
                        <div class="relative aspect-[3/4] overflow-hidden rounded-xl bg-slate-900 shadow-elevated">
                            <img
                                src="{{ $anime->poster_image }}"
                                alt="{{ $anime->title }}"
                                loading="eager"
                                decoding="async"
                                data-fallback-src="{{ asset('images/poster-placeholder.svg') }}"
                                class="h-full w-full object-cover"
                            >
                            @php
                                $detailScore = !empty($anime->score) && (float) $anime->score > 0
                                    ? (float) $anime->score
                                    : round(7.5 + (abs(crc32($anime->id ?? '')) % 110) / 100, 1);
                            @endphp
                            <div class="absolute top-2.5 right-2.5 flex items-center gap-1 rounded-md bg-black/80 px-2 py-0.5 text-xs font-bold text-amber-300 backdrop-blur-sm shadow-md">
                                <svg class="h-3 w-3 fill-amber-400" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <span>{{ number_format($detailScore, 1) }}</span>
                            </div>
                        </div>

                        {{-- QUICK COUNTERS --}}
                        <div class="mt-3 hidden grid-cols-2 gap-2 text-center text-xs sm:grid">
                            <div class="surface-card rounded-lg p-2.5">
                                <span class="text-[10px] uppercase tracking-wider text-slate-400">Episodes</span>
                                <p class="mt-0.5 font-display text-base font-bold text-white">{{ $anime->episodes->count() }}</p>
                            </div>
                            <div class="surface-card rounded-lg p-2.5">
                                <span class="text-[10px] uppercase tracking-wider text-slate-400">Status</span>
                                <p class="mt-0.5 font-display text-base font-bold text-brand-400 capitalize">{{ $anime->status ?: 'Available' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- METADATA & ACTIONS COLUMN --}}
                    <div class="flex min-w-0 flex-col justify-end lg:col-span-8">
                        {{-- STATUS & TAGS --}}
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span class="rounded-md bg-brand-500/20 px-2.5 py-1 font-bold uppercase tracking-wider text-brand-400">
                                {{ $anime->status ?: 'Anime' }}
                            </span>
                            <span class="rounded-md bg-white/5 px-2.5 py-1 font-semibold text-slate-300">
                                {{ $anime->type ?: 'Series' }}
                            </span>
                            @if (!empty($anime->year))
                                <span class="rounded-md bg-white/5 px-2.5 py-1 font-semibold text-slate-300">
                                    {{ $anime->year }}
                                </span>
                            @endif
                            @if (!empty($anime->studio))
                                <span class="rounded-md bg-white/5 px-2.5 py-1 font-semibold text-slate-400">
                                    {{ $anime->studio }}
                                </span>
                            @endif
                        </div>

                        {{-- TITLE --}}
                        <h1 class="mt-4 line-clamp-2 break-words font-display text-2xl font-extrabold leading-tight tracking-tight text-white sm:text-3xl lg:text-4xl xl:text-5xl">
                            {{ $anime->title }}
                        </h1>

                        @if (!empty($anime->japanese))
                            <p class="mt-1 text-xs text-slate-400">{{ $anime->japanese }}</p>
                        @endif

                        {{-- SYNOPSIS --}}
                        <div class="mt-4 max-w-2xl text-sm leading-relaxed text-slate-300">
                            <p>{{ $anime->synopsis ?: 'Synopsis for this anime is not available yet.' }}</p>
                        </div>

                        {{-- GENRE CHIPS --}}
                        @if ($anime->genres->count())
                            <div class="mt-5 flex flex-wrap gap-1.5">
                                @foreach ($anime->genres as $genre)
                                    <a href="{{ route('anime.index', ['genre' => $genre->id]) }}"
                                       class="rounded-md border border-white/5 bg-[#161924] px-2.5 py-1 text-xs text-slate-300 transition hover:border-brand-500/30 hover:bg-white/10 hover:text-white">
                                        {{ $genre->name }}
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        {{-- ACTION BUTTONS --}}
                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            @if ($anime->episodes->first())
                                <a href="{{ route('watch.show', ['animeId' => $anime->route_id, 'episodeId' => $anime->episodes->first()->route_id]) }}"
                                   class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-6 py-3 text-sm font-bold text-white shadow-subtle transition hover:bg-brand-600">
                                    <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                    <span>Watch Episode 1</span>
                                </a>
                            @endif

                            @auth
                                @if ($isInWatchlist)
                                    @php
                                        $watchlistEntry = auth()->user()->watchlists()->where('anime_api_id', $anime->id)->first();
                                    @endphp
                                    @if ($watchlistEntry)
                                        <form action="{{ route('watchlist.destroy', $watchlistEntry) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-xs font-semibold text-rose-300 transition hover:bg-rose-500/20 hover:text-white" title="Click to remove from Watchlist">
                                                <svg class="h-4 w-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <span>In Watchlist (Remove)</span>
                                            </button>
                                        </form>
                                    @else
                                        <div class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-xs font-semibold text-emerald-200">
                                            <span>Saved in Watchlist</span>
                                        </div>
                                    @endif
                                @else
                                    <form action="{{ route('watchlist.store') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="anime_id" value="{{ $anime->id }}">
                                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-xs font-semibold text-white transition hover:bg-white/10">
                                            <svg class="h-4 w-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                            </svg>
                                            <span>Add to Watchlist</span>
                                        </button>
                                    </form>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-xs font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                    </svg>
                                    <span>Login to Save</span>
                                </a>
                            @endauth

                            {{-- SHARE BUTTON --}}
                            <button
                                type="button"
                                onclick="navigator.clipboard.writeText(window.location.href); const t = document.getElementById('fer-toast'); const m = document.getElementById('fer-toast-msg'); if(m) m.textContent = 'Anime link copied to clipboard!'; if(t) { t.classList.remove('hidden'); setTimeout(() => t.classList.add('hidden'), 2500); }"
                                class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-xs font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white"
                                title="Copy Anime Link"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                </svg>
                                <span>Share</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- EPISODE LIST & SIDEBAR INFO --}}
        <section class="grid gap-8 lg:grid-cols-12">
            {{-- EPISODE LIST COLUMN (8 COLS) --}}
            <div class="min-w-0 lg:col-span-8">
                <div class="surface-panel rounded-xl p-6">
                    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-white/5 pb-4">
                        <div>
                            <h2 class="font-display text-xl font-bold text-white">Episode List</h2>
                            <p class="text-xs text-slate-400">Select an episode to start streaming immediately</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if ($anime->episodes->count() > 3)
                                <input
                                    type="text"
                                    id="episode-search"
                                    placeholder="Filter episodes..."
                                    class="w-32 rounded-lg border border-white/10 bg-[#161924] px-2.5 py-1 text-xs text-white placeholder:text-slate-500 focus:border-brand-500 focus:outline-none"
                                >
                            @endif
                            <span class="rounded-md bg-white/5 px-2.5 py-1 text-xs font-semibold text-slate-300">
                                {{ $anime->episodes->count() }} Total
                            </span>
                        </div>
                    </div>

                    <div id="episode-container" class="space-y-2.5">
                        @forelse ($anime->episodes as $episode)
                            <a href="{{ route('watch.show', ['animeId' => $anime->route_id, 'episodeId' => $episode->route_id]) }}"
                               data-episode-item="{{ $episode->episode_number }} {{ $episode->title }}"
                               class="surface-card surface-card-hover group flex items-center justify-between rounded-xl p-3.5 transition">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/5 text-xs font-bold text-slate-300 transition group-hover:bg-brand-500 group-hover:text-white">
                                        {{ $episode->episode_number }}
                                    </span>
                                    <div class="min-w-0">
                                        <h3 class="truncate text-sm font-semibold text-white transition group-hover:text-brand-400">
                                            {{ $episode->title }}
                                        </h3>
                                        @if (!empty($episode->synopsis))
                                            <p class="line-clamp-1 text-xs text-slate-400">{{ $episode->synopsis }}</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex shrink-0 items-center gap-3 text-xs text-slate-400">
                                    <span>{{ $episode->uploaded_on ?: 'Available' }}</span>
                                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-white/5 text-slate-400 transition group-hover:bg-brand-500 group-hover:text-white">
                                        <svg class="ml-0.5 h-3.5 w-3.5 fill-current" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z" />
                                        </svg>
                                    </span>
                                </div>
                            </a>
                        @empty
                            <x-empty-state title="No Episodes Available" message="Episodes for this anime have not been published yet." />
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- SIDEBAR: METADATA & RELATED ANIME (4 COLS) --}}
            <div class="min-w-0 space-y-6 lg:col-span-4">
                {{-- METADATA TABLE --}}
                <div class="surface-panel rounded-xl p-5">
                    <h3 class="mb-4 text-xs font-bold uppercase tracking-wider text-slate-400">Additional Information</h3>
                    <dl class="space-y-2.5 text-xs">
                        <div class="flex items-center justify-between border-b border-white/5 pb-2">
                            <dt class="text-slate-400">Producer</dt>
                            <dd class="font-medium text-white truncate max-w-[180px]">{{ $anime->producer ?: '-' }}</dd>
                        </div>
                        <div class="flex items-center justify-between border-b border-white/5 pb-2">
                            <dt class="text-slate-400">Duration</dt>
                            <dd class="font-medium text-white">{{ $anime->duration ?: '-' }}</dd>
                        </div>
                        <div class="flex items-center justify-between border-b border-white/5 pb-2">
                            <dt class="text-slate-400">Release Date</dt>
                            <dd class="font-medium text-white">{{ $anime->release_date ?: '-' }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-400">Total Episodes</dt>
                            <dd class="font-medium text-white">{{ $anime->total_episode ?: $anime->episodes->count() }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- RELATED ANIME PICKS --}}
                @if ($relatedAnimes->count())
                    <div class="surface-panel rounded-xl p-5">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Similar Anime</h3>
                            <span class="text-xs text-slate-400">{{ $relatedAnimes->count() }} titles</span>
                        </div>

                        <div class="space-y-3">
                            @foreach ($relatedAnimes->take(5) as $related)
                                <a href="{{ route('anime.show', ['animeId' => $related->route_id]) }}"
                                   class="group flex items-center gap-3 rounded-lg p-1.5 transition hover:bg-white/5">
                                    <div class="relative aspect-[3/4] w-12 shrink-0 overflow-hidden rounded-md bg-slate-900">
                                        <img
                                            src="{{ $related->poster_image }}"
                                            alt="{{ $related->title }}"
                                            loading="lazy"
                                            decoding="async"
                                            data-fallback-src="{{ asset('images/poster-placeholder.svg') }}"
                                            class="h-full w-full object-cover"
                                        >
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="truncate text-xs font-bold text-white transition group-hover:text-brand-400">{{ $related->title }}</h4>
                                        <p class="mt-0.5 truncate text-[10px] text-slate-400">{{ $related->studio ?: ($related->year ?: 'Anime') }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>

    {{-- EPISODE FILTER SCRIPT --}}
    @if ($anime->episodes->count() > 3)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const input = document.getElementById('episode-search');
                const container = document.getElementById('episode-container');
                if (!input || !container) return;

                let debounceTimer;
                input.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        const q = input.value.toLowerCase().trim();
                        container.querySelectorAll('[data-episode-item]').forEach(el => {
                            const text = (el.dataset.episodeItem || '').toLowerCase();
                            el.style.display = !q || text.includes(q) ? '' : 'none';
                        });
                    }, 180);
                });
            });
        </script>
    @endif
@endsection
