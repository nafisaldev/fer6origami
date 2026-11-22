@extends('layouts.app')

@section('title', 'Kelola Pengumuman — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.25em] text-brand-400 font-bold">Communication</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Pengumuman & Update</h1>
            <p class="mt-1 text-xs text-slate-400">Buat info rilis, jadwal maintenance, event, banner popup, dan notifikasi publik.</p>
        </div>
        <a href="{{ route('admin.announcements.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-bold text-white shadow-glow transition hover:bg-brand-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Buat Pengumuman Baru</span>
        </a>
    </div>

    @include('admin.partials.delete-all-button', ['resource' => 'announcements', 'label' => 'Pengumuman'])

    {{-- FILTER BAR --}}
    <div class="surface-panel rounded-2xl border border-white/10 p-4 shadow-elevated">
        <form method="GET" action="{{ route('admin.announcements.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari judul atau isi pengumuman..." class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2 text-xs text-white placeholder:text-slate-500">
            </div>
            <div>
                <select name="category" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" @selected($selectedCategory === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <select name="status" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white">
                    <option value="">Semua Status</option>
                    @foreach ($statuses as $k => $v)
                        <option value="{{ $k }}" @selected($selectedStatus === $k)>{{ $v }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-xl bg-white/10 px-4 py-2 text-xs font-semibold text-white hover:bg-white/20">Filter</button>
            </div>
        </form>
    </div>

    {{-- ANNOUNCEMENTS TABLE --}}
    <div class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
        @if ($announcements->isEmpty())
            <p class="py-12 text-center text-xs text-slate-500">Belum ada pengumuman.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="border-b border-white/5 bg-[#11131b] text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-4 py-3.5">Judul Pengumuman</th>
                            <th class="px-4 py-3.5">Kategori</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5">Fitur</th>
                            <th class="px-4 py-3.5">Dibaca</th>
                            <th class="px-4 py-3.5">Jadwal Tayang</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach ($announcements as $ann)
                            <tr class="transition hover:bg-white/[0.02]">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        @if ($ann->is_pinned)
                                            <span class="text-amber-400" title="Pinned Announcement">Sematkan</span>
                                        @endif
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.announcements.show', $ann) }}" class="font-bold text-white hover:text-brand-400 truncate block">
                                                {{ $ann->title }}
                                            </a>
                                            <p class="text-[10px] text-slate-500">Oleh {{ $ann->creator->name ?? 'Admin' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded bg-brand-500/20 px-2 py-0.5 text-[10px] font-bold text-brand-400">
                                        {{ $ann->category }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded px-2.5 py-0.5 text-[10px] font-bold {{ $ann->status === 'published' ? 'bg-emerald-500/20 text-emerald-400' : ($ann->status === 'scheduled' ? 'bg-blue-500/20 text-blue-400' : 'bg-slate-500/20 text-slate-400') }}">
                                        {{ ucfirst($ann->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-1">
                                        @if ($ann->is_popup)
                                            <span class="rounded bg-purple-500/20 px-1.5 py-0.5 text-[9px] font-bold text-purple-400">Popup</span>
                                        @endif
                                        @if ($ann->is_pinned)
                                            <span class="rounded bg-amber-500/20 px-1.5 py-0.5 text-[9px] font-bold text-amber-400">Pinned</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-300 font-semibold">
                                    {{ number_format($ann->reads_count) }} kali
                                </td>
                                <td class="px-4 py-3 text-slate-400 text-[10px]">
                                    {{ $ann->published_at ? $ann->published_at->format('d M Y, H:i') : '-' }}
                                    @if ($ann->expires_at)
                                        <p class="text-slate-500">Exp: {{ $ann->expires_at->format('d M Y') }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.announcements.show', $ann) }}" class="rounded bg-white/10 px-2.5 py-1 text-[11px] text-slate-300 hover:bg-white/20 hover:text-white">Detail</a>
                                        <a href="{{ route('admin.announcements.edit', $ann) }}" class="rounded bg-white/10 px-2.5 py-1 text-[11px] text-white hover:bg-white/20">Edit</a>
                                        <form method="POST" action="{{ route('admin.announcements.destroy', $ann) }}" onsubmit="return confirm('Hapus pengumuman ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded bg-red-500/10 px-2 py-1 text-[11px] text-red-400 hover:bg-red-500 hover:text-white">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-white/5 p-4">
                {{ $announcements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
