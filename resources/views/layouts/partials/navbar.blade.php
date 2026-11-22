@php
    $siteName = \App\Models\WebsiteSetting::get('site_name', 'Fer6origami');
    $siteTagline = \App\Models\WebsiteSetting::get('site_tagline', 'Streaming Atelier');
    $siteLogo = \App\Models\WebsiteSetting::get('site_logo');
    $logoUrl = $siteLogo ? Storage::disk('public')->url($siteLogo) : asset('images/emblem.png');

    $unreadNotifications = auth()->check()
        ? \App\Models\BroadcastNotification::query()
            ->where(fn ($q) => $q->whereNull('target_role')->orWhere('target_role', auth()->user()->role ?? 'user'))
            ->whereDoesntHave('reads', fn ($r) => $r->where('user_id', auth()->id()))
            ->latest()
            ->take(5)
            ->get()
        : collect();

    $membershipTier = 'free';
    $membershipBadgeClass = 'border-white/10 bg-white/5 text-slate-300';

    if (auth()->check()) {
        $navMembership = auth()->user()->membership;
        $membershipTier = $navMembership?->plan?->slug ?? 'free';

        if ($membershipTier === 'vip') {
            $membershipBadgeClass = 'border-amber-500/40 bg-amber-500/15 text-amber-300';
        } elseif ($membershipTier === 'premium') {
            $membershipBadgeClass = 'border-brand-500/40 bg-brand-500/15 text-brand-300';
        }
    }
@endphp

