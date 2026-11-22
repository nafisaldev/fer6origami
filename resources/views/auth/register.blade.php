@extends('layouts.app')

@section('title', 'Daftar Akun Baru — Fer6origami')

@section('content')
    <div class="mx-auto max-w-4xl py-6 sm:py-10">
        <div class="grid gap-8 lg:grid-cols-12">
            {{-- BRAND INTRO (5 COLS) --}}
            <div class="surface-panel flex flex-col justify-between rounded-xl p-8 lg:col-span-5">
                <div>
                    <div class="mb-4 flex items-center gap-2.5">
                        <img src="{{ asset('images/emblem.png') }}" alt="Fer6origami Emblem" class="h-9 w-9 rounded-full object-cover shadow-subtle">
                        <span class="font-display text-base font-bold text-white">Fer6<span class="text-brand-500">origami</span></span>
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider text-brand-400">Akun Pengguna</span>
                    <h1 class="mt-2 font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        Bergabung dengan Fer6origami
                    </h1>
                    <p class="mt-3 text-xs leading-relaxed text-slate-400">
                        Buat akun gratis dalam hitungan detik untuk mulai menyimpan anime ke watchlist dan mengakses seluruh fitur streaming.
                    </p>
                </div>

                <div class="mt-8 space-y-3 border-t border-white/5 pt-6 text-xs text-slate-400">
                    <div class="flex items-center gap-2">
                        <span class="text-brand-400"><svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg></span>
                        <span>Gratis tanpa biaya langganan</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-brand-400"><svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg></span>
                        <span>Simpan watchlist anime tak terbatas</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-brand-400"><svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg></span>
                        <span>Pengalaman personal di semua perangkat</span>
                    </div>
                </div>
            </div>

            {{-- FORM (7 COLS) --}}
            <div class="surface-panel rounded-xl p-8 lg:col-span-7">
                <h2 class="font-display text-xl font-bold text-white">Buat Akun</h2>
                <p class="mt-1 text-xs text-slate-400">Lengkapi data di bawah ini</p>

                <form action="{{ route('register') }}" method="POST" class="mt-6 space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="mb-1.5 block font-semibold text-slate-300">Nama Lengkap</label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            class="w-full rounded-lg border border-white/10 bg-[#161924] px-3.5 py-2.5 text-xs text-white placeholder:text-slate-500 focus:border-brand-500 focus:outline-none"
                            placeholder="Nama Anda"
                        >
                        @error('name')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block font-semibold text-slate-300">Alamat Email</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            class="w-full rounded-lg border border-white/10 bg-[#161924] px-3.5 py-2.5 text-xs text-white placeholder:text-slate-500 focus:border-brand-500 focus:outline-none"
                            placeholder="nama@email.com"
                        >
                        @error('email')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block font-semibold text-slate-300">Kata Sandi</label>
                        <input
                            type="password"
                            name="password"
                            required
                            class="w-full rounded-lg border border-white/10 bg-[#161924] px-3.5 py-2.5 text-xs text-white placeholder:text-slate-500 focus:border-brand-500 focus:outline-none"
                            placeholder="Minimal 8 karakter"
                        >
                        @error('password')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block font-semibold text-slate-300">Konfirmasi Kata Sandi</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            required
                            class="w-full rounded-lg border border-white/10 bg-[#161924] px-3.5 py-2.5 text-xs text-white placeholder:text-slate-500 focus:border-brand-500 focus:outline-none"
                            placeholder="Ulangi kata sandi"
                        >
                    </div>

                    <button type="submit" class="w-full rounded-lg bg-brand-500 py-2.5 text-xs font-bold text-white shadow-subtle transition hover:bg-brand-600">
                        Daftar Akun Baru
                    </button>
                </form>

                <div class="mt-6 border-t border-white/5 pt-4 text-center text-xs text-slate-400">
                    <span>Sudah memiliki akun?</span>
                    <a href="{{ route('login') }}" class="font-bold text-brand-400 hover:underline">Masuk di sini</a>
                </div>
            </div>
        </div>
    </div>
@endsection
