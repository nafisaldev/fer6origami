@php
    $siteName = \App\Models\WebsiteSetting::get('site_name', 'Fer6origami');
    $tagline = \App\Models\WebsiteSetting::get('site_tagline', 'Streaming Atelier');
    $siteLogo = \App\Models\WebsiteSetting::get('site_logo');
    $logoUrl = $siteLogo ? Storage::disk('public')->url($siteLogo) : asset('images/emblem.png');
    $copyrightText = \App\Models\WebsiteSetting::get('footer_copyright', '© ' . date('Y') . ' ' . $siteName . '. All rights reserved.');
    $footerPages = \App\Models\CustomPage::where('is_published', true)->get();
@endphp

<footer class="mt-20 border-t border-white/5 bg-[#090a0f] pb-16 text-slate-400 sm:pb-0">
    <div class="mx-auto w-full max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-10 md:grid-cols-4">
            {{-- BRAND COLUMN --}}
            <div class="md:col-span-2">
                <div class="flex items-center gap-3">
                    <img src="{{ $logoUrl }}" alt="{{ $siteName }} Emblem" class="h-9 w-9 rounded-full object-cover shadow-subtle">
                    <span class="font-display text-lg font-bold tracking-tight text-white">{{ $siteName }}</span>
                </div>
                <p class="mt-4 max-w-sm text-sm leading-6 text-slate-400">
                    {{ $siteName }} is an anime streaming and catalog platform with daily episode updates, high-speed interface, and a cinematic viewing experience.
                </p>
                <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
                    <span class="inline-block h-2 w-2 rounded-full bg-emerald-500"></span>
                    <span>Streaming Service Online</span>
                </div>
            </div>

            {{-- NAV COLUMN --}}
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-200">Navigation</p>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('home') }}" class="transition hover:text-white">Home</a></li>
                    <li><a href="{{ route('anime.index') }}" class="transition hover:text-white">Full Catalog</a></li>
                    <li><a href="{{ route('anime.index', ['status' => 'ongoing']) }}" class="transition hover:text-white">Ongoing Anime</a></li>
                    <li><a href="{{ route('anime.index', ['status' => 'completed']) }}" class="transition hover:text-white">Completed Anime</a></li>
                </ul>
            </div>

            {{-- USER & LEGAL COLUMN --}}
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-200">Account & Pages</p>
                <ul class="mt-4 space-y-2.5 text-sm">
                    @auth
                        <li><a href="{{ route('watchlist.index') }}" class="transition hover:text-white">My Watchlist</a></li>
                        @if (auth()->user()->isAdmin())
                            <li><a href="{{ route('admin.dashboard') }}" class="text-brand-400 transition hover:underline">Admin Panel</a></li>
                        @endif
                    @else
                        <li><a href="{{ route('login') }}" class="transition hover:text-white">Account Login</a></li>
                        <li><a href="{{ route('register') }}" class="transition hover:text-white">New Registration</a></li>
                    @endauth
                    @foreach ($footerPages as $page)
                        <li><a href="{{ route('pages.show', $page->slug) }}" class="transition hover:text-white">{{ $page->title }}</a></li>
                    @endforeach
                    <li><button type="button" onclick="document.getElementById('dmca-modal').classList.remove('hidden')" class="text-xs text-slate-400 transition hover:text-white">DMCA Disclaimer</button></li>
                </ul>
            </div>
        </div>

        <div class="mt-10 flex flex-col items-center justify-between gap-4 border-t border-white/5 pt-6 text-xs text-slate-400 sm:flex-row">
            <p>{{ $copyrightText }}</p>
            <div class="flex items-center gap-4">
                <button type="button" onclick="document.getElementById('dmca-modal').classList.remove('hidden')" class="transition hover:text-white underline">DMCA Policy</button>
                <span>•</span>
                <span>Fast Cache Enabled</span>
                <span>•</span>
                <span>Optimized Client-Side</span>
            </div>
        </div>
    </div>
</footer>
