@extends('layouts.app')

@section('title', 'Kelola Episode — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    {{-- HEADER --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.25em] text-brand-400 font-bold">Content Management</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Kelola Episode Anime</h1>
            <p class="mt-1 text-xs text-slate-400">Atur nomor episode, video streaming, server cadangan, dan status publikasi.</p>
        </div>
        <a href="{{ route('admin.episodes.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-bold text-white shadow-glow transition hover:bg-brand-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Upload Episode Baru</span>
        </a>
    </div>

    @include('admin.partials.delete-all-button', ['resource' => 'episodes', 'label' => 'Episode'])

    {{-- FILTER & SEARCH BAR --}}
    <div class="surface-panel rounded-2xl border border-white/10 p-4 shadow-elevated">
        <form method="GET" action="{{ route('admin.episodes.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari judul episode atau nama anime..." class="w-full rounded-xl border border-white/10 bg-[#161924] px-3.5 py-2 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">
            </div>

            <div>
                <select name="anime_id" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white">
                    <option value="">Semua Anime</option>
                    @foreach ($animes as $a)
                        <option value="{{ $a->id }}" @selected($selectedAnimeId == $a->id)>{{ $a->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <select name="status" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white">
                    <option value="">Semua Status</option>
                    <option value="published" @selected($selectedStatus === 'published')>Published</option>
                    <option value="draft" @selected($selectedStatus === 'draft')>Draft / Hidden</option>
                </select>
                <button type="submit" class="rounded-xl bg-white/10 px-4 py-2 text-xs font-semibold text-white transition hover:bg-white/20">
                    Filter
                </button>
            </div>
        </form>
    </div>

    {{-- EPISODES TABLE --}}
    <div class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
        @if ($episodes->isEmpty())
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="mt-3 text-sm font-bold text-white">Tidak Ada Episode</h3>
                <p class="mt-1 text-xs text-slate-400">Belum ada episode yang sesuai dengan filter pencarian ini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="border-b border-white/5 bg-[#11131b] text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-4 py-3.5">Anime Seri</th>
                            <th class="px-4 py-3.5">Episode</th>
                            <th class="px-4 py-3.5">Server Streaming</th>
                            <th class="px-4 py-3.5">Tanggal Tayang</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach ($episodes as $ep)
                            <tr class="transition hover:bg-white/[0.02]">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $ep->anime->poster_image ?? '' }}" alt="" class="h-10 w-8 shrink-0 rounded object-cover">
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.animes.show', $ep->anime_id) }}" class="font-bold text-white hover:text-brand-400 truncate block">
                                                {{ $ep->anime->title ?? 'Anime Terhapus' }}
                                            </a>
                                            <p class="text-[10px] text-slate-500">{{ $ep->anime->studio ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-block rounded bg-brand-500/20 px-2 py-0.5 text-[10px] font-extrabold text-brand-400">
                                        Eps {{ $ep->episode_number }}
                                    </span>
                                    <p class="mt-1 font-semibold text-white">{{ $ep->title }}</p>
                                    @if ($ep->duration_minutes)
                                        <span class="text-[10px] text-slate-400">{{ $ep->duration_minutes }} menit</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-bold text-white">{{ is_array($ep->servers) ? count($ep->servers) : 0 }} server</span>
                                    @if (!empty($ep->video_url))
                                        <span class="block text-[10px] text-emerald-400 font-semibold truncate max-w-xs">Primary Active</span>
                                    @else
                                        <span class="block text-[10px] text-amber-400 font-semibold">No URL</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-400">
                                    {{ $ep->aired_at ? $ep->aired_at->translatedFormat('d M Y, H:i') : '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded px-2.5 py-0.5 text-[10px] font-bold {{ $ep->is_published ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-500/20 text-slate-400' }}">
                                        {{ $ep->is_published ? 'Published' : 'Draft' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.episodes.show', $ep) }}" class="rounded-lg border border-white/10 px-2.5 py-1 text-[11px] font-semibold text-slate-300 hover:bg-white/10 hover:text-white">
                                            Detail
                                        </a>
                                        <a href="{{ route('admin.episodes.edit', $ep) }}" class="rounded-lg bg-white/10 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-white/20">
                                            Edit
                                        </a>
                                        <button type="button" onclick="confirmDeleteEpisode(this)" data-delete-url="{{ route('admin.episodes.destroy', $ep) }}" data-title="{{ $ep->title }}" class="rounded-lg bg-red-500/10 px-2.5 py-1 text-[11px] font-semibold text-red-400 hover:bg-red-500 hover:text-white">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-white/5 p-4">
                {{ $episodes->links() }}
            </div>
        @endif
    </div>
</div>

<form id="deleteEpisodeForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
    function confirmDeleteEpisode(button) {
        const title = button.dataset.title;
        if (confirm(`Apakah Anda yakin ingin menghapus episode "${title}"? Tindakan ini tidak dapat dibatalkan.`)) {
            const form = document.getElementById('deleteEpisodeForm');
            form.action = button.dataset.deleteUrl;
            form.submit();
        }
    }
</script>
@endsection
