@extends('layouts.app')

@section('title', 'Kelola Anime — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    {{-- HEADER --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.25em] text-brand-400 font-bold">Content Management</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Kelola Katalog Anime</h1>
            <p class="mt-1 text-xs text-slate-400">Tambah, edit, hapus, kelola genre, studio, dan status penayangan anime.</p>
        </div>
        <a href="{{ route('admin.animes.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-bold text-white shadow-glow transition hover:bg-brand-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Anime Baru</span>
        </a>
    </div>

    @include('admin.partials.delete-all-button', ['resource' => 'animes', 'label' => 'Anime'])

    {{-- FILTER & SEARCH BAR --}}
    <div class="surface-panel rounded-2xl border border-white/10 p-4 shadow-elevated">
        <form method="GET" action="{{ route('admin.animes.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari judul anime atau studio..." class="w-full rounded-xl border border-white/10 bg-[#161924] px-3.5 py-2 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">
            </div>

            <div>
                <select name="status" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white">
                    <option value="">Semua Status</option>
                    @foreach ($statuses as $val => $lbl)
                        <option value="{{ $val }}" @selected($status === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="genre" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white">
                    <option value="">Semua Genre</option>
                    @foreach ($genres as $g)
                        <option value="{{ $g->slug }}" @selected($selectedGenre === $g->slug)>{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <select name="sort" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white">
                    <option value="latest" @selected($sort === 'latest')>Terbaru</option>
                    <option value="oldest" @selected($sort === 'oldest')>Terlama</option>
                    <option value="score" @selected($sort === 'score')>Skor Tertinggi</option>
                    <option value="views" @selected($sort === 'views')>Paling Banyak Ditonton</option>
                    <option value="title" @selected($sort === 'title')>Judul (A-Z)</option>
                </select>
                <button type="submit" class="rounded-xl bg-white/10 px-4 py-2 text-xs font-semibold text-white transition hover:bg-white/20">
                    Filter
                </button>
            </div>
        </form>
    </div>

    {{-- MAIN TABLE WITH BULK ACTIONS --}}
    <form method="POST" action="{{ route('admin.animes.bulk') }}" id="bulkForm">
        @csrf
        <div class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
            {{-- BULK ACTION CONTROLS --}}
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/5 bg-white/[0.02] p-4">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="selectAllCheckbox" class="rounded border-white/20 bg-[#161924] text-brand-500 focus:ring-0">
                    <label for="selectAllCheckbox" class="text-xs font-semibold text-slate-300 cursor-pointer">Pilih Semua</label>
                </div>
                <div class="flex items-center gap-2">
                    <select name="bulk_action" required class="rounded-xl border border-white/10 bg-[#161924] px-3 py-1.5 text-xs text-white">
                        <option value="">-- Pilih Aksi Massal --</option>
                        <option value="status_ongoing">Ubah ke Ongoing</option>
                        <option value="status_completed">Ubah ke Completed</option>
                        <option value="feature">Jadikan Featured</option>
                        <option value="unfeature">Hapus dari Featured</option>
                        <option value="delete">Hapus Terpilih</option>
                    </select>
                    <button type="submit" onclick="return confirm('Apakah Anda yakin ingin memproses aksi massal ini?');" class="rounded-xl border border-brand-500/30 bg-brand-500/20 px-3.5 py-1.5 text-xs font-bold text-brand-400 transition hover:bg-brand-500 hover:text-white">
                        Terapkan
                    </button>
                </div>
            </div>

            @if ($animes->isEmpty())
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/>
                    </svg>
                    <h3 class="mt-3 text-sm font-bold text-white">Tidak Ada Anime Ditemukan</h3>
                    <p class="mt-1 text-xs text-slate-400">Coba ubah kata kunci filter atau tambahkan anime baru ke database.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="border-b border-white/5 bg-[#11131b] text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="w-10 px-4 py-3.5 text-center">#</th>
                                <th class="px-4 py-3.5">Anime Info</th>
                                <th class="px-4 py-3.5">Studio & Tahun</th>
                                <th class="px-4 py-3.5">Status & Rilis</th>
                                <th class="px-4 py-3.5">Score / Views</th>
                                <th class="px-4 py-3.5">Badges</th>
                                <th class="px-4 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach ($animes as $anime)
                                <tr class="transition hover:bg-white/[0.02]">
                                    <td class="px-4 py-3 text-center">
                                        <input type="checkbox" name="selected_ids[]" value="{{ $anime->id }}" class="anime-item-checkbox rounded border-white/20 bg-[#161924] text-brand-500 focus:ring-0">
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $anime->poster_image }}" alt="{{ $anime->title }}" class="h-12 w-9 shrink-0 rounded object-cover shadow">
                                            <div class="min-w-0">
                                                <a href="{{ route('admin.animes.show', $anime) }}" class="font-bold text-white transition hover:text-brand-400 truncate block">
                                                    {{ $anime->title }}
                                                </a>
                                                <p class="mt-0.5 text-[10px] text-slate-500">
                                                    {{ $anime->episodes_count }} episode • {{ $anime->genres->pluck('name')->take(2)->implode(', ') }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-slate-200">{{ $anime->studio ?: 'Unknown Studio' }}</p>
                                        <p class="text-[10px] text-slate-500">{{ $anime->year ?: '-' }} • {{ $anime->type }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-block rounded px-2 py-0.5 text-[10px] font-bold {{ $anime->status === 'ongoing' ? 'bg-amber-500/20 text-amber-400' : ($anime->status === 'completed' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-blue-500/20 text-blue-400') }}">
                                            {{ ucfirst($anime->status) }}
                                        </span>
                                        @if ($anime->release_day)
                                            <p class="mt-0.5 text-[10px] text-slate-400 font-semibold">{{ ucfirst($anime->release_day) }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-bold text-white">Rating {{ number_format((float) $anime->score, 1) }}</span>
                                        <p class="text-[10px] text-slate-500">{{ number_format($anime->views) }} views</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @if ($anime->is_featured)
                                                <span class="rounded bg-brand-500/20 px-1.5 py-0.5 text-[9px] font-extrabold text-brand-400">Featured</span>
                                            @endif
                                            @if ($anime->is_trending)
                                                <span class="rounded bg-amber-500/20 px-1.5 py-0.5 text-[9px] font-extrabold text-amber-400">Trending</span>
                                            @endif
                                            @if ($anime->is_popular)
                                                <span class="rounded bg-blue-500/20 px-1.5 py-0.5 text-[9px] font-extrabold text-blue-400">Popular</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('admin.animes.show', $anime) }}" class="rounded-lg border border-white/10 px-2 py-1 text-[11px] font-semibold text-slate-300 hover:bg-white/10 hover:text-white" title="Lihat Detail">
                                                Detail
                                            </a>
                                            <a href="{{ route('admin.animes.edit', $anime) }}" class="rounded-lg bg-white/10 px-2 py-1 text-[11px] font-semibold text-white hover:bg-white/20" title="Edit Anime">
                                                Edit
                                            </a>
                                            <button type="button" onclick="confirmDeleteAnime(this)" data-delete-url="{{ route('admin.animes.destroy', $anime) }}" data-title="{{ $anime->title }}" class="rounded-lg bg-red-500/10 px-2 py-1 text-[11px] font-semibold text-red-400 hover:bg-red-500 hover:text-white" title="Hapus">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                <div class="border-t border-white/5 p-4">
                    {{ $animes->links() }}
                </div>
            @endif
        </div>
    </form>
</div>

{{-- HIDDEN FORM FOR DELETION --}}
<form id="deleteAnimeForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
    // Toggle Select All Checkboxes
    const selectAll = document.getElementById('selectAllCheckbox');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.anime-item-checkbox').forEach(cb => cb.checked = selectAll.checked);
        });
    }

    function confirmDeleteAnime(button) {
        const title = button.dataset.title;
        if (confirm(`Apakah Anda yakin ingin menghapus anime "${title}" beserta seluruh episodenya? Tindakan ini tidak dapat dibatalkan.`)) {
            const form = document.getElementById('deleteAnimeForm');
            form.action = button.dataset.deleteUrl;
            form.submit();
        }
    }
</script>
@endsection
