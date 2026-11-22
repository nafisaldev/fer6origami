@extends('layouts.app')

@section('title', 'Quest & Streak — Admin Fer6origami')

@section('content')
<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.35em] text-slate-500">User Management</p>
            <h1 class="mt-2 font-display text-4xl font-bold text-white">Quest & Streak</h1>
            <p class="mt-2 text-sm text-slate-400">Pantau streak menonton, progres misi mingguan, dan level pengguna.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="rounded-2xl border border-white/10 px-5 py-3 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white">
            Kelola Users
        </a>
    </div>

    <div class="surface-panel rounded-2xl border border-white/10 p-4 shadow-elevated">
        <form method="GET" action="{{ route('admin.engagement.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau email pengguna..." class="w-full rounded-xl border border-white/10 bg-[#161924] px-3.5 py-2 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">
            </div>
            <div>
                <select name="quest" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white">
                    <option value="all" @selected($selectedQuest === 'all')>Semua Status Misi</option>
                    <option value="progress" @selected($selectedQuest === 'progress')>Belum Selesai</option>
                    <option value="claimable" @selected($selectedQuest === 'claimable')>Siap Diklaim</option>
                    <option value="claimed" @selected($selectedQuest === 'claimed')>Sudah Diklaim</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="w-full rounded-xl bg-white/10 px-4 py-2 text-xs font-semibold text-white transition hover:bg-white/20">Filter</button>
            </div>
        </form>
    </div>

    @include('admin.partials.delete-all-button', ['resource' => 'weekly-quests', 'label' => 'Misi Mingguan'])

    <div class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm text-slate-300">
                <thead class="border-b border-white/5 bg-[#11131b] text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-4 py-3.5">Pengguna</th>
                        <th class="px-4 py-3.5">Level</th>
                        <th class="px-4 py-3.5">Streak</th>
                        <th class="px-4 py-3.5">Misi Mingguan</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($rows as $row)
                        @php
                            $user = $row['user'];
                            $quest = $row['quest'];
                            $streak = $row['streak'];
                        @endphp
                        <tr class="transition hover:bg-white/[0.02]">
                            <td class="px-4 py-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-9 w-9 shrink-0 rounded-xl object-cover ring-1 ring-white/10">
                                    <div class="min-w-0">
                                        <p class="truncate font-bold text-white">{{ $user->name }}</p>
                                        <p class="truncate text-[10px] text-slate-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-bold text-white">Lv. {{ $row['levelInfo']['level'] }}</p>
                                <p class="text-[10px] text-slate-500">{{ number_format($row['levelInfo']['current_xp']) }} XP</p>
                                <p class="text-[10px] text-slate-500">{{ $row['levelInfo']['rank_name'] }}</p>
                                @if ($user->xp_manual)
                                    <span class="mt-1 inline-block rounded bg-amber-500/15 px-1.5 py-0.5 text-[9px] font-bold text-amber-300">Manual</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-bold text-white">{{ $streak['current'] }} hari</p>
                                <p class="text-[10px] text-slate-500">Terbaik: {{ $streak['best'] }} hari</p>
                                <div class="mt-1.5 flex gap-1" aria-label="Aktivitas tujuh hari terakhir">
                                    @foreach ($streak['days'] as $day)
                                        <span class="h-2.5 w-2.5 rounded-sm {{ $day['active'] ? 'bg-brand-500' : 'bg-white/10' }} {{ $day['is_today'] ? 'ring-1 ring-white/60' : '' }}" title="{{ $day['date'] }}"></span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-bold text-white">{{ $quest['progress'] }} / {{ $quest['target'] }} episode</p>
                                <div class="mt-2 h-1.5 w-32 overflow-hidden rounded-full bg-white/10">
                                    <div class="h-full rounded-full bg-brand-500" style="width: {{ $quest['percent'] }}%"></div>
                                </div>
                                <p class="mt-1 text-[10px] text-slate-500">Hadiah {{ $quest['reward_xp'] }} XP</p>
                            </td>
                            <td class="px-4 py-3">
                                @if ($quest['claimed'])
                                    <span class="rounded bg-emerald-500/15 px-2 py-0.5 text-[10px] font-bold text-emerald-300">Sudah diklaim</span>
                                @elseif ($quest['claimable'])
                                    <span class="rounded bg-amber-500/15 px-2 py-0.5 text-[10px] font-bold text-amber-300">Siap diklaim</span>
                                @else
                                    <span class="rounded bg-white/5 px-2 py-0.5 text-[10px] font-bold text-slate-400">Berjalan</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.users.show', $user) }}" class="rounded-lg border border-white/10 px-2.5 py-1 text-[11px] font-semibold text-slate-300 hover:bg-white/10 hover:text-white">Profil</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-xs text-slate-500">Tidak ada pengguna yang cocok dengan filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/5 p-4">{{ $rows->links() }}</div>
    </div>
</section>
@endsection
