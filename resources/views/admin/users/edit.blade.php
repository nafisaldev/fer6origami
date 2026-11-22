@extends('layouts.app')

@section('title', 'Edit Pengguna: ' . $user->name . ' — Admin Fer6origami')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center justify-between border-b border-white/10 pb-4">
        <div>
            <p class="text-xs uppercase tracking-wider text-brand-400 font-bold">User Management</p>
            <h1 class="font-display text-2xl font-bold text-white">Edit Pengguna: {{ $user->name }}</h1>
        </div>
        <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white">
            &larr; Kembali
        </a>
    </div>

    <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated">
        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="flex items-center gap-4 border-b border-white/5 pb-4">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-14 w-14 rounded-xl object-cover ring-2 ring-white/10">
                <div>
                    <h2 class="text-base font-bold text-white">{{ $user->name }}</h2>
                    <p class="text-xs text-slate-400">{{ $user->email }} • Terdaftar {{ $user->created_at->format('d M Y') }}</p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Nama Lengkap <span class="text-brand-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
                    @error('name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Email Akun <span class="text-brand-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
                    @error('email') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Role Pengguna (RBAC)</label>
                    <select name="role_id" class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2.5 text-xs text-white">
                        @foreach ($roles as $role)
                            @if ($role->slug === 'super_admin' && !auth()->user()->isSuperAdmin())
                                @continue
                            @endif
                            <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id || (empty($user->role_id) && $user->role === $role->slug))>
                                {{ $role->name }} ({{ $role->slug }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Status Akun</label>
                    <select name="status" class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2.5 text-xs text-white">
                        @foreach ($statuses as $val => $lbl)
                            <option value="{{ $val }}" @selected(old('status', $user->status) === $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Alasan Sanksi / Catatan Status</label>
                    <input type="text" name="banned_reason" value="{{ old('banned_reason', $user->banned_reason) }}" placeholder="Catatan pelanggaran jika ada" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Suspend Sampai (Jika Berlaku)</label>
                    <input type="datetime-local" name="banned_until" value="{{ old('banned_until', optional($user->banned_until)->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
                </div>
            </div>

            <div class="border-t border-white/5 pt-4">
                <p class="text-xs font-bold uppercase tracking-wider text-amber-400 mb-3">Ganti Password (Kosongkan jika tidak ingin diubah)</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs text-slate-300">Password Baru</label>
                        <input type="password" name="password" placeholder="••••••••" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
                        @error('password') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-slate-300">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" placeholder="••••••••" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-white/5 flex justify-end gap-3">
                <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-white/10 px-5 py-2.5 text-xs font-semibold text-slate-300 hover:bg-white/10">Batal</a>
                <button type="submit" class="rounded-xl bg-brand-500 px-6 py-2.5 text-xs font-bold text-white shadow-glow hover:bg-brand-600">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
