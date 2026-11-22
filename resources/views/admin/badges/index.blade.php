@extends('layouts.app')

@section('title', 'Badge & Pencapaian — Admin Fer6origami')

@section('content')
<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.35em] text-slate-500">User Management</p>
            <h1 class="mt-2 font-display text-4xl font-bold text-white">Badge & Pencapaian</h1>
            <p class="mt-2 text-sm text-slate-400">Lihat pencapaian otomatis dan berikan lencana manual kepada pengguna.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="rounded-2xl border border-white/10 px-5 py-3 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white">
            Kelola Users
        </a>
    </div>

    <div class="surface-panel rounded-2xl border border-white/10 p-5 shadow-elevated">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Katalog Lencana</h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($catalog as $badge)
                <div class="rounded-2xl border border-white/5 bg-white/[0.02] p-4">
                    <div class="flex items-start gap-3">
                        <span class="shrink-0 text-amber-400">{!! $badge['icon'] !!}</span>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-white">{{ $badge['name'] }}</p>
                            <p class="mt-1 text-[11px] leading-snug text-slate-400">{{ $badge['description'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="surface-panel rounded-2xl border border-white/10 p-4 shadow-elevated">
        <form method="GET" action="{{ route('admin.badges.index') }}" class="flex flex-col gap-2 sm:flex-row">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau email pengguna..." class="w-full rounded-xl border border-white/10 bg-[#161924] px-3.5 py-2 text-xs text-white placeholder:text-slate-500 focus:border-brand-500 sm:w-80">
            <button type="submit" class="rounded-xl bg-white/10 px-4 py-2 text-xs font-semibold text-white transition hover:bg-white/20">Filter</button>
        </form>
    </div>

    @include('admin.partials.delete-all-button', ['resource' => 'badges', 'label' => 'Lencana Manual'])

    <div class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm text-slate-300">
                <thead class="border-b border-white/5 bg-[#11131b] text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-4 py-3.5">Pengguna</th>
                        <th class="px-4 py-3.5">Lencana Terbuka</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($rows as $row)
                        <tr class="transition hover:bg-white/[0.02]">
                            <td class="px-4 py-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <img src="{{ $row['user']->avatar_url }}" alt="{{ $row['user']->name }}" class="h-9 w-9 shrink-0 rounded-xl object-cover ring-1 ring-white/10">
                                    <div class="min-w-0">
                                        <p class="truncate font-bold text-white">{{ $row['user']->name }}</p>
                                        <p class="truncate text-[10px] text-slate-500">Lv. {{ $row['user']->level }} · {{ $row['user']->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse (collect($row['badges'])->where('unlocked', true) as $badge)
                                        <span class="rounded-lg border border-amber-500/20 bg-amber-500/10 px-2 py-1 text-[10px] font-bold text-amber-300">
                                            {{ $badge['name'] }}{{ $badge['manual'] ? ' (manual)' : '' }}
                                        </span>
                                    @empty
                                        <span class="text-[11px] text-slate-500">Belum ada lencana terbuka.</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.badges.edit', $row['user']) }}" class="rounded-lg border border-white/10 px-2.5 py-1 text-[11px] font-semibold text-slate-300 hover:bg-white/10 hover:text-white">Kelola</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-10 text-center text-xs text-slate-500">Tidak ada pengguna yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/5 p-4">{{ $rows->links() }}</div>
    </div>
</section>
@endsection
