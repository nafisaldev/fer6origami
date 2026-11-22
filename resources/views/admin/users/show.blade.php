@extends('layouts.app')

@section('title', 'Profil Pengguna: ' . $user->name . ' — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 border-b border-white/10 pb-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-white">Admin</a>
            <span>/</span>
            <a href="{{ route('admin.users.index') }}" class="hover:text-white">Users</a>
            <span>/</span>
            <span class="text-white">{{ $user->name }}</span>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white hover:bg-white/20">Edit Akun</a>
            <form method="POST" action="{{ route('admin.users.toggle-verify', $user) }}" class="inline">
                @csrf
                <button type="submit" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white">
                    {{ $user->email_verified_at ? 'Batalkan Verifikasi' : 'Verifikasi Akun' }}
                </button>
            </form>
            <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white">&larr; Kembali</a>
        </div>
    </div>

    {{-- USER OVERVIEW PROFILE CARD --}}
    <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-center">
            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-20 w-20 rounded-2xl object-cover ring-2 ring-brand-500/50 shadow-xl">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="font-display text-2xl font-bold text-white">{{ $user->name }}</h1>
                    <span class="rounded px-2.5 py-0.5 text-xs font-bold {{ $user->status === 'active' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400' }}">
                        {{ strtoupper($user->status) }}
                    </span>
                    <span class="rounded bg-brand-500/20 px-2.5 py-0.5 text-xs font-bold text-brand-400">
                        {{ $user->roleModel->name ?? strtoupper($user->role) }}
                    </span>
                    @if ($user->email_verified_at)
                        <span class="rounded bg-blue-500/20 px-2 py-0.5 text-[11px] font-bold text-blue-400">Terverifikasi</span>
                    @else
                        <span class="rounded bg-amber-500/20 px-2 py-0.5 text-[11px] font-bold text-amber-400">Belum Verifikasi</span>
                    @endif
                </div>
                <p class="mt-1 text-xs text-slate-400">{{ $user->email }}</p>
                <div class="mt-3 flex flex-wrap gap-4 text-xs text-slate-400">
                    <span>Terdaftar: <strong class="text-slate-300">{{ $user->created_at->translatedFormat('d F Y') }}</strong></span>
                    <span>Login Terakhir: <strong class="text-slate-300">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Belum pernah' }}</strong></span>
                    <span>IP Terakhir: <strong class="text-slate-300">{{ $user->last_login_ip ?: '-' }}</strong></span>
                </div>
            </div>
        </div>
    </div>

    {{-- LEVEL & XP CONTROL --}}
    <div id="level-control" class="surface-panel rounded-2xl border border-white/10 p-5 shadow-elevated">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-xs font-bold uppercase tracking-widest text-slate-400">Level & XP Pengguna</h2>
                <p class="mt-1 text-sm font-bold text-white">Lv. {{ $user->level }} · {{ $user->level_details['rank_name'] }}</p>
                <p class="mt-0.5 text-[11px] text-slate-400">
                    {{ number_format($user->xp) }} XP · Mode {{ $user->xp_manual ? 'Manual' : 'Otomatis' }}
                </p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <a href="{{ route('admin.badges.edit', $user) }}" class="rounded-lg border border-white/10 px-2.5 py-1 text-[11px] font-semibold text-slate-300 hover:bg-white/5 hover:text-white">Kelola Lencana</a>
                    <a href="{{ route('admin.engagement.index', ['search' => $user->email]) }}" class="rounded-lg border border-white/10 px-2.5 py-1 text-[11px] font-semibold text-slate-300 hover:bg-white/5 hover:text-white">Lihat Engagement</a>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.users.level.update', $user) }}" class="w-full space-y-3 lg:max-w-md">
                @csrf
                @method('PATCH')

                <div>
                    <label for="level" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Set Level</label>
                    <select id="level" name="level" required class="w-full rounded-xl border border-white/10 bg-[#161924] px-3.5 py-2 text-xs text-white">
                        @for ($level = 1; $level <= 50; $level++)
                            <option value="{{ $level }}" @selected((int) old('level', $user->level) === $level)>Lv. {{ $level }} — {{ \App\Services\UserLevelService::getRankInfo($level)['name'] }}</option>
                        @endfor
                    </select>
                    @error('level') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-start gap-2.5 rounded-xl border border-white/5 bg-white/[0.02] p-3 text-[11px] leading-snug text-slate-400">
                    <input type="checkbox" name="auto_sync" value="1" @checked(old('auto_sync', ! $user->xp_manual)) class="mt-0.5 h-4 w-4 shrink-0 rounded border-white/20 bg-[#161924] text-brand-500 focus:ring-brand-500">
                    <span>
                        <span class="font-bold text-slate-200">Sinkronisasi otomatis dari aktivitas</span><br>
                        Jika aktif, XP aktivitas yang lebih tinggi tetap dipakai dan level yang dipilih menjadi batas minimum. Jika nonaktif, XP dikunci tepat di level pilihan.
                    </span>
                </label>

                <div class="flex flex-wrap justify-end gap-2">
                    <button type="submit" class="rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white hover:bg-brand-400">Simpan Level</button>
                </div>
            </form>
        </div>
    </div>

    {{-- WATCHLIST & WATCH HISTORY TABS --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Watch History --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-5 shadow-elevated">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-white/5 pb-2 mb-3">
                Riwayat Tontonan Terbaru ({{ $user->watchHistories->count() }})
            </h2>
            @if ($user->watchHistories->isEmpty())
                <p class="py-8 text-center text-xs text-slate-500">Belum ada riwayat tontonan.</p>
            @else
                <div class="space-y-2.5 max-h-80 overflow-y-auto pr-1 scrollbar-thin">
                    @foreach ($user->watchHistories as $wh)
                        <div class="flex items-center justify-between rounded-xl border border-white/5 bg-white/5 p-2.5">
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-white truncate">{{ $wh->anime->title ?? ($wh->anime_title ?: 'Anime') }}</p>
                                <p class="text-[10px] text-slate-400">
                                    {{ $wh->episode ? 'Episode ' . $wh->episode->episode_number : ($wh->episode_title ?: 'Series') }} • Durasi: {{ round($wh->duration_seconds / 60) }} min
                                </p>
                            </div>
                            <span class="text-[10px] text-slate-500 shrink-0">{{ $wh->created_at->diffForHumans() }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Watchlist Favorites --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-5 shadow-elevated">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-white/5 pb-2 mb-3">
                Daftar Favorit / Watchlist ({{ $user->watchlists->count() }})
            </h2>
            @if ($user->watchlists->isEmpty())
                <p class="py-8 text-center text-xs text-slate-500">Watchlist kosong.</p>
            @else
                <div class="space-y-2.5 max-h-80 overflow-y-auto pr-1 scrollbar-thin">
                    @foreach ($user->watchlists as $wl)
                        <div class="flex items-center gap-3 rounded-xl border border-white/5 bg-white/5 p-2.5">
                            <img src="{{ $wl->anime->poster_image ?? '' }}" alt="" class="h-10 w-8 rounded object-cover">
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-white truncate">{{ $wl->anime->title ?? ($wl->anime_title ?: 'Anime') }}</p>
                                <p class="text-[10px] text-slate-400">Ditambahkan {{ $wl->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- USER COMMENTS --}}
    <div class="surface-panel rounded-2xl border border-white/10 p-5 shadow-elevated">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-white/5 pb-2 mb-3">
            Komentar Pengguna Ini ({{ $user->comments->count() }})
        </h2>
        @if ($user->comments->isEmpty())
            <p class="py-6 text-center text-xs text-slate-500">Pengguna belum pernah menulis komentar.</p>
        @else
            <div class="space-y-3">
                @foreach ($user->comments as $c)
                    <div class="rounded-xl border border-white/5 bg-white/5 p-3">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-brand-400">{{ $c->anime->title ?? 'Anime' }} {{ $c->episode ? '• Ep ' . $c->episode->episode_number : '' }}</span>
                            <span class="text-[10px] text-slate-500">{{ $c->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="mt-1.5 text-xs text-slate-200">{{ $c->content }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
