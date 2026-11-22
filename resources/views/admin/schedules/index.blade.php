@extends('layouts.app')

@section('title', 'Jadwal Rilis Anime — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.25em] text-brand-400 font-bold">Content Management</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Jadwal Rilis Anime Mingguan</h1>
            <p class="mt-1 text-xs text-slate-400">Atur hari penayangan episode baru untuk anime ongoing dari Senin hingga Minggu.</p>
        </div>
    </div>

    {{-- QUICK ASSIGN BAR --}}
    <div class="surface-panel rounded-2xl border border-white/10 p-5 shadow-elevated">
        <h2 class="text-xs font-bold uppercase tracking-wider text-brand-400 mb-3">Atur Jadwal Rilis Anime</h2>
        <form method="POST" action="{{ route('admin.schedules.update') }}" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <div class="flex-1">
                <select name="anime_id" required class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2 text-xs text-white">
                    <option value="">-- Pilih Anime Ongoing --</option>
                    @foreach ($unscheduled as $u)
                        <option value="{{ $u->id }}">{{ $u->title }} (Belum Ada Jadwal)</option>
                    @endforeach
                    @foreach ($schedules as $dayKey => $items)
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}">{{ $item->title }} (Saat Ini: {{ ucfirst($dayKey) }})</option>
                        @endforeach
                    @endforeach
                </select>
            </div>
            <div class="sm:w-56">
                <select name="release_day" required class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2 text-xs text-white">
                    <option value="">-- Tetapkan Hari --</option>
                    @foreach ($days as $val => $lbl)
                        <option value="{{ $val }}">{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-xl bg-brand-500 px-6 py-2 text-xs font-bold text-white shadow-glow hover:bg-brand-600">
                Simpan Jadwal
            </button>
        </form>
    </div>

    {{-- 7 DAYS GRID --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-7">
        @foreach ($days as $dayKey => $dayLabel)
            @php
                $animesThisDay = $schedules[$dayKey] ?? collect();
            @endphp
            <div class="surface-panel rounded-2xl border border-white/10 p-4 flex flex-col">
                <div class="flex items-center justify-between border-b border-white/5 pb-2 mb-3">
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-brand-400">
                        {{ explode(' ', $dayLabel)[0] }}
                    </h3>
                    <span class="rounded bg-white/10 px-2 py-0.5 text-[10px] font-bold text-slate-300">
                        {{ $animesThisDay->count() }}
                    </span>
                </div>

                <div class="space-y-2.5 flex-1">
                    @forelse ($animesThisDay as $anime)
                        <div class="rounded-xl border border-white/5 bg-white/5 p-2 transition hover:bg-white/10">
                            <div class="flex items-center gap-2">
                                <img src="{{ $anime->poster_image }}" alt="" class="h-9 w-7 shrink-0 rounded object-cover">
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('admin.animes.show', $anime) }}" class="text-[11px] font-bold text-white truncate block hover:text-brand-400">
                                        {{ $anime->title }}
                                    </a>
                                    <span class="text-[9px] text-slate-400">{{ $anime->studio ?: 'Anime' }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="py-6 text-center text-[10px] text-slate-500">Tidak ada jadwal</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    {{-- UNSCHEDULED ONGOING ANIME --}}
    @if ($unscheduled->isNotEmpty())
        <div class="surface-panel rounded-2xl border border-white/10 p-5">
            <h2 class="text-xs font-bold uppercase tracking-wider text-amber-400 mb-3 border-b border-white/5 pb-2">
                Anime Ongoing Belum Memiliki Jadwal ({{ $unscheduled->count() }})
            </h2>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($unscheduled as $u)
                    <div class="flex items-center gap-3 rounded-xl border border-white/5 bg-white/5 p-3">
                        <img src="{{ $u->poster_image }}" alt="" class="h-10 w-8 rounded object-cover">
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('admin.animes.show', $u) }}" class="font-bold text-xs text-white truncate block hover:text-brand-400">
                                {{ $u->title }}
                            </a>
                            <p class="text-[10px] text-amber-400 font-semibold">Belum dijadwalkan</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
