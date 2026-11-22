@extends('layouts.app')

@section('title', 'Role & Permission RBAC — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.25em] text-brand-400 font-bold">User Management</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Role & Hak Akses (RBAC)</h1>
            <p class="mt-1 text-xs text-slate-400">Kelola Role-Based Access Control dan batasan hak akses per modul sistem.</p>
        </div>
        <a href="{{ route('admin.roles.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-bold text-white shadow-glow transition hover:bg-brand-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Role Baru</span>
        </a>
    </div>

    {{-- ROLES LIST CARDS / TABLE --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($roles as $role)
            <div class="surface-panel rounded-2xl border border-white/10 p-5 flex flex-col justify-between shadow-elevated transition hover:border-brand-500/30">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="rounded bg-brand-500/20 px-2.5 py-0.5 text-xs font-extrabold text-brand-400 uppercase">
                            {{ $role->slug }}
                        </span>
                        @if ($role->is_system)
                            <span class="rounded bg-purple-500/20 px-2 py-0.5 text-[10px] font-bold text-purple-400">System Role</span>
                        @endif
                    </div>
                    <h2 class="mt-3 text-lg font-bold text-white">{{ $role->name }}</h2>
                    <p class="mt-1 text-xs text-slate-400 leading-relaxed">{{ $role->description ?: 'Tidak ada deskripsi role.' }}</p>

                    <div class="mt-4 flex items-center gap-3 border-t border-white/5 pt-3 text-xs text-slate-400">
                        <span><strong>{{ $role->permissions_count }}</strong> permissions</span>
                        <span>•</span>
                        <span><strong>{{ $role->users_count }}</strong> pengguna</span>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-2 border-t border-white/5 pt-3">
                    <a href="{{ route('admin.roles.show', $role) }}" class="rounded-lg border border-white/10 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white">
                        Detail
                    </a>
                    @if (!$role->is_system || auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.roles.edit', $role) }}" class="rounded-lg bg-white/10 px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/20">
                            Edit Hak Akses
                        </a>
                    @endif
                    @if (!$role->is_system && $role->users_count === 0)
                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm('Hapus role ini?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg bg-red-500/10 px-3 py-1.5 text-xs font-semibold text-red-400 hover:bg-red-500 hover:text-white">
                                Hapus
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
