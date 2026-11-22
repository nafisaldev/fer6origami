@extends('layouts.app')

@section('title', 'Blacklist Anime — Admin Fer6origami')

@section('content')
<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.35em] text-slate-500">Content Management</p>
            <h1 class="mt-2 font-display text-4xl font-bold text-white">Blacklist Anime</h1>
            <p class="mt-2 text-sm text-slate-400">Judul dalam daftar ini tidak akan muncul lagi di katalog, meski berasal dari API.</p>
        </div>
        <a href="{{ route('admin.blacklist.create') }}" class="rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-slate-200">
            Tambah Blacklist
        </a>
    </div>

    <div class="surface-panel rounded-2xl border border-white/10 p-4 shadow-elevated">
        <form method="GET" action="{{ route('admin.blacklist.index') }}" class="flex flex-col gap-2 sm:flex-row">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari judul atau slug..." class="w-full rounded-xl border border-white/10 bg-[#161924] px-3.5 py-2 text-xs text-white placeholder:text-slate-500 focus:border-brand-500 sm:w-80">
            <button type="submit" class="rounded-xl bg-white/10 px-4 py-2 text-xs font-semibold text-white transition hover:bg-white/20">Filter</button>
        </form>
    </div>

    @include('admin.partials.delete-all-button', ['resource' => 'blacklist', 'label' => 'Blacklist Anime'])

    <div class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm text-slate-300">
                <thead class="border-b border-white/5 bg-[#11131b] text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-4 py-3.5">Judul</th>
                        <th class="px-4 py-3.5">Slug</th>
                        <th class="px-4 py-3.5">Ditambahkan Oleh</th>
                        <th class="px-4 py-3.5">Waktu</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($items as $item)
                        <tr class="transition hover:bg-white/[0.02]">
                            <td class="px-4 py-3 font-bold text-white">{{ $item->title }}</td>
                            <td class="px-4 py-3 text-xs text-slate-400">{{ $item->slug }}</td>
                            <td class="px-4 py-3 text-xs text-slate-400">{{ $item->deletedBy->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs text-slate-400">{{ $item->created_at->diffForHumans() }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex flex-wrap justify-end gap-1.5">
                                    <form method="POST" action="{{ route('admin.blacklist.restore', $item) }}" onsubmit="return confirm('Pulihkan {{ addslashes($item->title) }} ke katalog?');">
                                        @csrf
                                        <button type="submit" class="rounded-lg border border-emerald-500/20 px-2.5 py-1 text-[11px] font-semibold text-emerald-300 hover:bg-emerald-500/10">Pulihkan</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.blacklist.destroy', $item) }}" onsubmit="return confirm('Hapus {{ addslashes($item->title) }} dari blacklist?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-rose-500/20 px-2.5 py-1 text-[11px] font-semibold text-rose-300 hover:bg-rose-500/10">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-xs text-slate-500">Belum ada anime dalam blacklist.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/5 p-4">{{ $items->links() }}</div>
    </div>
</section>
@endsection
