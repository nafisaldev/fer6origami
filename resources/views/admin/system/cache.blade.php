@extends('layouts.app')

@section('title', 'Manajemen Cache Sistem — Admin Fer6origami')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex items-center justify-between border-b border-white/10 pb-4">
        <div>
            <p class="text-xs uppercase tracking-wider text-brand-400 font-bold">System Management</p>
            <h1 class="font-display text-2xl font-bold text-white">Manajemen Cache Aplikasi</h1>
            <p class="mt-1 text-xs text-slate-400">Bersihkan atau regenerasi berkas cache Laravel untuk memastikan perubahan data langsung aktif.</p>
        </div>
        <form method="POST" action="{{ route('admin.system.cache.clear') }}">
            @csrf
            <input type="hidden" name="type" value="all">
            <button type="submit" class="rounded-xl bg-brand-500 px-5 py-2.5 text-xs font-bold text-white shadow-glow hover:bg-brand-600">
                Bersihkan Seluruh Cache
            </button>
        </form>
    </div>

    {{-- CACHE MODULES CARDS --}}
    <div class="grid gap-4 sm:grid-cols-2">
        {{-- Application Cache --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-5 flex flex-col justify-between shadow-elevated">
            <div>
                <h2 class="text-sm font-bold text-white">Application Cache</h2>
                <p class="mt-1 text-xs text-slate-400">Data hasil cache query database, session, media catalog cache, dan token memori.</p>
            </div>
            <form method="POST" action="{{ route('admin.system.cache.clear') }}" class="mt-5 pt-3 border-t border-white/5 flex justify-end">
                @csrf
                <input type="hidden" name="type" value="application">
                <button type="submit" class="rounded-xl bg-white/10 px-4 py-2 text-xs font-semibold text-white hover:bg-white/20">
                    Clear Application Cache
                </button>
            </form>
        </div>

        {{-- Configuration Cache --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-5 flex flex-col justify-between shadow-elevated">
            <div>
                <h2 class="text-sm font-bold text-white">Configuration Cache</h2>
                <p class="mt-1 text-xs text-slate-400">Kompilasi berkas config/*.php dan variabel lingkungan .env.</p>
            </div>
            <form method="POST" action="{{ route('admin.system.cache.clear') }}" class="mt-5 pt-3 border-t border-white/5 flex justify-end">
                @csrf
                <input type="hidden" name="type" value="config">
                <button type="submit" class="rounded-xl bg-white/10 px-4 py-2 text-xs font-semibold text-white hover:bg-white/20">
                    Clear Config Cache
                </button>
            </form>
        </div>

        {{-- Route Cache --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-5 flex flex-col justify-between shadow-elevated">
            <div>
                <h2 class="text-sm font-bold text-white">Route Cache</h2>
                <p class="mt-1 text-xs text-slate-400">Pohon routing URL dan middleware yang telah dikompilasi ke memori.</p>
            </div>
            <form method="POST" action="{{ route('admin.system.cache.clear') }}" class="mt-5 pt-3 border-t border-white/5 flex justify-end">
                @csrf
                <input type="hidden" name="type" value="route">
                <button type="submit" class="rounded-xl bg-white/10 px-4 py-2 text-xs font-semibold text-white hover:bg-white/20">
                    Clear Route Cache
                </button>
            </form>
        </div>

        {{-- View / Blade Cache --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-5 flex flex-col justify-between shadow-elevated">
            <div>
                <h2 class="text-sm font-bold text-white">Compiled Views Cache</h2>
                <p class="mt-1 text-xs text-slate-400">Kompilasi template Blade HTML yang tersimpan di storage/framework/views.</p>
                <p class="mt-2 text-[10px] text-slate-500 font-mono">Ukuran direktori: {{ $storageUsage['framework'] }}</p>
            </div>
            <form method="POST" action="{{ route('admin.system.cache.clear') }}" class="mt-5 pt-3 border-t border-white/5 flex justify-end">
                @csrf
                <input type="hidden" name="type" value="view">
                <button type="submit" class="rounded-xl bg-white/10 px-4 py-2 text-xs font-semibold text-white hover:bg-white/20">
                    Clear Compiled Views
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
