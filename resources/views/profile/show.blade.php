@extends('layouts.app')

@section('title', 'Profil ' . $user->name . ' (Lv. ' . $levelInfo['level'] . ') — Fer6origami')

@section('content')
<div class="space-y-8">
    {{-- PROFILE HEADER HERO --}}
    <div class="surface-panel relative overflow-hidden rounded-3xl border border-white/10 p-6 sm:p-8 shadow-elevated">
        <div class="absolute -right-16 -top-16 h-72 w-72 rounded-full bg-brand-500/10 blur-3xl pointer-events-none"></div>

        <div class="relative flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-col sm:flex-row sm:items-center gap-6">
                {{-- Avatar with Level Border --}}
                <div class="relative inline-block self-start rounded-2xl p-1 {{ $membershipVisual['profile_frame'] }}">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-24 w-24 sm:h-28 sm:w-28 rounded-xl object-cover">
                    <span class="absolute -bottom-2 -right-2 inline-flex items-center justify-center rounded-xl border border-white/20 px-2.5 py-0.5 text-xs font-black shadow-glow {{ $levelInfo['badge_class'] }}">
                        Lv. {{ $levelInfo['level'] }}
                    </span>
                </div>

                {{-- User Info --}}
                <div class="min-w-0 flex-1 space-y-2">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h1 class="font-display text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{{ $user->name }}</h1>

                        <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-0.5 text-xs font-extrabold {{ $levelInfo['badge_class'] }}">
                            <span class="h-1.5 w-1.5 rounded-full bg-current animate-pulse"></span>
                            {{ $levelInfo['title'] }}
                        </span>

                        @if ($user->isAdmin())
                            <span class="rounded-full bg-purple-500/20 border border-purple-500/30 px-3 py-0.5 text-xs font-extrabold text-purple-300">
                                {{ $user->roleModel->name ?? strtoupper($user->role) }}
                            </span>
                        @endif

                        <span class="rounded-full border px-3 py-0.5 text-xs font-extrabold {{ $membershipTier === 'vip' ? 'border-amber-500/40 bg-amber-500/15 text-amber-300' : ($membershipTier === 'premium' ? 'border-brand-500/40 bg-brand-500/15 text-brand-300' : 'border-white/10 bg-white/5 text-slate-300') }}">
                            {{ ucfirst($membershipTier) }}
                        </span>

                        @if ($user->email_verified_at)
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 px-2.5 py-0.5 text-[11px] font-bold text-emerald-400" title="Akun Terverifikasi">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                Verified
                            </span>
                        @endif
                    </div>

                    @if ($user->bio)
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-2xl">{{ $user->bio }}</p>
                    @else
                        <p class="text-xs text-slate-500 italic">"Penonton setia anime di Fer6origami."</p>
                    @endif

                    <div class="flex flex-wrap items-center gap-4 text-xs text-slate-400 pt-1">
                        @if ($user->favorite_anime)
                            <span class="inline-flex items-center gap-1.5 text-slate-300">
                                <span class="text-brand-400">Favorit:</span>
                                <strong>{{ $user->favorite_anime }}</strong>
                            </span>
                            <span>•</span>
                        @endif
                        <span>Bergabung sejak <strong>{{ $user->created_at->translatedFormat('d F Y') }}</strong></span>
                        @if ($isOwnProfile || (auth()->check() && auth()->user()->isAdmin()))
                            <span>•</span>
                            <span class="text-slate-500">{{ $user->email }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            @if ($isOwnProfile)
                <div class="flex flex-wrap items-center gap-2 self-start md:self-center">
                    <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-bold text-white shadow-glow transition hover:bg-brand-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Edit Profil</span>
                    </a>
                    @if ($user->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs font-semibold text-slate-200 transition hover:bg-white/10 hover:text-white">
                            <span>Admin Panel</span>
                        </a>
                    @endif
                </div>
            @endif
        </div>

        {{-- LEVEL & XP PROGRESS BAR SECTION --}}
        <div class="mt-6 pt-6 border-t border-white/10">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2 text-xs">
                <div class="flex items-center gap-2">
                    <span class="font-extrabold text-white">Level {{ $levelInfo['level'] }}</span>
                    <span class="text-slate-400">({{ $levelInfo['rank_name'] }})</span>
                    <span class="text-slate-500">•</span>
                    <span class="text-slate-300 font-bold">{{ number_format($levelInfo['current_xp']) }} XP</span>
                </div>
                <div class="text-[11px] text-slate-400">
                    @if ($levelInfo['xp_for_next'] > 0)
                        <span>Kurang <strong class="text-brand-400 font-bold">{{ number_format($levelInfo['xp_for_next']) }} XP</strong> menuju <strong class="text-white">Level {{ $levelInfo['level'] + 1 }}</strong></span>
                    @else
                        <span class="text-emerald-400 font-bold">Level Maksimum Tercapai!</span>
                    @endif
                    <span class="ml-1 text-slate-500">({{ $levelInfo['progress_percent'] }}%)</span>
                </div>
            </div>

            {{-- Visual Animated Progress Bar --}}
            <div class="relative h-3.5 w-full overflow-hidden rounded-full bg-white/5 border border-white/10 p-0.5">
                <div class="h-full rounded-full transition-all duration-700 ease-out {{ $levelInfo['bar_class'] }}" style="width: {{ $levelInfo['progress_percent'] }}%;"></div>
            </div>

            <div class="mt-2.5 flex items-center justify-between text-[10px] text-slate-500 font-medium">
                <span>{{ number_format($levelInfo['level_min_xp']) }} XP</span>
                <span>Target: {{ number_format($levelInfo['next_level_xp']) }} XP</span>
            </div>
        </div>
    </div>

    {{-- MEMBERSHIP RINGKASAN --}}
    @if ($isOwnProfile)
        @php
            $membershipStatusLabel = match ($membership->status) {
                'active' => 'Aktif',
                'pending' => 'Menunggu',
                'expired' => 'Kedaluwarsa',
                'cancelled' => 'Dicabut',
                default => ucfirst((string) $membership->status),
            };
        @endphp
        <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-5 shadow-elevated">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-start gap-3">
                    <span class="rounded-xl bg-brand-500/15 p-2.5 text-brand-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-white">Membership Anda</h2>
                        <p class="mt-0.5 text-[11px] text-slate-400">Paket aktif, masa berlaku, dan boost XP dari membership.</p>
                    </div>
                </div>
                <span class="w-fit rounded-full border px-3 py-1 text-xs font-extrabold {{ $membershipTier === 'vip' ? 'border-amber-500/40 bg-amber-500/15 text-amber-300' : ($membershipTier === 'premium' ? 'border-brand-500/40 bg-brand-500/15 text-brand-300' : 'border-white/10 bg-white/5 text-slate-300') }}">
                    {{ ucfirst($membershipTier) }}
                </span>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-white/5 bg-white/5 p-3">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Paket</p>
                    <p class="mt-1 truncate text-sm font-bold text-white">{{ $membership->plan->name ?? ucfirst($membershipTier) }}</p>
                </div>
                <div class="rounded-xl border border-white/5 bg-white/5 p-3">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Status</p>
                    <p class="mt-1 text-sm font-bold {{ $membership->status === 'active' ? 'text-emerald-400' : 'text-amber-400' }}">{{ $membershipStatusLabel }}</p>
                </div>
                <div class="rounded-xl border border-white/5 bg-white/5 p-3">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Berlaku Sampai</p>
                    <p class="mt-1 truncate text-sm font-bold text-white">
                        {{ $membership->expires_at ? $membership->expires_at->translatedFormat('d M Y H:i') : 'Tanpa batas waktu' }}
                    </p>
                </div>
            </div>

            <div class="mt-4 flex flex-col gap-2 border-t border-white/5 pt-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[11px] text-slate-400">
                    Boost XP aktif: <strong class="text-brand-300">{{ $membershipXpMultiplier }}x</strong>
                    @if ($user->xp_manual)
                        <span class="block text-slate-500">Nonaktif karena XP diatur manual oleh admin.</span>
                    @endif
                </p>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('membership.show') }}" class="rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white transition hover:bg-brand-600">Kelola Membership</a>
                    <a href="{{ route('membership.pricing') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10">Lihat Paket</a>
                </div>
            </div>
        </div>
    @endif

    {{-- STREAK & MISI MINGGUAN --}}
    <div class="grid gap-4 lg:grid-cols-2">
        <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-5 shadow-elevated">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="rounded-xl bg-orange-500/15 p-2.5 text-orange-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3c1 3-1 4-1 6a3 3 0 006 0c0-1-1-2-1-3 3 2 5 5 5 8a9 9 0 11-18 0c0-4 3-7 5-9 0 2 1 3 2 4 0-2 1-4 2-6z" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-white">Streak Menonton</h2>
                        <p class="mt-0.5 text-[11px] text-slate-400">
                            {{ $streak['watched_today'] ? 'Sudah nonton hari ini. Pertahankan!' : 'Nonton 1 episode hari ini untuk jaga streak.' }}
                        </p>
                    </div>
                </div>
                <div class="shrink-0 text-right">
                    <p class="font-display text-2xl font-black text-orange-400">{{ $streak['current'] }} <span class="text-xs font-semibold text-slate-400">hari</span></p>
                    <p class="text-[10px] text-slate-500">Terbaik: {{ $streak['best'] }} hari</p>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-7 gap-1.5">
                @foreach ($streak['days'] as $day)
                    <div class="flex flex-col items-center gap-1.5">
                        <div class="flex h-8 w-full items-center justify-center rounded-lg border text-[10px] font-bold {{ $day['active'] ? 'border-orange-500/40 bg-orange-500/20 text-orange-300' : 'border-white/5 bg-white/[0.02] text-slate-600' }} {{ $day['is_today'] ? 'ring-2 ring-orange-400/40' : '' }}">
                            {{ $day['active'] ? 'Aktif' : '-' }}
                        </div>
                        <span class="text-[9px] {{ $day['is_today'] ? 'font-bold text-orange-300' : 'text-slate-500' }}">{{ $day['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-5 shadow-elevated">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="rounded-xl bg-brand-500/15 p-2.5 text-brand-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" />
                            <circle cx="12" cy="12" r="5" />
                            <circle cx="12" cy="12" r="1" fill="currentColor" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-white">Misi Mingguan</h2>
                        <p class="mt-0.5 text-[11px] text-slate-400">Tonton {{ $weeklyQuest['target'] }} episode sebelum minggu berakhir.</p>
                    </div>
                </div>
                <span class="shrink-0 rounded-full border border-brand-500/30 bg-brand-500/10 px-2.5 py-1 text-[11px] font-extrabold text-brand-300">+{{ $weeklyQuest['reward_xp'] }} XP</span>
            </div>

            <div class="mt-4 flex items-center justify-between text-xs">
                <span class="font-bold text-white">{{ $weeklyQuest['progress'] }}/{{ $weeklyQuest['target'] }} episode</span>
                <span class="text-slate-400">{{ $weeklyQuest['days_left'] }} hari tersisa</span>
            </div>
            <div class="mt-2 h-2.5 w-full overflow-hidden rounded-full border border-white/10 bg-white/5 p-0.5">
                <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-orange-400 transition-all duration-700" style="width: {{ $weeklyQuest['percent'] }}%;"></div>
            </div>

            @if ($isOwnProfile)
                @if ($weeklyQuest['claimed'])
                    <p class="mt-3 text-[11px] font-bold text-emerald-400">Hadiah minggu ini sudah diklaim. Sampai minggu depan!</p>
                @elseif ($weeklyQuest['claimable'])
                    <form method="POST" action="{{ route('profile.weekly-quest.claim') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white shadow-glow transition hover:bg-brand-600">
                            Klaim +{{ $weeklyQuest['reward_xp'] }} XP
                        </button>
                    </form>
                @else
                    <p class="mt-3 text-[11px] text-slate-500">Tinggal {{ $weeklyQuest['target'] - $weeklyQuest['progress'] }} episode lagi untuk klaim hadiah.</p>
                @endif
            @endif
        </div>
    </div>

    {{-- 4 STAT CARDS --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-4 transition hover:border-brand-500/30">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Ditonton</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-500/10 text-brand-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-2 font-display text-2xl font-black text-white">{{ number_format($stats['total_watched']) }}</p>
            <p class="mt-1 text-[11px] text-brand-400 font-semibold">+{{ number_format($stats['total_watched'] * 25) }} XP diperoleh</p>
        </div>

        <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-4 transition hover:border-brand-500/30">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Waktu Streaming</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-500/10 text-blue-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <p class="mt-2 font-display text-2xl font-black text-blue-400">{{ $stats['watch_hours'] }} <span class="text-xs font-normal text-slate-400">jam</span></p>
            <p class="mt-1 text-[11px] text-slate-400">Estimasi tontonan</p>
        </div>

        <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-4 transition hover:border-brand-500/30">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Watchlist Favorit</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500/10 text-amber-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg>
                </span>
            </div>
            <p class="mt-2 font-display text-2xl font-black text-amber-400">{{ number_format($stats['watchlist_count']) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">Anime tersimpan</p>
        </div>

        <div class="surface-panel min-w-0 rounded-2xl border border-white/10 p-4 transition hover:border-brand-500/30">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Diskusi & Ulasan</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </span>
            </div>
            <p class="mt-2 font-display text-2xl font-black text-emerald-400">{{ number_format($stats['comments_count'] + $stats['reviews_count']) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">{{ $stats['comments_count'] }} komentar • {{ $stats['reviews_count'] }} review</p>
        </div>
    </div>

    {{-- LANJUT NONTON --}}
    @if ($isOwnProfile && $continueWatching->isNotEmpty())
        <div class="surface-panel min-w-0 rounded-3xl border border-white/10 p-5 shadow-elevated">
            <div class="mb-4 flex min-w-0 items-center justify-between gap-2 border-b border-white/10 pb-3">
                <div class="flex items-center gap-2">
                    <span class="rounded-lg bg-brand-500/10 p-1.5 text-brand-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <h2 class="text-xs sm:text-sm font-bold text-white">Lanjut Nonton</h2>
                </div>
                <span class="text-[11px] text-slate-400">Kembali ke episode terakhir kamu</span>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($continueWatching as $item)
                    <a href="{{ $item['url'] }}" class="group flex min-w-0 items-center gap-3 rounded-2xl border border-white/5 bg-white/[0.02] p-3 transition hover:border-brand-500/40 hover:bg-white/5">
                        <img src="{{ $item['anime']->poster_image ?: asset('images/poster-placeholder.svg') }}" alt="{{ $item['anime']->title }}" class="h-16 w-11 shrink-0 rounded-lg object-cover">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-bold text-white">{{ $item['anime']->title }}</p>
                            <p class="mt-0.5 truncate text-[11px] font-semibold text-brand-400">{{ $item['label'] }}</p>
                            <p class="mt-0.5 text-[10px] text-slate-500">Terakhir {{ $item['watched_at']->diffForHumans() }}</p>
                        </div>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-500/15 text-brand-400 transition group-hover:bg-brand-500 group-hover:text-white">▶</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- PENCAPAIAN & BADGE PENGGUNA --}}
    <div class="surface-panel min-w-0 rounded-3xl border border-white/10 p-6 shadow-elevated">
        <div class="mb-5 flex items-center justify-between border-b border-white/10 pb-4">
            <div class="flex items-center gap-2">
                <span class="rounded-xl bg-amber-500/10 p-2 text-amber-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-sm sm:text-base font-bold text-white">Lencana & Pencapaian (Achievements)</h2>
                    <p class="text-xs text-slate-400">Tingkatkan terus aktivitas menonton, komentar, dan ulasan untuk membuka seluruh lencana!</p>
                </div>
            </div>
            <span class="rounded-full bg-white/5 border border-white/10 px-3 py-1 text-xs font-bold text-slate-300">
                {{ collect($badges)->where('unlocked', true)->count() }} / {{ count($badges) }} Terbuka
            </span>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-8">
            @foreach ($badges as $badge)
                <div class="relative flex flex-col items-center text-center rounded-2xl border p-3 transition {{ $badge['unlocked'] ? 'border-amber-500/30 bg-amber-500/5 hover:border-amber-500/50' : 'border-white/5 bg-white/[0.02] opacity-50 grayscale' }}">
                    <span class="mb-1.5 flex h-8 w-8 items-center justify-center {{ $badge['unlocked'] ? 'text-amber-400' : 'text-slate-400' }}">{!! $badge['icon'] !!}</span>
                    <p class="font-bold text-xs text-white truncate w-full" title="{{ $badge['name'] }}">{{ $badge['name'] }}</p>
                    <p class="mt-1 text-[10px] text-slate-400 line-clamp-2 leading-tight">{{ $badge['description'] }}</p>
                    <div class="mt-2">
                        @if ($badge['unlocked'])
                            <span class="inline-flex items-center gap-1 text-[9px] font-bold text-amber-400">
                                Terbuka
                            </span>
                        @else
                            <span class="text-[9px] font-medium text-slate-500">Terkunci</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- DUA KOLOM: RIWAYAT TONTONAN & WATCHLIST --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Riwayat Tontonan Terbaru --}}
        <div class="surface-panel min-w-0 rounded-3xl border border-white/10 p-5 shadow-elevated">
            <div class="mb-4 flex min-w-0 items-center justify-between gap-2 border-b border-white/10 pb-3">
                <div class="flex items-center gap-2">
                    <span class="rounded-lg bg-brand-500/10 p-1.5 text-brand-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <h2 class="text-xs sm:text-sm font-bold text-white">Riwayat Tontonan Terbaru</h2>
                </div>
                <span class="text-xs text-slate-400">{{ $user->watchHistories->count() }} aktivitas</span>
            </div>

            @if ($user->watchHistories->isEmpty())
                <div class="py-12 text-center">
                    <p class="text-xs text-slate-500">Belum ada riwayat anime yang ditonton.</p>
                    <a href="{{ route('anime.index') }}" class="mt-2 inline-block text-xs font-semibold text-brand-400 hover:underline">Mulai Nonton Anime &rarr;</a>
                </div>
            @else
                <div class="min-w-0 space-y-2.5 max-h-96 overflow-y-auto pr-1 scrollbar-thin">
                    @foreach ($user->watchHistories as $wh)
                        @php
                            $whPoster = $wh->anime->poster_image ?? ($wh->anime_poster ?: null);
                            $whTitle = $wh->anime->title ?? ($wh->anime_title ?: 'Anime');
                            $whSlug = $wh->anime_slug ?: ($wh->anime->slug ?? null);
                            $replayUrl = $whSlug
                                ? ($wh->episode_route_id
                                    ? route('watch.show', ['animeId' => $whSlug, 'episodeId' => $wh->episode_route_id])
                                    : route('anime.show', $whSlug))
                                : null;
                        @endphp
                        <div class="flex min-w-0 items-center justify-between gap-3 rounded-xl border border-white/5 bg-white/5 p-2.5 transition hover:bg-white/10">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                @if ($whPoster)
                                    <img src="{{ $whPoster }}" alt="{{ $whTitle }}" class="h-12 w-9 rounded object-cover shrink-0">
                                @else
                                    <div class="h-12 w-9 rounded bg-white/10 flex items-center justify-center text-[10px] font-bold text-slate-400 shrink-0">Anime</div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-white truncate">{{ $whTitle }}</p>
                                    <p class="text-[11px] text-brand-400 font-semibold truncate">{{ $wh->episode_title ?: 'Episode ' . ($wh->episode->episode_number ?? '1') }}</p>
                                    <p class="text-[10px] text-slate-500">{{ $wh->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            @if ($replayUrl)
                                <a href="{{ $replayUrl }}" class="rounded-lg border border-white/10 px-2.5 py-1 text-[11px] font-semibold text-slate-300 hover:bg-white/10 hover:text-white shrink-0">
                                    Putar Lagi
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Watchlist & Anime Favorit --}}
        <div class="surface-panel min-w-0 rounded-3xl border border-white/10 p-5 shadow-elevated">
            <div class="mb-4 flex min-w-0 items-center justify-between gap-2 border-b border-white/10 pb-3">
                <div class="flex items-center gap-2">
                    <span class="rounded-lg bg-amber-500/10 p-1.5 text-amber-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                        </svg>
                    </span>
                    <h2 class="text-xs sm:text-sm font-bold text-white">Watchlist Tersimpan</h2>
                </div>
                <a href="{{ route('watchlist.index') }}" class="text-xs font-semibold text-brand-400 hover:underline">Kelola &rarr;</a>
            </div>

            @if ($user->watchlists->isEmpty())
                <div class="py-12 text-center">
                    <p class="text-xs text-slate-500">Belum ada anime yang ditambahkan ke Watchlist.</p>
                    <a href="{{ route('anime.index') }}" class="mt-2 inline-block text-xs font-semibold text-brand-400 hover:underline">Jelajahi Katalog &rarr;</a>
                </div>
            @else
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 max-h-96 overflow-y-auto pr-1 scrollbar-thin">
                    @foreach ($user->watchlists as $wl)
                        <div class="group relative flex flex-col overflow-hidden rounded-xl border border-white/5 bg-white/5 transition hover:border-brand-500/40">
                            <div class="aspect-[3/4] w-full overflow-hidden bg-slate-900">
                                @if ($wl->anime && $wl->anime->poster_image)
                                    <img src="{{ $wl->anime->poster_image }}" alt="{{ $wl->anime->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-xs text-slate-500">No Image</div>
                                @endif
                            </div>
                            <div class="p-2.5">
                                <p class="text-xs font-bold text-white truncate" title="{{ $wl->anime->title ?? 'Anime' }}">{{ $wl->anime->title ?? 'Anime' }}</p>
                                <span class="text-[10px] text-brand-400 font-semibold">{{ $wl->status ?: 'Watching' }}</span>
                            </div>
                            @if ($wl->anime)
                                <a href="{{ route('anime.show', $wl->anime->slug ?: $wl->anime->id) }}" class="absolute inset-0" aria-label="Lihat {{ $wl->anime->title }}"></a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ULASAN & KOMENTAR SAYA --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Reviews --}}
        <div class="surface-panel min-w-0 rounded-3xl border border-white/10 p-5 shadow-elevated">
            <h2 class="text-xs sm:text-sm font-bold text-white mb-3 border-b border-white/10 pb-3 flex items-center gap-2">
                Ulasan & Rating Anime ({{ $user->reviews->count() }})
            </h2>
            @if ($user->reviews->isEmpty())
                <p class="py-8 text-center text-xs text-slate-500">Belum ada review yang ditulis.</p>
            @else
                <div class="space-y-3 max-h-80 overflow-y-auto pr-1 scrollbar-thin">
                    @foreach ($user->reviews as $rev)
                        <div class="rounded-xl border border-white/5 bg-white/5 p-3">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-bold text-white truncate">{{ $rev->anime->title ?? 'Anime' }}</p>
                                <span class="rounded bg-amber-500/20 px-2 py-0.5 text-xs font-extrabold text-amber-300">Rating {{ $rev->rating }}/10</span>
                            </div>
                            @if ($rev->title)
                                <p class="mt-1 text-xs font-semibold text-slate-200">{{ $rev->title }}</p>
                            @endif
                            <p class="mt-1 text-[11px] text-slate-400 leading-snug">{{ Str::limit($rev->content, 120) }}</p>
                            <span class="mt-2 block text-[10px] text-slate-500">{{ $rev->created_at->diffForHumans() }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Comments --}}
        <div class="surface-panel min-w-0 rounded-3xl border border-white/10 p-5 shadow-elevated">
            <h2 class="text-xs sm:text-sm font-bold text-white mb-3 border-b border-white/10 pb-3 flex items-center gap-2">
                Komentar Diskusi ({{ $user->comments->count() }})
            </h2>
            @if ($user->comments->isEmpty())
                <p class="py-8 text-center text-xs text-slate-500">Belum ada komentar yang diposting.</p>
            @else
                <div class="space-y-3 max-h-80 overflow-y-auto pr-1 scrollbar-thin">
                    @foreach ($user->comments as $com)
                        <div class="rounded-xl border p-3 {{ $membershipVisual['comment_card'] }}">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex min-w-0 items-center gap-2">
                                    <p class="truncate text-xs font-bold {{ $membershipVisual['comment_name'] }}">{{ $com->anime->title ?? 'Komentar Anime' }}</p>
                                    @if ($membershipVisual['tier'] !== 'free')
                                        <span class="shrink-0 rounded border px-1.5 py-0.5 text-[9px] font-extrabold uppercase {{ $membershipVisual['comment_badge'] }}">
                                            {{ $membershipVisual['label'] }}
                                        </span>
                                    @endif
                                </div>
                                <span class="shrink-0 text-[10px] text-slate-500">{{ $com->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="mt-1 text-[11px] text-slate-300 leading-snug">{{ Str::limit($com->content, 140) }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
