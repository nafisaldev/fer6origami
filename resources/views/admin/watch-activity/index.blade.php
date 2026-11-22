@extends('layouts.app')

@section('title', 'Riwayat & Watchlist — Admin Fer6origami')

@section('content')
<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.35em] text-slate-500">User Management</p>
            <h1 class="mt-2 font-display text-4xl font-bold text-white">Riwayat & Watchlist</h1>
            <p class="mt-2 text-sm text-slate-400">Lihat dan hapus aktivitas tontonan serta daftar simpanan pengguna.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="rounded-2xl border border-white/10 px-5 py-3 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white">
            Kelola Users
        </a>
    </div>

    <div class="surface-panel rounded-2xl border border-white/10 p-4 shadow-elevated">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="inline-flex rounded-xl border border-white/10 bg-[#11131b] p-1">
                <a href="{{ route('admin.watch-activity.index', ['type' => 'history']) }}" class="rounded-lg px-4 py-2 text-xs font-bold transition {{ $type === 'history' ? 'bg-brand-500 text-white' : 'text-slate-400 hover:text-white' }}">
                    Riwayat Tonton
                </a>
                <a href="{{ route('admin.watch-activity.index', ['type' => 'watchlist']) }}" class="rounded-lg px-4 py-2 text-xs font-bold transition {{ $type === 'watchlist' ? 'bg-brand-500 text-white' : 'text-slate-400 hover:text-white' }}">
                    Watchlist
                </a>
            </div>
            <form method="GET" action="{{ route('admin.watch-activity.index') }}" class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto">
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari anime atau pengguna..." class="w-full rounded-xl border border-white/10 bg-[#161924] px-3.5 py-2 text-xs text-white placeholder:text-slate-500 focus:border-brand-500 sm:w-64">
                <button type="submit" class="rounded-xl bg-white/10 px-4 py-2 text-xs font-semibold text-white transition hover:bg-white/20">Filter</button>
            </form>
        </div>
    </div>

    @if ($type === 'history')
        @include('admin.partials.delete-all-button', ['resource' => 'watch-histories', 'label' => 'Riwayat Tonton'])
    @else
        @include('admin.partials.delete-all-button', ['resource' => 'watchlists', 'label' => 'Watchlist'])
    @endif

    <div class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm text-slate-300">
                <thead class="border-b border-white/5 bg-[#11131b] text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-4 py-3.5">Pengguna</th>
                        <th class="px-4 py-3.5">{{ $type === 'history' ? 'Episode Ditonton' : 'Anime Disimpan' }}</th>
                        <th class="px-4 py-3.5">Detail</th>
                        <th class="px-4 py-3.5">Waktu</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($items as $item)
                        <tr class="transition hover:bg-white/[0.02]">
                            <td class="px-4 py-3">
                                @if ($item->user)
                                    <p class="font-bold text-white">{{ $item->user->name }}</p>
                                    <p class="text-[10px] text-slate-500">{{ $item->user->email }}</p>
                                @else
                                    <span class="text-slate-500">Pengguna dihapus</span>
                                @endif
                            </td>
                            @if ($type === 'history')
                                <td class="px-4 py-3">
                                    <p class="font-bold text-white">{{ $item->anime_title ?: ($item->anime->title ?? 'Anime') }}</p>
                                    <p class="text-[10px] text-slate-500">{{ $item->anime_slug ?: ($item->anime->slug ?? '-') }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-xs text-slate-300">{{ $item->episode_title ?: ($item->episode ? 'Episode ' . $item->episode->episode_number : 'Series') }}</p>
                                    <p class="text-[10px] text-slate-500">{{ round($item->duration_seconds / 60) }} menit · {{ $item->is_completed ? 'Selesai' : 'Belum selesai' }}</p>
                                </td>
                            @else
                                <td class="px-4 py-3">
                                    <p class="font-bold text-white">{{ $item->anime_title }}</p>
                                    <p class="text-[10px] text-slate-500">{{ $item->anime_api_id }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-xs text-slate-300">{{ $item->status ?: 'Status tidak diketahui' }}</p>
                                    <p class="text-[10px] text-slate-500">{{ $item->type ?: 'Anime' }} · Skor {{ $item->score ?: '-' }}</p>
                                </td>
                            @endif
                            <td class="px-4 py-3 text-xs text-slate-400">{{ $item->created_at->diffForHumans() }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ $type === 'history' ? route('admin.watch-histories.destroy', $item) : route('admin.watchlists.destroy', $item) }}" onsubmit="return confirm('Hapus data ini secara permanen?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-rose-500/20 px-2.5 py-1 text-[11px] font-semibold text-rose-300 hover:bg-rose-500/10">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-xs text-slate-500">Belum ada data yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/5 p-4">{{ $items->links() }}</div>
    </div>
</section>
@endsection
