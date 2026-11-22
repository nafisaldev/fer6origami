@extends('layouts.app')

@section('title', 'Pengaturan Umum — Admin Fer6origami')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center justify-between border-b border-white/10 pb-4">
        <div>
            <p class="text-xs uppercase tracking-wider text-brand-400 font-bold">System Management</p>
            <h1 class="font-display text-2xl font-bold text-white">Pengaturan Umum Website</h1>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white">&larr; Dashboard</a>
    </div>

    <form method="POST" action="{{ route('admin.settings.general') }}" class="space-y-6">
        @csrf

        {{-- SITE IDENTITY --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-brand-400 border-b border-white/5 pb-2">
                Identitas Website
            </h2>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Nama Website <span class="text-brand-500">*</span></label>
                <input type="text" name="site_name" value="{{ $settings['site_name'] }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Tagline / Slogan Website <span class="text-brand-500">*</span></label>
                <input type="text" name="site_tagline" value="{{ $settings['site_tagline'] }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Email Dukungan & Kontak <span class="text-brand-500">*</span></label>
                <input type="email" name="site_email" value="{{ $settings['site_email'] }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
            </div>
        </div>

        {{-- USER REGISTRATION SETTINGS --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-brand-400 border-b border-white/5 pb-2">
                Pendaftaran & Pengguna Baru
            </h2>

            <label class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/5 p-4 text-xs text-slate-300 cursor-pointer">
                <input type="checkbox" name="allow_registration" value="1" @checked($settings['allow_registration']) class="rounded border-white/20 bg-[#161924] text-brand-500 focus:ring-0">
                <div>
                    <span class="font-bold text-white block">Izinkan Pendaftaran Akun Pengguna Baru</span>
                    <span class="text-slate-400 text-[11px]">Bila dinonaktifkan, formulir registrasi publik akan ditutup sementara.</span>
                </div>
            </label>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Role Bawaan untuk Pendaftar Baru</label>
                <select name="default_role" class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2 text-xs text-white">
                    @foreach ($roles as $r)
                        @if ($r->slug !== 'super_admin')
                            <option value="{{ $r->slug }}" @selected($settings['default_role'] === $r->slug)>{{ $r->name }} ({{ $r->slug }})</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <button type="submit" class="rounded-xl bg-brand-500 px-6 py-2.5 text-xs font-bold text-white shadow-glow hover:bg-brand-600">
                Simpan Pengaturan Umum
            </button>
        </div>
    </form>
</div>
@endsection
