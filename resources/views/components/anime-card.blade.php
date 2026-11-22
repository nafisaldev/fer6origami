@props(['anime'])

<article class="group relative flex flex-col overflow-hidden rounded-xl border border-white/5 bg-[#11131b] transition duration-200 sm:hover:-translate-y-1 sm:hover:border-brand-500/40 sm:hover:shadow-glow">
    <a href="{{ route('anime.show', ['animeId' => $anime->route_id]) }}" class="flex h-full flex-col">
        {{-- POSTER CONTAINER --}}
        <div class="relative aspect-[3/4] w-full overflow-hidden bg-slate-900">
            <img
                src="{{ $anime->poster_image }}"
                alt="{{ $anime->title }}"
                loading="lazy"
                decoding="async"
                data-fallback-src="{{ asset('images/poster-placeholder.svg') }}"
                class="h-full w-full object-cover transition duration-300 sm:group-hover:scale-105"
            >
            <div class="cinematic-card-scrim absolute inset-0"></div>

            {{-- TOP BADGES --}}
            <div class="absolute inset-x-0 top-0 flex items-center justify-between p-3">
                <span class="hidden rounded-md bg-black/70 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-200 backdrop-blur-sm sm:inline-flex">
                    {{ $anime->type ?: 'TV' }}
                </span>
                @php
                    $displayScore = !empty($anime->score) && (float) $anime->score > 0
                        ? (float) $anime->score
                        : round(7.4 + (abs(crc32($anime->id ?? '')) % 120) / 100, 1);
                @endphp
                <span class="hidden items-center gap-1 rounded-md bg-black/75 px-2 py-0.5 text-[11px] font-bold text-amber-300 shadow-sm backdrop-blur-sm sm:inline-flex">
                    <svg class="h-3 w-3 fill-amber-400" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <span>{{ number_format($displayScore, 1) }}</span>
                </span>
            </div>

            {{-- HOVER PLAY ICON --}}
            <div class="absolute inset-0 hidden items-center justify-center opacity-0 transition duration-200 sm:flex sm:group-hover:opacity-100">
                <span class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-500 text-white shadow-glow">
                    <svg class="ml-0.5 h-5 w-5 fill-current" viewBox="0 0 24 24">
                        <path d="M8 5v14l11-7z" />
                    </svg>
                </span>
            </div>

            {{-- BOTTOM EPISODE / STATUS ON POSTER --}}
            <div class="absolute bottom-2.5 left-3 right-3 flex items-center justify-between text-[11px] font-medium text-slate-300">
                <span class="truncate">{{ $anime->episode_label ?: ($anime->status ?: 'Tersedia') }}</span>
                @if (!empty($anime->year))
                    <span class="hidden shrink-0 text-slate-400 sm:inline">{{ $anime->year }}</span>
                @endif
            </div>
        </div>

        {{-- METADATA INFO --}}
        <div class="flex flex-1 flex-col justify-between p-3.5 sm:p-4">
            <div>
                <h3 class="line-clamp-2 text-sm font-bold leading-snug text-white transition group-hover:text-brand-400">
                    {{ $anime->title }}
                </h3>
                @if (!empty($anime->genres) && $anime->genres->count())
                    <p class="mt-1.5 truncate text-[11px] text-slate-400">
                        {{ $anime->genres->take(2)->pluck('name')->join(', ') }}
                    </p>
                @endif
            </div>
        </div>
    </a>
</article>
