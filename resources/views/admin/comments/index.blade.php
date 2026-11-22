@extends('layouts.app')

@section('title', 'Kelola Komentar — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.25em] text-brand-400 font-bold">User Management</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Moderasi Komentar</h1>
            <p class="mt-1 text-xs text-slate-400">Pantau komentar pengguna pada halaman anime dan episode, sembunyikan spam atau konten toxic.</p>
        </div>
    </div>

    @include('admin.partials.delete-all-button', ['resource' => 'comments', 'label' => 'Komentar'])

    {{-- FILTER & SEARCH BAR --}}
    <div class="surface-panel rounded-2xl border border-white/10 p-4 shadow-elevated">
        <form method="GET" action="{{ route('admin.comments.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari isi komentar atau nama penulis..." class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">
            </div>
            <div class="sm:w-48">
                <select name="status" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white">
                    <option value="">Semua Status</option>
                    @foreach ($statuses as $val => $lbl)
                        <option value="{{ $val }}" @selected($selectedStatus === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-xl bg-white/10 px-5 py-2 text-xs font-semibold text-white hover:bg-white/20">
                Filter
            </button>
        </form>
    </div>

    {{-- COMMENTS TABLE --}}
    <div class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
        @if ($comments->isEmpty())
            <p class="py-12 text-center text-xs text-slate-500">Tidak ada komentar yang sesuai filter.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="border-b border-white/5 bg-[#11131b] text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-4 py-3.5">Penulis</th>
                            <th class="px-4 py-3.5">Anime / Episode</th>
                            <th class="px-4 py-3.5">Isi Komentar</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5">Waktu</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach ($comments as $comment)
                            <tr class="transition hover:bg-white/[0.02]">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <img src="{{ $comment->user->avatar_url ?? '' }}" alt="" class="h-8 w-8 rounded-lg object-cover">
                                        <div>
                                            <span class="font-bold text-white block">{{ $comment->user->name ?? 'User Terhapus' }}</span>
                                            <span class="text-[10px] text-slate-500">{{ $comment->user->email ?? '' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-bold text-brand-400 block truncate max-w-xs">{{ $comment->anime->title ?? 'Anime' }}</span>
                                    @if ($comment->episode)
                                        <span class="text-[10px] text-slate-400">Ep {{ $comment->episode->episode_number }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-xs text-slate-200 line-clamp-2 max-w-md">{{ $comment->content }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded px-2.5 py-0.5 text-[10px] font-bold {{ $comment->status === 'published' ? 'bg-emerald-500/20 text-emerald-400' : ($comment->status === 'spam' ? 'bg-red-500/20 text-red-400' : 'bg-slate-500/20 text-slate-400') }}">
                                        {{ ucfirst($comment->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-400 text-[10px]">
                                    {{ $comment->created_at->diffForHumans() }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if ($comment->status !== 'published')
                                            <form method="POST" action="{{ route('admin.comments.status', $comment) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="published">
                                                <button type="submit" class="rounded bg-emerald-500/10 px-2 py-1 text-[10px] text-emerald-400 hover:bg-emerald-500 hover:text-white">Publish</button>
                                            </form>
                                        @endif
                                        @if ($comment->status !== 'hidden')
                                            <form method="POST" action="{{ route('admin.comments.status', $comment) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="hidden">
                                                <button type="submit" class="rounded bg-amber-500/10 px-2 py-1 text-[10px] text-amber-400 hover:bg-amber-500 hover:text-white">Hide</button>
                                            </form>
                                        @endif
                                        @if ($comment->status !== 'spam')
                                            <form method="POST" action="{{ route('admin.comments.status', $comment) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="spam">
                                                <button type="submit" class="rounded bg-red-500/10 px-2 py-1 text-[10px] text-red-400 hover:bg-red-500 hover:text-white">Spam</button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.comments.edit', $comment) }}" class="rounded bg-white/10 px-2 py-1 text-[10px] text-white hover:bg-white/20">Edit</a>
                                        <form method="POST" action="{{ route('admin.comments.destroy', $comment) }}" onsubmit="return confirm('Hapus komentar ini permanen?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded bg-red-500/10 px-2 py-1 text-[10px] text-red-400 hover:bg-red-500 hover:text-white">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-white/5 p-4">
                {{ $comments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
