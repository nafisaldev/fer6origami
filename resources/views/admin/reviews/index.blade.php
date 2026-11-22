@extends('layouts.app')

@section('title', 'Kelola Review & Rating — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.25em] text-brand-400 font-bold">User Management</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Review & Rating Anime</h1>
            <p class="mt-1 text-xs text-slate-400">Pantau ulasan dan rating bintang 1-10 yang diberikan pengguna pada anime.</p>
        </div>
    </div>

    @include('admin.partials.delete-all-button', ['resource' => 'reviews', 'label' => 'Review'])

    {{-- FILTER BAR --}}
    <div class="surface-panel rounded-2xl border border-white/10 p-4 shadow-elevated">
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari isi ulasan, anime, atau pengguna..." class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2 text-xs text-white placeholder:text-slate-500">
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
                <select name="rating" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white">
                    <option value="">Semua Rating</option>
                    @for ($r = 10; $r >= 1; $r--)
                        <option value="{{ $r }}" @selected($selectedRating == $r)>{{ $r }}/10</option>
                    @endfor
                </select>
                <button type="submit" class="rounded-xl bg-white/10 px-4 py-2 text-xs font-semibold text-white hover:bg-white/20">Filter</button>
            </div>
        </form>
    </div>

    {{-- REVIEWS TABLE --}}
    <div class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
        @if ($reviews->isEmpty())
            <p class="py-12 text-center text-xs text-slate-500">Belum ada review anime.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="border-b border-white/5 bg-[#11131b] text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-4 py-3.5">Reviewer</th>
                            <th class="px-4 py-3.5">Anime</th>
                            <th class="px-4 py-3.5">Rating</th>
                            <th class="px-4 py-3.5">Ulasan</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach ($reviews as $rev)
                            <tr class="transition hover:bg-white/[0.02]">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <img src="{{ $rev->user->avatar_url ?? '' }}" alt="" class="h-8 w-8 rounded-lg object-cover">
                                        <div>
                                            <span class="font-bold text-white block">{{ $rev->user->name ?? 'User' }}</span>
                                            <span class="text-[10px] text-slate-500">{{ $rev->user->email ?? '' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-bold text-white block truncate max-w-xs">{{ $rev->anime->title ?? 'Anime' }}</span>
                                    <span class="text-[10px] text-slate-500">{{ $rev->anime->studio ?? '' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded bg-amber-500/20 px-2 py-0.5 text-xs font-extrabold text-amber-400">
                                        Rating {{ $rev->rating }}/10
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($rev->title)
                                        <p class="font-bold text-white text-xs">{{ $rev->title }}</p>
                                    @endif
                                    <p class="text-xs text-slate-300 line-clamp-2 max-w-md">{{ $rev->content }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded px-2.5 py-0.5 text-[10px] font-bold {{ $rev->status === 'published' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-500/20 text-slate-400' }}">
                                        {{ ucfirst($rev->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if ($rev->status === 'published')
                                            <form method="POST" action="{{ route('admin.reviews.status', $rev) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="hidden">
                                                <button type="submit" class="rounded bg-amber-500/10 px-2.5 py-1 text-[11px] text-amber-400 hover:bg-amber-500 hover:text-white">Sembunyikan</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.reviews.status', $rev) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="published">
                                                <button type="submit" class="rounded bg-emerald-500/10 px-2.5 py-1 text-[11px] text-emerald-400 hover:bg-emerald-500 hover:text-white">Tampilkan</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.reviews.destroy', $rev) }}" onsubmit="return confirm('Hapus review ini?');" class="inline">
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
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
