@extends('layouts.app')

@section('title', 'Kelola Studio Animasi — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.25em] text-brand-400 font-bold">Content Management</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Studio Animasi</h1>
            <p class="mt-1 text-xs text-slate-400">Kelola informasi studio produksi (MAPPA, Ufotable, Wit, dll) dan relasinya dengan anime.</p>
        </div>
        <a href="{{ route('admin.studios.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-bold text-white shadow-glow transition hover:bg-brand-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Studio Baru</span>
        </a>
    </div>

    @include('admin.partials.delete-all-button', ['resource' => 'studios', 'label' => 'Studio'])

    {{-- SEARCH BAR --}}
    <div class="surface-panel rounded-2xl border border-white/10 p-4">
        <form method="GET" action="{{ route('admin.studios.index') }}" class="flex gap-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama studio atau negara..." class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2 text-xs text-white placeholder:text-slate-500">
            <button type="submit" class="rounded-xl bg-white/10 px-5 py-2 text-xs font-semibold text-white hover:bg-white/20">Cari</button>
        </form>
    </div>

    {{-- STUDIOS TABLE --}}
    <div class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
        @if ($studios->isEmpty())
            <p class="py-12 text-center text-xs text-slate-500">Belum ada data studio animasi.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="border-b border-white/5 bg-[#11131b] text-[10px] uppercase font-bold text-slate-400">
                        <tr>
                            <th class="px-4 py-3.5">Logo</th>
                            <th class="px-4 py-3.5">Nama Studio</th>
                            <th class="px-4 py-3.5">Negara</th>
                            <th class="px-4 py-3.5">Tahun Berdiri</th>
                            <th class="px-4 py-3.5">Jumlah Anime</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach ($studios as $studio)
                            <tr class="hover:bg-white/[0.02]">
                                <td class="px-4 py-3">
                                    <img src="{{ $studio->logo_url }}" alt="{{ $studio->name }}" class="h-9 w-9 rounded-lg object-cover bg-slate-900 border border-white/10">
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.studios.show', $studio) }}" class="font-bold text-white hover:text-brand-400">
                                        {{ $studio->name }}
                                    </a>
                                    @if ($studio->website)
                                        <a href="{{ $studio->website }}" target="_blank" class="block text-[10px] text-slate-500 hover:text-brand-400">
                                            {{ $studio->website }} &rarr;
                                        </a>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-300">{{ $studio->country ?: '-' }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ $studio->founded_year ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded bg-brand-500/20 px-2 py-0.5 text-[10px] font-bold text-brand-400">
                                        {{ $studio->animes_count }} anime
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.studios.show', $studio) }}" class="rounded bg-white/10 px-2.5 py-1 text-[11px] text-slate-300 hover:bg-white/20 hover:text-white">Detail</a>
                                        <a href="{{ route('admin.studios.edit', $studio) }}" class="rounded bg-white/10 px-2.5 py-1 text-[11px] text-white hover:bg-white/20">Edit</a>
                                        <form method="POST" action="{{ route('admin.studios.destroy', $studio) }}" onsubmit="return confirm('Hapus studio ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded bg-red-500/10 px-2.5 py-1 text-[11px] text-red-400 hover:bg-red-500 hover:text-white">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-white/5 p-4">
                {{ $studios->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
