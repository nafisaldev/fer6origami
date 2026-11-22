@extends('layouts.app')

@section('title', 'Watch ' . $episode->anime->title . ' Episode ' . $episode->episode_number . ' Online — Fer6origami')
@section('meta_title', $episode->anime->title . ' Ep ' . $episode->episode_number . ' — Fer6origami')
@section('meta_description', 'Stream ' . $episode->anime->title . ' Episode ' . $episode->episode_number . ' HD on Fer6origami. Choose the best server and watch smoothly.')
@section('meta_image', $episode->anime->poster_image ?? '')
@section('meta_type', 'video.episode')

@section('content')
    <div class="space-y-6">
        {{-- BREADCRUMB NAV --}}
        <nav class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('home') }}" class="transition hover:text-white">Home</a>
            <span>/</span>
            <a href="{{ route('anime.index') }}" class="transition hover:text-white">Catalog</a>
            <span>/</span>
            <a href="{{ route('anime.show', ['animeId' => $episode->anime->route_id]) }}" class="transition hover:text-white">{{ $episode->anime->title }}</a>
            <span>/</span>
            <span class="truncate text-slate-200">Episode {{ $episode->episode_number }}</span>
        </nav>

        {{-- MAIN STREAMING THEATER & PLAYLIST GRID --}}
        <section class="grid gap-8 lg:grid-cols-12">
            {{-- PLAYER & CONTROLS COLUMN (8 COLS) --}}
            <div class="min-w-0 space-y-5 lg:col-span-8">
                {{-- VIDEO FRAME --}}
                <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-black shadow-elevated">
                    @if (!empty($episode->stream_url))
                        @php
                            $isDirectVideo = preg_match('/\.(mp4|m3u8|webm)(\?.*)?$/i', $episode->stream_url);
                        @endphp
                        @if ($isDirectVideo)
                            <video
                                controls
                                preload="metadata"
                                class="aspect-video w-full bg-black object-contain"
                                poster="{{ $episode->anime->banner_image }}"
                            >
                                <source src="{{ $episode->stream_url }}" type="video/mp4">
                                Your browser does not support HTML5 video playback.
                            </video>
                        @else
                            <iframe
                                src="{{ $episode->stream_url }}"
                                class="aspect-video w-full bg-black"
                                allowfullscreen
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                referrerpolicy="strict-origin-when-cross-origin"
                            ></iframe>
                        @endif
                    @else
                        {{-- NO STREAM EMPTY STATE --}}
                        <div class="flex aspect-video w-full flex-col items-center justify-center gap-3 bg-[#0d0f17] p-6 text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/5 text-slate-400">
                                <svg class="h-6 w-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.723v6.554a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-white">Stream Not Available</h3>
                            <p class="max-w-md text-xs text-slate-400">Try selecting another server below or use the available download links.</p>
                        </div>
                    @endif
                </div>

                {{-- PLAYER CONTROL BAR (NEXT / PREV & POP-OUT) --}}
                <div class="surface-panel flex flex-wrap items-center justify-between gap-3 rounded-xl p-3">
                    <div class="flex items-center gap-2">
                        @if ($previousEpisode)
                            <a href="{{ route('watch.show', ['animeId' => $episode->anime->route_id, 'episodeId' => $previousEpisode->route_id]) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs font-semibold text-slate-200 transition hover:bg-white/10">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                <span>Eps {{ $previousEpisode->episode_number }}</span>
                            </a>
                        @endif

                        @if ($nextEpisode)
                            <a href="{{ route('watch.show', ['animeId' => $episode->anime->route_id, 'episodeId' => $nextEpisode->route_id]) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-bold text-white transition hover:bg-brand-600">
                                <span>Eps {{ $nextEpisode->episode_number }}</span>
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        @if (!empty($episode->stream_url))
                            <a
                                href="{{ $episode->stream_url }}"
                                target="_blank"
                                rel="noreferrer"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 px-3 py-1.5 text-xs font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                <span>Open in New Tab</span>
                            </a>
                        @endif

                        <button
                            type="button"
                            onclick="document.getElementById('report-modal').classList.remove('hidden')"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-rose-500/30 bg-rose-500/10 px-3 py-1.5 text-xs font-semibold text-rose-300 transition hover:bg-rose-500/20"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span>Report Issue</span>
                        </button>
                    </div>
                </div>

                {{-- SERVER & RESOLUTION SELECTOR --}}
                @php
                    $groupedOptions = $episode->stream_options->groupBy('quality');
                    $currentUrl = $episode->stream_url;
                    $currentLabel = $episode->stream_label;
                @endphp
                @if ($episode->stream_options->count())
                    <div class="surface-panel rounded-xl p-4">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2 border-b border-white/5 pb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Select Streaming Server</span>
                            <div class="flex flex-wrap items-center gap-1.5">
                                @if ($episode->stream_quality)
                                    <span class="rounded bg-brand-500/20 px-2 py-0.5 text-[10px] font-bold text-brand-400">
                                        Active: {{ $episode->stream_quality }} ({{ $episode->stream_label }})
                                    </span>
                                @endif
                                <span class="rounded border px-2 py-0.5 text-[10px] font-bold uppercase {{ $viewerVisual['comment_badge'] }}">
                                    {{ $viewerVisual['label'] }} · maks {{ $viewerVisual['max_quality_label'] }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @foreach ($groupedOptions as $quality => $servers)
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                    <span class="w-16 shrink-0 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                        {{ $quality ?: 'Default' }}
                                    </span>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($servers as $option)
                                            @php
                                                $allowed = (bool) ($option['allowed'] ?? true);
                                                $requiredTier = $option['required_tier'] ?? 'free';
                                                $isActive = ($option['url'] ?? '') === $currentUrl
                                                    || (($option['quality'] ?? '') === $episode->stream_quality && ($option['label'] ?? '') === $currentLabel);
                                                $isDesu = str_contains(strtolower($option['url'] ?? ''), 'desustream') || str_contains(strtolower($option['url'] ?? ''), 'odstream');
                                                $optionUrl = $allowed
                                                    ? route('watch.show', ['animeId' => $episode->anime->route_id, 'episodeId' => $episode->route_id, 'quality' => $option['quality'], 'server' => $option['label']])
                                                    : route('membership.pricing');
                                            @endphp
                                            <a
                                                href="{{ $optionUrl }}"
                                                @if (! $allowed) title="Kualitas ini membutuhkan paket {{ ucfirst($requiredTier) }}" @endif
                                                class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition
                                                    {{ $isActive
                                                        ? 'border-brand-500 bg-brand-500 text-white font-bold'
                                                        : (! $allowed
                                                            ? 'border-amber-500/20 bg-amber-500/5 text-amber-200 hover:bg-amber-500/10'
                                                            : 'border-white/10 bg-[#161924] text-slate-300 hover:border-white/20 hover:bg-white/10') }}"
                                            >
                                                {{ $option['label'] ?? 'Server' }}
                                                @if (! $allowed)
                                                    <span class="ml-1 text-[9px] font-extrabold uppercase text-amber-400">{{ ucfirst($requiredTier) }}</span>
                                                @endif
                                                @if ($isDesu)
                                                    <span class="text-[10px] text-amber-400" title="May be restricted by browser iframe policy">*</span>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- STREAM NOTICE --}}
                        @if (!empty($episode->stream_url) && (str_contains($episode->stream_url, 'desustream') || str_contains($episode->stream_url, 'odstream')))
                            <div class="mt-4 flex items-start gap-2.5 rounded-lg border border-amber-500/20 bg-amber-500/5 p-3 text-xs text-amber-300/90">
                                <span class="font-bold text-amber-400">Note:</span>
                                <p>If the video player does not load due to browser restrictions, click <strong>"Open in New Tab"</strong> above or select another streaming server.</p>
                            </div>
                        @endif
                    </div>
                @endif

                @if ($qualityLimited || $qualityBlocked)
                    <div class="rounded-lg border border-amber-500/20 bg-amber-500/5 p-3 text-xs text-amber-200">
                        @if ($qualityBlocked)
                            <p>Kualitas yang diminta belum bisa diputar dengan paket {{ $viewerVisual['label'] }}. Pilih server lain atau <a href="{{ route('membership.pricing') }}" class="font-bold text-amber-300 underline">lihat paket</a>.</p>
                        @else
                            <p>Kualitas dibatasi ke {{ $viewerVisual['max_quality_label'] }} untuk paket {{ $viewerVisual['label'] }}. <a href="{{ route('membership.pricing') }}" class="font-bold text-amber-300 underline">Upgrade paket</a> untuk kualitas lebih tinggi.</p>
                        @endif
                    </div>
                @endif

                {{-- EPISODE INFO & SYNOPSIS --}}
                <div class="surface-panel rounded-xl p-5">
                    <h1 class="font-display text-2xl font-bold tracking-tight text-white">
                        {{ $episode->anime->title }}
                    </h1>
                    <p class="mt-1 text-sm font-semibold text-brand-400">
                        Episode {{ $episode->episode_number }} — {{ $episode->title }}
                    </p>
                    <p class="mt-3 text-xs leading-relaxed text-slate-300">
                        {{ $episode->synopsis ?: 'Enjoy watching high quality anime streaming on Fer6origami.' }}
                    </p>
                </div>

                {{-- DOWNLOAD LINKS --}}
                @if ($episode->qualities->count())
                    <div class="surface-panel rounded-xl p-5">
                        <div class="mb-4 flex items-center justify-between border-b border-white/5 pb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Download Links</span>
                            <span class="text-xs text-slate-400">{{ $episode->qualities->count() }} Quality Formats</span>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($episode->qualities as $quality)
                                <div class="surface-card rounded-lg p-3">
                                    <div class="flex items-center justify-between">
                                        <span class="rounded bg-brand-500/20 px-2 py-0.5 text-xs font-bold text-brand-400">
                                            {{ $quality['quality'] ?? '-' }}
                                        </span>
                                        @if (!empty($quality['size']))
                                            <span class="text-xs text-slate-400">{{ $quality['size'] }}</span>
                                        @endif
                                    </div>

                                    <div class="mt-3 flex flex-wrap gap-1.5">
                                        @foreach (($quality['download_links'] ?? []) as $link)
                                            <a href="{{ $link['link'] }}" target="_blank" rel="noreferrer"
                                               class="rounded-md border border-white/10 bg-white/5 px-2.5 py-1 text-xs font-medium text-slate-200 transition hover:border-brand-500/30 hover:bg-white/10 hover:text-white">
                                                {{ $link['host'] }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- COMMENTS & DISCUSSION SECTION --}}
                <div class="surface-panel rounded-xl p-5">
                    <div class="mb-4 flex items-center justify-between border-b border-white/5 pb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Comments & Community Discussion</span>
                        <span class="text-xs text-slate-400">{{ isset($comments) ? $comments->count() : 0 }} Comments</span>
                    </div>

                    @auth
                        <form action="{{ route('comments.store') }}" method="POST" class="mb-6 space-y-3">
                            @csrf
                            @if (!empty($dbEpisode))
                                <input type="hidden" name="episode_id" value="{{ $dbEpisode->id }}">
                                <input type="hidden" name="anime_id" value="{{ $dbEpisode->anime_id }}">
                            @endif
                            <textarea
                                name="content"
                                rows="3"
                                required
                                placeholder="Share your thoughts about this episode..."
                                class="w-full rounded-xl border border-white/10 bg-[#11131b] p-3 text-xs text-white placeholder:text-slate-500 focus:border-brand-500 focus:outline-none"
                            ></textarea>
                            <div class="flex justify-end">
                                <button type="submit" class="rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white transition hover:bg-brand-600">
                                    Post Comment
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="mb-6 rounded-xl border border-white/5 bg-[#161924] p-4 text-center text-xs text-slate-400">
                            Please <a href="{{ route('login') }}" class="font-bold text-brand-400 hover:underline">Log In</a> to join the conversation and comment on this episode.
                        </div>
                    @endauth

                    <div class="space-y-3">
                        @if (isset($comments) && $comments->count())
                            @foreach ($comments as $comment)
                                @php
                                    $style = $commentStyles->get($comment->user_id) ?? $defaultCommentStyle;
                                @endphp
                                <div class="rounded-xl border p-3.5 {{ $style['comment_card'] }}">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex min-w-0 items-center gap-2">
                                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $style['comment_avatar'] }}">
                                                {{ strtoupper(substr($comment->user->name ?? 'User', 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex min-w-0 items-center gap-1.5">
                                                    <span class="truncate text-xs font-bold {{ $style['comment_name'] }}">{{ $comment->user->name ?? 'Community Member' }}</span>
                                                    @if ($style['tier'] !== 'free')
                                                        <span class="shrink-0 rounded border px-1.5 py-0.5 text-[9px] font-extrabold uppercase {{ $style['comment_badge'] }}">{{ $style['label'] }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <span class="shrink-0 text-[10px] text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="mt-2 text-xs text-slate-200 leading-relaxed">{{ $comment->content }}</p>
                                </div>
                            @endforeach
                        @else
                            <p class="py-4 text-center text-xs text-slate-500">No comments yet. Be the first to share your thoughts!</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- PLAYLIST & SERIES DETAILS COLUMN (4 COLS) --}}
            <div class="min-w-0 space-y-6 lg:col-span-4">
                {{-- SERIES SUMMARY CARD --}}
                <div class="surface-panel flex items-center gap-3.5 rounded-xl p-3.5">
                    <div class="relative aspect-[3/4] w-14 shrink-0 overflow-hidden rounded-lg bg-slate-900">
                        <img
                            src="{{ $episode->anime->poster_image }}"
                            alt="{{ $episode->anime->title }}"
                            loading="lazy"
                            decoding="async"
                            data-fallback-src="{{ asset('images/poster-placeholder.svg') }}"
                            class="h-full w-full object-cover"
                        >
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Series Info</span>
                        <h2 class="truncate text-sm font-bold text-white">{{ $episode->anime->title }}</h2>
                        <div class="mt-1 flex items-center gap-2 text-xs text-slate-400">
                            <span>{{ $episode->anime->studio ?: 'Anime' }}</span>
                            <span>•</span>
                            <a href="{{ route('anime.show', ['animeId' => $episode->anime->route_id]) }}" class="inline-flex items-center gap-1 font-semibold text-brand-400 hover:underline">
                                Series Details
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- EPISODE PLAYLIST --}}
                <div class="surface-panel rounded-xl p-4">
                    <div class="mb-3 flex items-center justify-between border-b border-white/5 pb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Episode List</span>
                        <span class="text-xs text-slate-400">{{ $episode->anime->episodes->count() }} total</span>
                    </div>

                    <div class="max-h-[500px] space-y-1.5 overflow-y-auto pr-1">
                        @foreach ($episode->anime->episodes as $item)
                            @php
                                $isCurrent = $item->id === $episode->id;
                            @endphp
                            <a
                                href="{{ route('watch.show', ['animeId' => $episode->anime->route_id, 'episodeId' => $item->route_id]) }}"
                                class="flex items-center justify-between rounded-lg px-3 py-2 text-xs transition
                                    {{ $isCurrent
                                        ? 'bg-brand-500 font-bold text-white'
                                        : 'bg-[#161924] text-slate-300 hover:bg-white/10 hover:text-white' }}"
                            >
                                <div class="flex items-center gap-2.5 truncate">
                                    <span class="shrink-0 {{ $isCurrent ? 'text-white' : 'text-slate-400' }}">
                                        Ep {{ $item->episode_number }}
                                    </span>
                                    <span class="truncate">{{ $item->title }}</span>
                                </div>
                                @if ($isCurrent)
                                    <span class="shrink-0 text-[10px] uppercase tracking-wider font-extrabold text-white">
                                        Playing
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- RECOMMENDATIONS --}}
                @if ($recommendations->count())
                    <div class="surface-panel rounded-xl p-4">
                        <span class="mb-3 block text-xs font-bold uppercase tracking-wider text-slate-400">Similar Recommendations</span>
                        <div class="space-y-2.5">
                            @foreach ($recommendations->take(4) as $anime)
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
                                        <h4 class="truncate text-xs font-bold text-white transition group-hover:text-brand-400">{{ $anime->title }}</h4>
                                        <p class="truncate text-[10px] text-slate-400">{{ $anime->studio ?: 'Anime' }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- COMMENTS & DISCUSSION SECTION --}}
                <div class="surface-panel rounded-xl p-5">
                    <div class="mb-4 flex items-center justify-between border-b border-white/5 pb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Comments & Community Discussion</span>
                        <span class="text-xs text-slate-400">{{ isset($comments) ? $comments->count() : 0 }} Comments</span>
                    </div>

                    @auth
                        <form action="{{ route('comments.store') }}" method="POST" class="mb-6 space-y-3">
                            @csrf
                            @if (!empty($dbEpisode))
                                <input type="hidden" name="episode_id" value="{{ $dbEpisode->id }}">
                                <input type="hidden" name="anime_id" value="{{ $dbEpisode->anime_id }}">
                            @endif
                            <textarea
                                name="content"
                                rows="3"
                                required
                                placeholder="Share your thoughts about this episode..."
                                class="w-full rounded-xl border border-white/10 bg-[#11131b] p-3 text-xs text-white placeholder:text-slate-500 focus:border-brand-500 focus:outline-none"
                            ></textarea>
                            <div class="flex justify-end">
                                <button type="submit" class="rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white transition hover:bg-brand-600">
                                    Post Comment
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="mb-6 rounded-xl border border-white/5 bg-[#161924] p-4 text-center text-xs text-slate-400">
                            Please <a href="{{ route('login') }}" class="font-bold text-brand-400 hover:underline">Log In</a> to join the conversation and comment on this episode.
                        </div>
                    @endauth

                    <div class="space-y-3">
                        @if (isset($comments) && $comments->count())
                            @foreach ($comments as $comment)
                                @php
                                    $style = $commentStyles->get($comment->user_id) ?? $defaultCommentStyle;
                                @endphp
                                <div class="rounded-xl border p-3.5 {{ $style['comment_card'] }}">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex min-w-0 items-center gap-2">
                                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $style['comment_avatar'] }}">
                                                {{ strtoupper(substr($comment->user->name ?? 'User', 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex min-w-0 items-center gap-1.5">
                                                    <span class="truncate text-xs font-bold {{ $style['comment_name'] }}">{{ $comment->user->name ?? 'Community Member' }}</span>
                                                    @if ($style['tier'] !== 'free')
                                                        <span class="shrink-0 rounded border px-1.5 py-0.5 text-[9px] font-extrabold uppercase {{ $style['comment_badge'] }}">{{ $style['label'] }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <span class="shrink-0 text-[10px] text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="mt-2 text-xs text-slate-200 leading-relaxed">{{ $comment->content }}</p>
                                </div>
                            @endforeach
                        @else
                            <p class="py-4 text-center text-xs text-slate-500">No comments yet. Be the first to share your thoughts!</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
    {{-- ISSUE REPORT MODAL --}}
    <div id="report-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm">
        <div class="surface-panel relative w-full max-w-md rounded-2xl border border-white/10 p-6 shadow-2xl">
            <div class="flex items-center justify-between border-b border-white/5 pb-3">
                <h3 class="font-display text-base font-bold text-white">Report Video / Stream Issue</h3>
                <button type="button" onclick="document.getElementById('report-modal').classList.add('hidden')" class="text-slate-400 hover:text-white" aria-label="Tutup"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6L6 18" /></svg></button>
            </div>

            <form action="{{ route('reports.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                @if (!empty($dbEpisode))
                    <input type="hidden" name="reportable_type" value="App\Models\Episode">
                    <input type="hidden" name="reportable_id" value="{{ $dbEpisode->id }}">
                @endif

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-300">Issue Type</label>
                    <select name="type" required class="w-full rounded-xl border border-white/10 bg-[#11131b] px-3 py-2 text-xs text-white focus:border-brand-500 focus:outline-none">
                        <option value="broken_link">Broken / Dead Video Link</option>
                        <option value="subtitle_issue">Subtitle Missing or Out of Sync</option>
                        <option value="inappropriate_content">Wrong Episode or Audio Glitch</option>
                        <option value="other">Other Issue</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-300">Description</label>
                    <textarea name="description" rows="3" required placeholder="Describe what went wrong so our admin team can fix it quickly..." class="w-full rounded-xl border border-white/10 bg-[#11131b] p-3 text-xs text-white placeholder:text-slate-500 focus:border-brand-500 focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('report-modal').classList.add('hidden')" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/5">
                        Cancel
                    </button>
                    <button type="submit" class="rounded-xl bg-brand-500 px-5 py-2 text-xs font-bold text-white transition hover:bg-brand-600">
                        Submit Report
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
