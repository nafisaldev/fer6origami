@extends('layouts.app')

@section('title', 'Kelola Lencana: ' . $user->name . ' — Admin Fer6origami')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.35em] text-slate-500">User Management</p>
            <h1 class="mt-2 font-display text-3xl font-bold text-white">Kelola Lencana</h1>
            <p class="mt-2 text-sm text-slate-400">{{ $user->name }} · Lv. {{ $user->level }} · {{ number_format($user->xp) }} XP</p>
        </div>
        <a href="{{ route('admin.badges.index') }}" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/5 hover:text-white">&larr; Kembali</a>
    </div>

    <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated">
        <form method="POST" action="{{ route('admin.badges.update', $user) }}" class="space-y-5">
            @csrf
            @method('PATCH')

            <div class="rounded-xl border border-white/5 bg-white/[0.02] p-4 text-xs leading-relaxed text-slate-400">
                Lencana otomatis dihitung dari aktivitas pengguna dan tidak bisa dicabut di sini. Centang lencana untuk memberi penghargaan manual tambahan; lencana yang tidak dicentang akan kembali ke status otomatisnya.
            </div>

            <div class="space-y-3">
                @foreach ($catalog as $slug => $badge)
                    @php
                        $state = collect($badges)->firstWhere('id', $slug);
                    @endphp
                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-white/5 bg-white/[0.02] p-4 transition hover:border-white/15">
                        <input type="checkbox" name="badges[]" value="{{ $slug }}" @checked($state['manual']) class="mt-2 h-4 w-4 shrink-0 rounded border-white/20 bg-[#161924] text-brand-500 focus:ring-brand-500">
                        <span class="shrink-0 text-amber-400">{!! $badge['icon'] !!}</span>
                        <span class="min-w-0 flex-1">
                            <span class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-bold text-white">{{ $badge['name'] }}</span>
                                @if ($state['auto'])
                                    <span class="rounded bg-emerald-500/15 px-1.5 py-0.5 text-[10px] font-bold text-emerald-300">Otomatis</span>
                                @elseif ($state['manual'])
                                    <span class="rounded bg-amber-500/15 px-1.5 py-0.5 text-[10px] font-bold text-amber-300">Manual</span>
                                @else
                                    <span class="rounded bg-white/5 px-1.5 py-0.5 text-[10px] font-bold text-slate-400">Terkunci</span>
                                @endif
                            </span>
                            <span class="mt-1 block text-[11px] leading-snug text-slate-400">{{ $badge['description'] }}</span>
                        </span>
                    </label>
                @endforeach
            </div>

            @error('badges') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
            @error('badges.*') <p class="text-xs text-red-400">{{ $message }}</p> @enderror

            <div class="flex flex-wrap justify-end gap-2 border-t border-white/5 pt-4">
                <a href="{{ route('admin.badges.index') }}" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/5">Batal</a>
                <button type="submit" class="rounded-xl bg-brand-500 px-5 py-2 text-xs font-bold text-white hover:bg-brand-400">Simpan Lencana</button>
            </div>
        </form>
    </div>
</div>
@endsection
