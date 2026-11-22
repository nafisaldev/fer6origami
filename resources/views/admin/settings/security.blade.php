@extends('layouts.app')

@section('title', 'Keamanan & Maintenance — Admin Fer6origami')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center justify-between border-b border-white/10 pb-4">
        <div>
            <p class="text-xs uppercase tracking-wider text-brand-400 font-bold">System Management</p>
            <h1 class="font-display text-2xl font-bold text-white">Pengaturan Keamanan & Maintenance</h1>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white">&larr; Dashboard</a>
    </div>

    <form method="POST" action="{{ route('admin.settings.security') }}" class="space-y-6">
        @csrf

        {{-- MAINTENANCE MODE --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-brand-400 border-b border-white/5 pb-2">
                Mode Pemeliharaan (Maintenance Mode)
            </h2>

            <label class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/5 p-4 text-xs text-slate-300 cursor-pointer">
                <input type="checkbox" name="maintenance_mode" value="1" @checked($settings['maintenance_mode']) class="rounded border-white/20 bg-[#161924] text-red-500 focus:ring-0">
                <div>
                    <span class="font-bold text-white block">Aktifkan Maintenance Mode</span>
                    <span class="text-slate-400 text-[11px]">Jika aktif, pengunjung biasa tidak dapat mengakses streaming dan akan melihat pesan pemeliharaan. Administrator tetap dapat login.</span>
                </div>
            </label>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Pesan Pemeliharaan untuk Pengunjung</label>
                <textarea name="maintenance_message" rows="3" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white leading-relaxed">{{ $settings['maintenance_message'] }}</textarea>
            </div>
        </div>

        {{-- LOGIN RATE LIMITING & LOCKOUT --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-brand-400 border-b border-white/5 pb-2">
                Rate Limiting & Proteksi Brute-Force Login
            </h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Batas Percobaan Login Gagal (Maksimal)</label>
                    <input type="number" name="max_login_attempts" min="3" max="20" value="{{ $settings['max_login_attempts'] }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs text-white">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Durasi Lockout (Menit)</label>
                    <input type="number" name="lockout_minutes" min="1" max="1440" value="{{ $settings['lockout_minutes'] }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs text-white">
                </div>
            </div>
        </div>

        {{-- IP BLACKLIST --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-brand-400 border-b border-white/5 pb-2">
                Daftar Hitam IP Address (Blacklist)
            </h2>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">IP Blacklist (Pisahkan dengan baris baru)</label>
                <textarea name="ip_blacklist" rows="4" placeholder="192.168.1.100&#10;10.0.0.5" class="w-full rounded-xl border border-white/10 bg-black/40 p-4 font-mono text-xs text-red-400">{{ $settings['ip_blacklist'] }}</textarea>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <button type="submit" class="rounded-xl bg-brand-500 px-6 py-2.5 text-xs font-bold text-white shadow-glow hover:bg-brand-600">
                Simpan Pengaturan Keamanan
            </button>
        </div>
    </form>
</div>
@endsection