<header class="sticky top-0 z-40 border-b border-white/5 bg-[#090a0f]/90 backdrop-blur-md">
    <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
        {{-- BRAND LOGO --}}
        <a href="{{ route('home') }}" class="group flex shrink-0 items-center gap-2 sm:gap-3">
            <img src="{{ $logoUrl }}" alt="{{ $siteName }} Emblem" class="h-9 w-9 rounded-full object-cover shadow-glow transition group-hover:scale-105 sm:h-10 sm:w-10">
            <div class="flex min-w-0 flex-col">
                <div class="flex items-center gap-1.5">
                    <span class="truncate font-display text-base font-extrabold tracking-tight text-white sm:text-lg">{{ $siteName }}</span>
                </div>
                <span class="hidden text-[10px] font-medium uppercase tracking-wider text-slate-400 2xl:block">{{ $siteTagline }}</span>
            </div>
        </a>

        {{-- DESKTOP MAIN NAVIGATION --}}
        <nav class="hidden items-center gap-1 text-sm font-semibold text-slate-300 lg:flex">
            <a href="{{ route('home') }}" class="rounded-lg px-2 py-2 transition 2xl:px-3 {{ request()->routeIs('home') ? 'bg-white/10 text-white font-bold' : 'hover:bg-white/5 hover:text-white' }}">
                Home
            </a>
            <a href="{{ route('anime.index') }}" class="rounded-lg px-2 py-2 transition 2xl:px-3 {{ request()->routeIs('anime.index') && !request('status') ? 'bg-white/10 text-white font-bold' : 'hover:bg-white/5 hover:text-white' }}">
                Catalog
            </a>
            <a href="{{ route('anime.index', ['status' => 'ongoing']) }}" class="rounded-lg px-2 py-2 transition 2xl:px-3 {{ request('status') === 'ongoing' ? 'bg-white/10 text-white font-bold' : 'hover:bg-white/5 hover:text-white' }}">
                Ongoing
            </a>
            <a href="{{ route('anime.index', ['status' => 'completed']) }}" class="rounded-lg px-2 py-2 transition 2xl:px-3 {{ request('status') === 'completed' ? 'bg-white/10 text-white font-bold' : 'hover:bg-white/5 hover:text-white' }}">
                Completed
            </a>
            <a href="{{ route('membership.pricing') }}" class="rounded-lg px-2 py-2 transition 2xl:px-3 {{ request()->routeIs('membership.*') ? 'bg-white/10 text-white font-bold' : 'hover:bg-white/5 hover:text-white' }}">
                Membership
            </a>

            {{-- GENRE FLYOUT BUTTON --}}
            <div class="relative">
                <button
                    type="button"
                    data-menu-toggle="genre-dropdown"
                    class="inline-flex items-center gap-1.5 rounded-lg px-2 py-2 text-slate-300 transition hover:bg-white/5 hover:text-white 2xl:px-3"
                >
                    <span>Genres</span>
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                {{-- GENRE POPUP PANEL --}}
                <div id="genre-dropdown" class="surface-panel absolute left-0 top-full mt-2 hidden w-80 rounded-xl p-4 shadow-elevated">
                    <div class="mb-3 flex items-center justify-between border-b border-white/5 pb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Select Genre</span>
                        <a href="{{ route('anime.index') }}" class="text-xs font-semibold text-brand-400 hover:underline">All</a>
                    </div>
                    <div class="grid max-h-72 grid-cols-2 gap-1.5 overflow-y-auto pr-1 text-xs">
                        @foreach (($navGenres ?? collect()) as $genre)
                            <a href="{{ route('anime.index', ['genre' => $genre->id]) }}" class="rounded-lg px-2.5 py-1.5 text-slate-300 transition hover:bg-white/10 hover:text-white">
                                {{ $genre->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </nav>

        {{-- SEARCH & USER ACTIONS --}}
        <div class="flex items-center gap-2 sm:gap-3">
            {{-- MOBILE SEARCH BUTTON (opens search overlay) --}}
            <button
                type="button"
                id="mobile-search-trigger"
                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-white/10 bg-[#161924] text-slate-300 transition hover:bg-white/10 xl:hidden"
                aria-label="Search anime"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>

            {{-- DESKTOP SEARCH FORM --}}
            <form action="{{ route('anime.index') }}" method="GET" class="relative hidden xl:block">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search anime title..."
                    class="w-56 rounded-xl border border-white/10 bg-[#161924] py-2 pl-9 pr-3 text-xs text-white placeholder:text-slate-500 transition focus:w-64 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 lg:w-64 lg:focus:w-72"
                >
                <svg class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </form>

            @auth
                {{-- NOTIFICATIONS BELL --}}
                <div class="relative">
                    <button
                        type="button"
                        data-menu-toggle="notifications-dropdown"
                        class="relative inline-flex h-9 w-9 items-center justify-center rounded-xl border border-white/10 bg-[#161924] text-slate-300 transition hover:bg-white/10 hover:text-white"
                        aria-label="Notifications"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        @if ($unreadNotifications->isNotEmpty())
                            <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-brand-500 text-[9px] font-bold text-white">
                                {{ $unreadNotifications->count() }}
                            </span>
                        @endif
                    </button>

                    <div id="notifications-dropdown" class="surface-panel absolute right-0 top-full mt-2 hidden w-80 rounded-xl p-3 shadow-2xl z-50">
                        <div class="mb-2 flex items-center justify-between border-b border-white/5 pb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Notifications</span>
                            @if ($unreadNotifications->isNotEmpty())
                                <form action="{{ route('notifications.read-all') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-[10px] text-brand-400 font-semibold hover:underline">Mark all read</button>
                                </form>
                            @else
                                <span class="text-[10px] text-slate-500">Up to date</span>
                            @endif
                        </div>
                        <div class="max-h-64 space-y-2 overflow-y-auto">
                            @forelse ($unreadNotifications as $notif)
                                <div class="rounded-lg border border-white/5 bg-[#161924] p-2.5 text-xs">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-white">{{ $notif->title }}</span>
                                        <span class="text-[10px] text-slate-400">{{ $notif->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="mt-1 text-[11px] text-slate-300 leading-snug">{{ $notif->message }}</p>
                                </div>
                            @empty
                                <p class="py-4 text-center text-xs text-slate-400">No new notifications</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- PROFILE SHORTCUT --}}
                <a href="{{ route('profile.show') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-[#161924] py-1.5 pl-1.5 pr-1.5 text-xs font-semibold text-slate-200 transition hover:border-brand-500/40 hover:bg-white/10 sm:pr-3" aria-label="Profil saya">
                    <img src="{{ auth()->user()->avatar_url }}" alt="" class="h-6 w-6 rounded-lg object-cover ring-1 ring-white/10">
                    <span class="hidden text-left leading-tight sm:block">
                        <span class="block font-bold">Profil</span>
                        <span class="hidden text-[10px] font-extrabold text-brand-400 2xl:block">Lv. {{ auth()->user()->level }}</span>
                        <span class="mt-0.5 hidden rounded border px-1.5 py-0.5 text-[9px] font-extrabold uppercase leading-none 2xl:inline-block {{ $membershipBadgeClass }}">{{ ucfirst($membershipTier) }}</span>
                    </span>
                </a>

                <a href="{{ route('watchlist.index') }}" class="hidden items-center gap-1.5 rounded-xl border border-white/10 bg-[#161924] px-2.5 py-2 text-xs font-semibold text-slate-200 transition hover:border-white/20 hover:bg-white/10 sm:inline-flex 2xl:px-3.5" aria-label="Watchlist">
                    <svg class="h-3.5 w-3.5 text-brand-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M5 4a2 2 0 012-2h10a2 2 0 012 2v18l-7-4-7 4V4z"/>
                    </svg>
                    <span class="hidden 2xl:inline">Watchlist</span>
                </a>

                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="hidden rounded-xl border border-brand-500/30 bg-brand-500/10 px-2.5 py-2 text-xs font-bold text-brand-300 transition hover:bg-brand-500/20 sm:inline-flex 2xl:px-3" aria-label="Admin Panel">
                        <span class="hidden 2xl:inline">Admin</span>
                        <svg class="h-3.5 w-3.5 2xl:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </a>
                @endif

                <form action="{{ route('logout') }}" method="POST" class="hidden sm:inline-block">
                    @csrf
                    <button type="submit" class="inline-flex rounded-xl bg-white/10 px-2.5 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/20 2xl:px-3" aria-label="Logout">
                        <span class="hidden 2xl:inline">Logout</span>
                        <svg class="h-3.5 w-3.5 2xl:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="hidden rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-300 transition hover:text-white sm:inline-flex">
                    Login
                </a>
                <a href="{{ route('register') }}" class="hidden rounded-xl bg-brand-500 px-3.5 py-2 text-xs font-bold text-white shadow-subtle transition hover:bg-brand-600 sm:inline-flex">
                    Register
                </a>
            @endauth

            {{-- MOBILE TOGGLE BUTTON --}}
            <button
                type="button"
                data-menu-toggle="mobile-drawer"
                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-white/10 bg-[#161924] text-slate-300 transition hover:bg-white/10 xl:hidden"
                aria-label="Menu"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
            </button>
        </div>
    </div>

    {{-- MOBILE MENU DRAWER --}}
    <div id="mobile-drawer" class="surface-panel hidden border-t border-white/5 px-4 py-4 xl:hidden">
        <form action="{{ route('anime.index') }}" method="GET" class="relative mb-3">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search anime..."
                class="w-full rounded-xl border border-white/10 bg-[#161924] py-2.5 pl-9 pr-3 text-sm text-white placeholder:text-slate-500 focus:border-brand-500 focus:outline-none"
            >
            <svg class="pointer-events-none absolute left-3 top-3 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </form>

        <nav class="grid gap-1 border-b border-white/5 pb-3 text-sm font-semibold text-slate-300">
            <a href="{{ route('home') }}" class="rounded-lg px-3 py-2 hover:bg-white/5 hover:text-white">Home</a>
            <a href="{{ route('anime.index') }}" class="rounded-lg px-3 py-2 hover:bg-white/5 hover:text-white">Anime Catalog</a>
            <a href="{{ route('anime.index', ['status' => 'ongoing']) }}" class="rounded-lg px-3 py-2 hover:bg-white/5 hover:text-white">Ongoing Anime</a>
            <a href="{{ route('anime.index', ['status' => 'completed']) }}" class="rounded-lg px-3 py-2 hover:bg-white/5 hover:text-white">Completed Anime</a>
        </nav>

        {{-- MOBILE GENRES --}}
        <div class="py-3">
            <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Popular Genres</p>
            <div class="flex flex-wrap gap-1.5">
                @foreach (($navGenres ?? collect())->take(12) as $genre)
                    <a href="{{ route('anime.index', ['genre' => $genre->id]) }}" class="rounded-md border border-white/5 bg-white/5 px-2.5 py-1 text-xs text-slate-300 hover:bg-white/10 hover:text-white">
                        {{ $genre->name }}
                    </a>
                @endforeach
            </div>
        </div>

        @auth
            <div class="border-t border-white/5 pt-3">
                <a href="{{ route('profile.show') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold text-slate-300 hover:bg-white/5">
                    <img src="{{ auth()->user()->avatar_url }}" alt="" class="h-8 w-8 rounded-lg object-cover ring-1 ring-white/10">
                    <span>
                        Profil Saya
                        <span class="block text-[10px] font-bold text-brand-400">Level {{ auth()->user()->level }} · Lihat streak & misi</span>
                    </span>
                </a>
                <a href="{{ route('watchlist.index') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-300 hover:bg-white/5">My Watchlist</a>
                <div class="flex items-center justify-between gap-2 rounded-lg px-3 py-2">
                    <a href="{{ route('membership.show') }}" class="text-sm font-semibold text-slate-300 hover:text-white">Membership Saya</a>
                    <span class="rounded-full border px-2 py-0.5 text-[10px] font-extrabold uppercase {{ $membershipBadgeClass }}">{{ ucfirst($membershipTier) }}</span>
                </div>
                <a href="{{ route('membership.pricing') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-300 hover:bg-white/5">Lihat Paket Membership</a>
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold text-brand-400 hover:bg-white/5">Admin Dashboard</a>
                @endif
                <form action="{{ route('logout') }}" method="POST" class="mt-1">
                    @csrf
                    <button type="submit" class="w-full rounded-lg px-3 py-2 text-left text-sm font-semibold text-rose-400 hover:bg-white/5">Logout</button>
                </form>
            </div>
        @else
            <div class="grid grid-cols-2 gap-2 border-t border-white/5 pt-3">
                <a href="{{ route('login') }}" class="rounded-xl border border-white/10 px-4 py-2 text-center text-sm font-semibold text-slate-200">Login</a>
                <a href="{{ route('register') }}" class="rounded-xl bg-brand-500 px-4 py-2 text-center text-sm font-bold text-white">Register</a>
            </div>
        @endauth
    </div>

    {{-- MOBILE SEARCH OVERLAY --}}
    <div id="mobile-search-overlay" class="fixed inset-0 z-50 hidden bg-black/80 backdrop-blur-sm xl:hidden">
        <div class="mx-auto max-w-md px-4 pt-20">
            <form action="{{ route('anime.index') }}" method="GET" class="relative">
                <input
                    type="text"
                    name="search"
                    id="mobile-search-input"
                    placeholder="Type anime title..."
                    autofocus
                    class="w-full rounded-2xl border border-white/15 bg-[#161924] py-3.5 pl-11 pr-12 text-sm text-white placeholder:text-slate-500 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                >
                <svg class="pointer-events-none absolute left-3.5 top-3.5 h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <button
                    type="button"
                    onclick="document.getElementById('mobile-search-overlay').classList.add('hidden')"
                    class="absolute right-3 top-2.5 rounded-lg p-1 text-slate-400 hover:text-white"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </form>
            <p class="mt-3 text-center text-xs text-slate-500">Press Enter to search</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const trigger = document.getElementById('mobile-search-trigger');
            const overlay = document.getElementById('mobile-search-overlay');
            const input = document.getElementById('mobile-search-input');
            if (!trigger || !overlay) return;

            trigger.addEventListener('click', () => {
                overlay.classList.remove('hidden');
                if (input) setTimeout(() => input.focus(), 100);
            });

            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) overlay.classList.add('hidden');
            });
        });
    </script>
</header>
