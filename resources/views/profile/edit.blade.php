@extends('layouts.app')

@section('title', 'Edit Profil — Fer6origami')

@section('content')
<div class="mx-auto max-w-3xl space-y-8">
    <div class="flex items-center justify-between border-b border-white/10 pb-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-brand-400">Pengaturan Akun</p>
            <h1 class="font-display text-2xl font-extrabold text-white sm:text-3xl">Edit Profil Pengguna</h1>
            <p class="mt-1 text-xs text-slate-400">Perbarui identitas profil, foto avatar, biografi, dan keamanan akun Anda.</p>
        </div>
        <a href="{{ route('profile.show') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white">
            &larr; Kembali ke Profil
        </a>
    </div>

    {{-- CURRENT LEVEL HIGHLIGHT CARD --}}
    <div class="surface-panel flex items-center justify-between rounded-2xl border border-white/10 p-4 shadow-elevated">
        <div class="flex items-center gap-3">
            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-12 w-12 rounded-xl object-cover ring-2 ring-white/10">
            <div>
                <div class="flex items-center gap-2">
                    <p class="text-sm font-bold text-white">{{ $user->name }}</p>
                    <span class="rounded px-2 py-0.5 text-[10px] font-black {{ $levelInfo['badge_class'] }}">
                        Lv. {{ $levelInfo['level'] }}
                    </span>
                </div>
                <p class="text-xs text-slate-400">{{ $levelInfo['rank_name'] }} • <strong class="text-brand-400">{{ number_format($levelInfo['current_xp']) }} XP</strong></p>
            </div>
        </div>
        <div class="hidden sm:block text-right">
            <span class="text-[11px] text-slate-400">Target Level {{ $levelInfo['level'] + 1 }}</span>
            <p class="text-xs font-bold text-white">{{ number_format($levelInfo['xp_for_next']) }} XP lagi</p>
        </div>
    </div>

    {{-- FORM 1: INFORMASI PROFIL & AVATAR --}}
    <div class="surface-panel rounded-3xl border border-white/10 p-6 shadow-elevated">
        <h2 class="text-base font-bold text-white border-b border-white/10 pb-3 mb-5">
            Informasi Profil & Avatar
        </h2>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Avatar Upload with Live Preview --}}
            <div>
                <label class="block text-xs font-bold text-slate-300 mb-2">Foto Avatar Profil</label>
                <div class="flex items-center gap-4">
                    <img id="avatar-preview" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-16 w-16 rounded-2xl object-cover ring-2 ring-brand-500/40">
                    <div class="flex-1">
                        <input
                            type="file"
                            name="avatar"
                            id="avatar-input"
                            accept="image/jpeg,image/png,image/webp,image/jpg"
                            class="block w-full text-xs text-slate-400 file:mr-4 file:rounded-xl file:border-0 file:bg-white/10 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-white/20 cursor-pointer"
                        >
                        <p class="mt-1 text-[10px] text-slate-500">Format yang didukung: JPG, PNG, WEBP. Maksimal 2MB.</p>
                    </div>
                </div>
                @error('avatar')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nama --}}
            <div>
                <label for="name" class="block text-xs font-bold text-slate-300 mb-1.5">Nama Tampilan</label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name', $user->name) }}"
                    required
                    class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2.5 text-xs text-white placeholder:text-slate-500 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                >
                @error('name')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email (Read Only) --}}
            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1.5">Alamat Email</label>
                <input
                    type="email"
                    value="{{ $user->email }}"
                    disabled
                    class="w-full rounded-xl border border-white/5 bg-white/[0.03] px-4 py-2.5 text-xs text-slate-400 cursor-not-allowed"
                >
                <p class="mt-1 text-[10px] text-slate-500">Alamat email terdaftar tidak dapat diubah secara langsung.</p>
            </div>

            {{-- Anime Favorit --}}
            <div>
                <label for="favorite_anime" class="block text-xs font-bold text-slate-300 mb-1.5">Anime Favorit Sepanjang Masa</label>
                <input
                    type="text"
                    name="favorite_anime"
                    id="favorite_anime"
                    value="{{ old('favorite_anime', $user->favorite_anime) }}"
                    placeholder="Contoh: Attack on Titan, Steins;Gate, Naruto..."
                    class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2.5 text-xs text-white placeholder:text-slate-500 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                >
                @error('favorite_anime')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Bio / Kutipan --}}
            <div>
                <label for="bio" class="block text-xs font-bold text-slate-300 mb-1.5">Bio / Kutipan Profil (Maks. 500 karakter)</label>
                <textarea
                    name="bio"
                    id="bio"
                    rows="3"
                    placeholder="Tuliskan sedikit tentang diri Anda atau kutipan anime favorit..."
                    class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2.5 text-xs text-white placeholder:text-slate-500 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                >{{ old('bio', $user->bio) }}</textarea>
                @error('bio')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="rounded-xl bg-brand-500 px-6 py-2.5 text-xs font-bold text-white shadow-glow transition hover:bg-brand-600">
                    Simpan Perubahan Profil
                </button>
            </div>
        </form>
    </div>

    {{-- FORM 2: GANTI PASSWORD --}}
    <div class="surface-panel rounded-3xl border border-white/10 p-6 shadow-elevated">
        <h2 class="text-base font-bold text-white border-b border-white/10 pb-3 mb-5">
            Perbarui Password Akun
        </h2>

        <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password" class="block text-xs font-bold text-slate-300 mb-1.5">Password Saat Ini</label>
                <input
                    type="password"
                    name="current_password"
                    id="current_password"
                    required
                    class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2.5 text-xs text-white placeholder:text-slate-500 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                >
                @error('current_password')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-300 mb-1.5">Password Baru</label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        required
                        class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2.5 text-xs text-white placeholder:text-slate-500 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                    >
                    @error('password')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-300 mb-1.5">Konfirmasi Password Baru</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        id="password_confirmation"
                        required
                        class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2.5 text-xs text-white placeholder:text-slate-500 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                    >
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="rounded-xl border border-white/10 bg-white/10 px-6 py-2.5 text-xs font-bold text-white transition hover:bg-white/20">
                    Perbarui Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Live avatar preview
    document.getElementById('avatar-input')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(evt) {
                document.getElementById('avatar-preview').src = evt.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection
