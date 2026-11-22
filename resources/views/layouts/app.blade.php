<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $siteName = \App\Models\WebsiteSetting::get('site_name', 'Fer6origami');
            $defaultMetaTitle = \App\Models\WebsiteSetting::get('meta_title', 'Fer6origami — Anime Streaming & Catalog Platform');
            $defaultMetaDesc = \App\Models\WebsiteSetting::get('meta_description', 'Fer6origami is a modern anime streaming platform with a comprehensive catalog, fast playback, latest episode updates, and a cinematic interface.');
            $defaultMetaKeywords = \App\Models\WebsiteSetting::get('meta_keywords', 'anime streaming, watch anime, streaming anime, catalog anime, latest episodes');
            $siteFavicon = \App\Models\WebsiteSetting::get('site_favicon');
            $customPrimaryColor = \App\Models\WebsiteSetting::get('theme_primary_color');
            $siteThemeColor = \App\Models\WebsiteSetting::get('theme_background_color', '#090a0f');
        @endphp
        <title>@yield('title', $defaultMetaTitle)</title>
        <meta name="description" content="@yield('meta_description', $defaultMetaDesc)">
        <meta name="keywords" content="@yield('meta_keywords', $defaultMetaKeywords)">
        <meta name="theme-color" content="{{ $siteThemeColor }}">
        <link rel="canonical" href="{{ url()->current() }}">

        {{-- OPEN GRAPH / FACEBOOK / WHATSAPP / DISCORD --}}
        <meta property="og:type" content="@yield('meta_type', 'website')">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:title" content="@yield('meta_title', $defaultMetaTitle)">
        <meta property="og:description" content="@yield('meta_description', $defaultMetaDesc)">
        <meta property="og:image" content="@yield('meta_image', asset('images/emblem.png'))">

        {{-- TWITTER CARDS --}}
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="@yield('meta_title', $defaultMetaTitle)">
        <meta name="twitter:description" content="@yield('meta_description', $defaultMetaDesc)">
        <meta name="twitter:image" content="@yield('meta_image', asset('images/emblem.png'))">

        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
        <link rel="icon" type="image/png" href="{{ asset('images/emblem.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/emblem.png') }}">
        <link rel="manifest" href="/manifest.webmanifest">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="preconnect" href="https://placehold.co" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">

        @php
            $siteFavicon = \App\Models\WebsiteSetting::get('site_favicon');
            $customPrimaryColor = \App\Models\WebsiteSetting::get('theme_primary_color');
        @endphp
        @if ($siteFavicon)
            <link rel="icon" href="{{ Storage::disk('public')->url($siteFavicon) }}">
        @endif
        @if ($customPrimaryColor)
            <style>
                :root { --brand-color-custom: {{ $customPrimaryColor }}; }
            </style>
        @endif

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#090a0f] font-sans text-slate-100 antialiased selection:bg-brand-500 selection:text-white">
        <div class="relative flex min-h-screen flex-col">
            @include('layouts.partials.navbar')

            @php
                $pinnedAnnouncement = \App\Models\Announcement::active()->where('is_pinned', true)->latest()->first();
                $popupAnnouncement = \App\Models\Announcement::active()->where('is_popup', true)->latest()->first();
            @endphp

            {{-- LIVE PINNED ANNOUNCEMENT FROM ADMIN --}}
            @if (!request()->routeIs('admin.*') && $pinnedAnnouncement)
                <div id="pinned-announcement" class="bg-gradient-to-r from-brand-600 to-brand-500 border-b border-brand-400/30 px-4 py-2.5 text-xs font-semibold text-white shadow-md">
                    <div class="mx-auto flex max-w-7xl items-center justify-between gap-3">
                        <a href="{{ route('announcements.show', $pinnedAnnouncement->slug) }}" class="flex items-center gap-2.5 truncate transition hover:opacity-90">
                            <span class="rounded bg-black/40 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-amber-300">
                                {{ $pinnedAnnouncement->category }}
                            </span>
                            <span class="font-bold text-white">{{ $pinnedAnnouncement->title }}:</span>
                            <span class="truncate text-white/95">{{ Str::limit(strip_tags($pinnedAnnouncement->content), 140) }}</span>
                            <span class="text-[11px] underline text-amber-200">Read &rarr;</span>
                        </a>
                        <button type="button" onclick="document.getElementById('pinned-announcement').style.display='none'" class="rounded px-2 py-0.5 text-white/80 transition hover:bg-black/20 hover:text-white" aria-label="Dismiss announcement">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6L6 18" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            <main class="relative z-10 flex-1">
                @if (request()->routeIs('admin.*'))
                    <div class="mx-auto flex w-full max-w-[1600px] flex-col gap-6 px-4 py-6 sm:px-6 lg:flex-row lg:px-8">
                        @include('admin.partials.sidebar')

                        <div class="min-w-0 flex-1">
                            <x-flash-message />
                            @yield('content')
                        </div>
                    </div>
                @else
                    <div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        <x-flash-message />
                        @yield('content')
                    </div>
                @endif
            </main>

            @include('layouts.partials.footer')

            {{-- SLEEK MOBILE BOTTOM NAVIGATION BAR --}}
            <nav class="fixed inset-x-0 bottom-0 z-40 flex items-center justify-around border-t border-white/10 bg-[#090a0f]/95 px-2 py-2 backdrop-blur-md sm:hidden">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 py-1 text-[10px] font-semibold {{ request()->routeIs('home') ? 'text-brand-400 font-bold' : 'text-slate-400 hover:text-white' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Home</span>
                </a>
                <a href="{{ route('anime.index') }}" class="flex flex-col items-center gap-1 py-1 text-[10px] font-semibold {{ request()->routeIs('anime.index') && !request('status') ? 'text-brand-400 font-bold' : 'text-slate-400 hover:text-white' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    <span>Catalog</span>
                </a>
                <a href="{{ route('anime.index', ['status' => 'ongoing']) }}" class="flex flex-col items-center gap-1 py-1 text-[10px] font-semibold {{ request('status') === 'ongoing' ? 'text-brand-400 font-bold' : 'text-slate-400 hover:text-white' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Ongoing</span>
                </a>
                @auth
                    <a href="{{ route('watchlist.index') }}" class="flex flex-col items-center gap-1 py-1 text-[10px] font-semibold {{ request()->routeIs('watchlist.*') ? 'text-brand-400 font-bold' : 'text-slate-400 hover:text-white' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                        <span>Watchlist</span>
                    </a>
                    <a href="{{ route('profile.show') }}" class="flex flex-col items-center gap-1 py-1 text-[10px] font-semibold {{ request()->routeIs('profile.*') ? 'text-brand-400 font-bold' : 'text-slate-400 hover:text-white' }}">
                        <img src="{{ auth()->user()->avatar_url }}" alt="" class="h-5 w-5 rounded-md object-cover ring-1 {{ request()->routeIs('profile.*') ? 'ring-brand-400' : 'ring-white/20' }}">
                        <span>Profil</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="flex flex-col items-center gap-1 py-1 text-[10px] font-semibold {{ request()->routeIs('login') ? 'text-brand-400 font-bold' : 'text-slate-400 hover:text-white' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Login</span>
                    </a>
                @endauth
            </nav>
        </div>

        {{-- DMCA & LEGAL DISCLAIMER MODAL --}}
        <div id="dmca-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm">
            <div class="surface-panel relative w-full max-w-xl rounded-2xl border border-white/10 p-6 shadow-elevated">
                <div class="flex items-center justify-between border-b border-white/10 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-500/20 text-brand-400 text-xs font-bold">§</span>
                        <h3 class="font-display text-base font-bold text-white">DMCA & Copyright Policy</h3>
                    </div>
                    <button onclick="document.getElementById('dmca-modal').classList.add('hidden')" class="rounded-lg p-1 text-slate-400 hover:bg-white/10 hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="mt-4 space-y-3 text-xs leading-relaxed text-slate-300">
                    <p><strong>Fer6origami</strong> is an anime discovery and catalog platform that aggregates media links from freely available third-party sources on the internet.</p>
                    <p>We <strong>do not host, store, or upload</strong> any video files on our own servers. All streaming contents are embedded directly from external third-party services.</p>
                    <p>All copyrights, trademarks, and intellectual property belong strictly to their respective creators, animation studios, and authorized license holders.</p>
                    <p>If you are a copyright owner and wish to request removal of links indexing your work, please contact us with verifiable proof of copyright ownership.</p>
                </div>
                <div class="mt-6 flex justify-end">
                    <button onclick="document.getElementById('dmca-modal').classList.add('hidden')" class="rounded-xl bg-brand-500 px-5 py-2 text-xs font-bold text-white transition hover:bg-brand-600">
                        I Understand
                    </button>
                </div>
            </div>
        </div>

        {{-- LIVE POPUP ANNOUNCEMENT FROM ADMIN --}}
        @if (!request()->routeIs('admin.*') && !empty($popupAnnouncement))
            <div id="popup-announcement-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm">
                <div class="surface-panel relative w-full max-w-lg rounded-2xl border border-white/10 p-6 shadow-2xl">
                    @if ($popupAnnouncement->banner_image)
                        <div class="relative -mx-6 -mt-6 mb-4 aspect-video overflow-hidden rounded-t-2xl">
                            <img src="{{ $popupAnnouncement->banner_image }}" alt="{{ $popupAnnouncement->title }}" class="h-full w-full object-cover">
                        </div>
                    @endif
                    <div class="flex items-center gap-2">
                        <span class="rounded bg-brand-500/20 px-2.5 py-0.5 text-xs font-bold text-brand-400 uppercase">
                            {{ $popupAnnouncement->category }}
                        </span>
                        <span class="text-xs text-slate-400">{{ $popupAnnouncement->published_at?->format('M d, Y') ?? 'Announcement' }}</span>
                    </div>
                    <h3 class="mt-2 font-display text-xl font-bold text-white">{{ $popupAnnouncement->title }}</h3>
                    <div class="mt-3 max-h-60 overflow-y-auto text-xs leading-relaxed text-slate-300">
                        {!! nl2br(e($popupAnnouncement->content)) !!}
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="button" onclick="document.getElementById('popup-announcement-modal').style.display='none'" class="rounded-xl bg-brand-500 px-5 py-2 text-xs font-bold text-white transition hover:bg-brand-600">
                            Close & Continue
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- SCROLL TO TOP --}}
        <button
            id="scroll-top-btn"
            class="fixed bottom-16 right-4 z-40 hidden h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-[#161924] text-slate-300 shadow-elevated transition hover:bg-brand-500 hover:text-white sm:bottom-6"
            aria-label="Scroll to top"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
            </svg>
        </button>

        {{-- TOAST NOTIFICATION --}}
        <div id="fer-toast" class="fixed bottom-20 right-4 z-50 hidden rounded-xl border border-white/10 bg-[#161924] px-4 py-3 text-xs font-semibold text-white shadow-elevated transition sm:bottom-6 sm:right-6">
            <span id="fer-toast-msg">Link copied to clipboard!</span>
        </div>

        <script>
            // Clean dropdown & menu toggles
            document.querySelectorAll('[data-menu-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const target = document.getElementById(button.dataset.menuToggle);
                    if (!target) return;
                    target.classList.toggle('hidden');
                });
            });

            // Resilient image fallback
            document.querySelectorAll('img[data-fallback-src]').forEach((image) => {
                const useFallback = () => {
                    const fallback = image.dataset.fallbackSrc;
                    if (!fallback || image.src === fallback) return;
                    image.src = fallback;
                };

                image.addEventListener('error', useFallback, { once: true });
                if (image.complete && image.naturalWidth === 0) {
                    useFallback();
                }
            });

            // Scroll-to-top visibility
            (() => {
                const btn = document.getElementById('scroll-top-btn');
                if (!btn) return;
                let ticking = false;
                window.addEventListener('scroll', () => {
                    if (!ticking) {
                        window.requestAnimationFrame(() => {
                            btn.classList.toggle('hidden', window.scrollY < 400);
                            btn.classList.toggle('flex', window.scrollY >= 400);
                            ticking = false;
                        });
                        ticking = true;
                    }
                });
                btn.addEventListener('click', () => {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            })();
        </script>
    </body>
</html>
