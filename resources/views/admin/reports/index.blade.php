@extends('layouts.app')

@section('title', 'Laporan Pengguna — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.25em] text-brand-400 font-bold">User Management</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Laporan Masalah Pengguna</h1>
            <p class="mt-1 text-xs text-slate-400">Kelola tiket aduan streaming rusak, subtitle bermasalah, dan laporan konten.</p>
        </div>
    </div>

    @include('admin.partials.delete-all-button', ['resource' => 'reports', 'label' => 'Laporan'])

    {{-- FILTER BAR --}}
    <div class="surface-panel rounded-2xl border border-white/10 p-4 shadow-elevated">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari keluhan atau nama pelapor..." class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2 text-xs text-white placeholder:text-slate-500">
            </div>
            <div>
                <select name="status" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white">
                    <option value="">Semua Status</option>
                    @foreach ($statuses as $val => $lbl)
                        <option value="{{ $val }}" @selected($selectedStatus === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <select name="type" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white">
                    <option value="">Semua Kategori</option>
                    @foreach ($types as $val => $lbl)
                        <option value="{{ $val }}" @selected($selectedType === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-xl bg-white/10 px-4 py-2 text-xs font-semibold text-white hover:bg-white/20">Filter</button>
            </div>
        </form>
    </div>

    {{-- REPORTS TABLE --}}
    <div class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
        @if ($reports->isEmpty())
            <p class="py-12 text-center text-xs text-slate-500">Tidak ada laporan masalah yang sesuai.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="border-b border-white/5 bg-[#11131b] text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-4 py-3.5">ID / Pelapor</th>
                            <th class="px-4 py-3.5">Jenis Aduan</th>
                            <th class="px-4 py-3.5">Deskripsi Masalah</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5">Waktu Lapor</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach ($reports as $rep)
                            <tr class="transition hover:bg-white/[0.02]">
                                <td class="px-4 py-3">
                                    <span class="font-mono text-[10px] text-slate-500 block">#REP-{{ $rep->id }}</span>
                                    <span class="font-bold text-white">{{ $rep->user->name ?? 'Guest' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded bg-brand-500/20 px-2 py-0.5 text-[10px] font-bold text-brand-400">
                                        {{ $types[$rep->type] ?? $rep->type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-xs text-slate-200 line-clamp-2 max-w-md">{{ $rep->description }}</p>
                                    @if ($rep->admin_notes)
                                        <p class="text-[10px] text-emerald-400 mt-0.5">Catatan: {{ $rep->admin_notes }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded px-2.5 py-0.5 text-[10px] font-bold {{ $rep->status === 'pending' ? 'bg-red-500/20 text-red-400' : ($rep->status === 'resolved' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400') }}">
                                        {{ $statuses[$rep->status] ?? ucfirst($rep->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-400 text-[10px]">
                                    {{ $rep->created_at->diffForHumans() }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.reports.show', $rep) }}" class="rounded bg-brand-500 px-2.5 py-1 text-[11px] font-bold text-white hover:bg-brand-600">
                                            Tinjau
                                        </a>
                                        <form method="POST" action="{{ route('admin.reports.destroy', $rep) }}" onsubmit="return confirm('Hapus tiket laporan ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded bg-red-500/10 px-2 py-1 text-[11px] text-red-400 hover:bg-red-500 hover:text-white">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-white/5 p-4">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
