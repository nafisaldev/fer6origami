@php
    $currentRoute = request()->route()->getName();
    $pendingReportsCount = \App\Models\Report::where('status', 'pending')->count();
    $currentUser = auth()->user();

    $sections = [
        'OVERVIEW' => [
            ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'pattern' => 'admin.dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['route' => 'admin.analytics.index', 'label' => 'Analytics', 'pattern' => 'admin.analytics.*', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ['route' => 'admin.visitor-logs.index', 'label' => 'Visitor Logs', 'pattern' => 'admin.visitor-logs.*', 'icon' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
        ],
        'CONTENT MANAGEMENT' => [
            ['route' => 'admin.animes.index', 'label' => 'Anime', 'pattern' => 'admin.animes.*', 'icon' => 'M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z'],
            ['route' => 'admin.episodes.index', 'label' => 'Episodes', 'pattern' => 'admin.episodes.*', 'icon' => 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['route' => 'admin.genres.index', 'label' => 'Genres', 'pattern' => 'admin.genres.*', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
            ['route' => 'admin.studios.index', 'label' => 'Studios', 'pattern' => 'admin.studios.*', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
            ['route' => 'admin.schedules.index', 'label' => 'Release Schedule', 'pattern' => 'admin.schedules.*', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['route' => 'admin.blacklist.index', 'label' => 'Blacklist Anime', 'pattern' => 'admin.blacklist.*', 'icon' => 'M18.364 5.636a9 9 0 11-12.728 12.728 9 9 0 0112.728-12.728zM5.636 5.636l12.728 12.728'],
        ],
        'USER MANAGEMENT' => [
            ['route' => 'admin.users.index', 'label' => 'Users', 'pattern' => 'admin.users.*', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
            ['route' => 'admin.roles.index', 'label' => 'Roles & Permissions', 'pattern' => 'admin.roles.*', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
            ['route' => 'admin.engagement.index', 'label' => 'Quest & Streak', 'pattern' => 'admin.engagement.*', 'icon' => 'M13 2L3 14h7l-1 8 10-12h-7l1-8z'],
            ['route' => 'admin.watch-activity.index', 'label' => 'Riwayat & Watchlist', 'pattern' => ['admin.watch-activity.*', 'admin.watch-histories.*', 'admin.watchlists.*'], 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['route' => 'admin.badges.index', 'label' => 'Badge & Pencapaian', 'pattern' => 'admin.badges.*', 'icon' => 'M12 2l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17l-5.8 3 1.1-6.5L2.6 8.8l6.5-.9L12 2z'],
            ['route' => 'admin.comments.index', 'label' => 'Comments', 'pattern' => 'admin.comments.*', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
            ['route' => 'admin.reviews.index', 'label' => 'Reviews', 'pattern' => 'admin.reviews.*', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
            ['route' => 'admin.reports.index', 'label' => 'Reports', 'pattern' => 'admin.reports.*', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'badge' => $pendingReportsCount],
        ],
        'MEMBERSHIP' => [
            ['route' => 'admin.membership.index', 'label' => 'Membership', 'pattern' => ['admin.membership.index', 'admin.membership.show', 'admin.membership.grant', 'admin.membership.renew', 'admin.membership.change', 'admin.membership.revoke', 'admin.membership.sync'], 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
            ['route' => 'admin.membership-plans.index', 'label' => 'Paket & Harga', 'pattern' => ['admin.membership-plans.*', 'admin.membership-prices.*'], 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['route' => 'admin.vouchers.index', 'label' => 'Voucher', 'pattern' => 'admin.vouchers.*', 'icon' => 'M15 9a2 2 0 11-4 0 2 2 0 014 0zM7 15a2 2 0 11-4 0 2 2 0 014 0zm10 2a2 2 0 11-4 0 2 2 0 014 0zM7 7a2 2 0 11-4 0 2 2 0 014 0zm8.5 5.5l-9 4m0-9l9 4'],
            ['route' => 'admin.payments.index', 'label' => 'Pembayaran', 'pattern' => 'admin.payments.*', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
            ['route' => 'admin.membership-history.index', 'label' => 'Riwayat Membership', 'pattern' => 'admin.membership-history.*', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ],
        'COMMUNICATION' => [
            ['route' => 'admin.announcements.index', 'label' => 'Announcements', 'pattern' => 'admin.announcements.*', 'icon' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z'],
            ['route' => 'admin.notifications.index', 'label' => 'Notifications', 'pattern' => 'admin.notifications.*', 'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
        ],
        'WEBSITE MANAGEMENT' => [
            ['route' => 'admin.appearance.homepage', 'label' => 'Homepage', 'pattern' => 'admin.appearance.homepage', 'icon' => 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z'],
            ['route' => 'admin.appearance.theme', 'label' => 'Appearance', 'pattern' => 'admin.appearance.theme', 'icon' => 'M7 21a4 4 0 01-4-4 5 5 0 014-4h4a4 4 0 014 4v4a2 2 0 01-2 2H7z M17 14V8a2 2 0 00-2-2H9a2 2 0 00-2 2v6'],
            ['route' => 'admin.pages.index', 'label' => 'Pages', 'pattern' => 'admin.pages.*', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['route' => 'admin.settings.seo', 'label' => 'SEO', 'pattern' => 'admin.settings.seo', 'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'],
        ],
        'SYSTEM MANAGEMENT' => [
            ['route' => 'admin.activity-logs.index', 'label' => 'Activity Logs', 'pattern' => 'admin.activity-logs.*', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['route' => 'admin.system.monitoring', 'label' => 'System Monitoring', 'pattern' => 'admin.system.monitoring', 'icon' => 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z'],
            ['route' => 'admin.system.cache', 'label' => 'Cache Management', 'pattern' => 'admin.system.cache', 'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
            ['route' => 'admin.system.backup', 'label' => 'Database Backup', 'pattern' => 'admin.system.backup', 'icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4'],
            ['route' => 'admin.settings.security', 'label' => 'Security Settings', 'pattern' => 'admin.settings.security', 'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
            ['route' => 'admin.settings.general', 'label' => 'General Settings', 'pattern' => 'admin.settings.general', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
        ],
    ];

    if ($currentUser->isSuperAdmin()) {
        $sections['SYSTEM MANAGEMENT'][] = [
            'route' => 'admin.system.delete-all-content',
            'label' => 'Hapus Semua Konten',
            'pattern' => 'admin.system.delete-all-content',
            'icon' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
        ];
    }
@endphp

<aside class="w-full shrink-0 lg:w-72">
    <details class="surface-panel mb-1 rounded-2xl border border-white/10 p-4 shadow-elevated lg:hidden">
        <summary class="cursor-pointer text-xs font-bold text-white">Menu Admin</summary>
        <div class="mt-3 grid max-h-80 grid-cols-2 gap-2 overflow-y-auto scrollbar-thin">
            @foreach ($sections as $sectionLinks)
                @foreach ($sectionLinks as $link)
                    <a href="{{ route($link['route']) }}" class="truncate rounded-lg border px-2.5 py-2 text-[11px] font-semibold {{ request()->routeIs($link['pattern']) ? 'border-brand-500/30 bg-brand-500/10 text-brand-300' : 'border-white/5 bg-white/5 text-slate-300' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            @endforeach
        </div>
    </details>
    <div class="surface-panel sticky top-20 hidden max-h-[calc(100vh-6rem)] overflow-y-auto rounded-2xl border border-white/10 p-4 shadow-elevated scrollbar-thin scrollbar-thumb-white/10 lg:block">
        {{-- ADMIN HEADER PROFILE BADGE --}}
        <div class="mb-5 flex items-center gap-3 border-b border-white/10 pb-4">
            <div class="relative">
                <img src="{{ $currentUser->avatar_url }}" alt="{{ $currentUser->name }}" class="h-10 w-10 rounded-xl object-cover ring-2 ring-brand-500/50">
                <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-[#11131b] bg-emerald-500"></span>
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-xs font-bold text-white">{{ $currentUser->name }}</p>
                <div class="mt-0.5 flex items-center gap-1.5">
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-brand-500"></span>
                    <span class="truncate text-[10px] font-extrabold uppercase tracking-wider text-brand-400">
                        {{ $currentUser->roleModel->name ?? strtoupper($currentUser->role) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- NAVIGATION SECTIONS --}}
        <nav class="space-y-6">
            @foreach ($sections as $sectionTitle => $items)
                <div>
                    <p class="mb-2 px-3 text-[10px] font-extrabold uppercase tracking-[0.2em] text-slate-500">
                        {{ $sectionTitle }}
                    </p>
                    <div class="space-y-1">
                        @foreach ($items as $item)
                            @php
                                $isActive = request()->routeIs($item['pattern']);
                            @endphp
                            <a
                                href="{{ route($item['route']) }}"
                                class="group flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold transition {{ $isActive ? 'bg-brand-500 text-white shadow-glow' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}"
                            >
                                <div class="flex items-center gap-2.5 truncate">
                                    <svg class="h-4 w-4 shrink-0 transition {{ $isActive ? 'text-white' : 'text-slate-500 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $item['icon'] }}" />
                                    </svg>
                                    <span class="truncate">{{ $item['label'] }}</span>
                                </div>
                                @if (!empty($item['badge']) && $item['badge'] > 0)
                                    <span class="inline-flex h-4 min-w-[16px] items-center justify-center rounded-full px-1 text-[10px] font-extrabold {{ $isActive ? 'bg-white text-brand-600' : 'bg-red-500 text-white' }}">
                                        {{ $item['badge'] }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>

        {{-- QUICK ACTIONS --}}
        <div class="mt-6 border-t border-white/10 pt-4">
            <a
                href="{{ route('home') }}"
                target="_blank"
                class="flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/5 py-2 text-xs font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                <span>Lihat Website</span>
            </a>
        </div>
    </div>
</aside>
