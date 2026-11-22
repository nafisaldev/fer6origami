@extends('layouts.app')

@section('title', 'Tinjau Laporan #REP-' . $report->id . ' — Admin Fer6origami')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center justify-between border-b border-white/10 pb-4">
        <div>
            <p class="text-xs uppercase tracking-wider text-brand-400 font-bold">Report Management</p>
            <h1 class="font-display text-2xl font-bold text-white">Tiket Aduan #REP-{{ $report->id }}</h1>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white">&larr; Daftar Laporan</a>
    </div>

    {{-- REPORT DETAIL CARD --}}
    <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-white/5 pb-3">
            <div>
                <span class="rounded bg-brand-500/20 px-2.5 py-0.5 text-xs font-bold text-brand-400">
                    {{ $types[$report->type] ?? $report->type }}
                </span>
                <span class="ml-2 text-xs text-slate-400">Dilaporkan {{ $report->created_at->translatedFormat('d F Y, H:i') }}</span>
            </div>
            <span class="rounded px-2.5 py-0.5 text-xs font-bold {{ $report->status === 'pending' ? 'bg-red-500/20 text-red-400' : ($report->status === 'resolved' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400') }}">
                Status: {{ $statuses[$report->status] ?? ucfirst($report->status) }}
            </span>
        </div>

        <div class="rounded-xl bg-white/5 p-4 border border-white/5 text-xs space-y-1">
            <p class="text-slate-400">Pelapor: <strong class="text-white">{{ $report->user->name ?? 'Pengunjung / Guest' }}</strong> {{ $report->user ? "({$report->user->email})" : '' }}</p>
            @if ($report->resolved_by)
                <p class="text-slate-400">Ditangani oleh: <strong class="text-emerald-400">{{ $report->resolver->name ?? 'Admin' }}</strong> pada {{ $report->resolved_at?->translatedFormat('d M Y, H:i') }}</p>
            @endif
        </div>

        <div>
            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Deskripsi Keluhan Pengguna</label>
            <div class="rounded-xl border border-white/10 bg-[#11131b] p-4 text-xs leading-relaxed text-slate-200 whitespace-pre-wrap">
                {{ $report->description }}
            </div>
        </div>

        {{-- RESOLUTION FORM --}}
        <form method="POST" action="{{ route('admin.reports.update', $report) }}" class="border-t border-white/5 pt-4 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Ubah Status Tiket</label>
                <select name="status" class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2 text-xs text-white">
                    @foreach ($statuses as $k => $v)
                        <option value="{{ $k }}" @selected($report->status === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Catatan Penyelesaian Admin</label>
                <textarea name="admin_notes" rows="3" placeholder="Tuliskan tindakan yang telah dilakukan (cth: Server streaming telah diganti, subtitle diperbaiki)..." class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs text-white">{{ old('admin_notes', $report->admin_notes) }}</textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('admin.reports.index') }}" class="rounded-xl border border-white/10 px-5 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10">Batal</a>
                <button type="submit" class="rounded-xl bg-brand-500 px-6 py-2 text-xs font-bold text-white shadow-glow hover:bg-brand-600">Perbarui Status</button>
            </div>
        </form>
    </div>
</div>
@endsection
