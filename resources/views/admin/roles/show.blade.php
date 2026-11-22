@extends('layouts.app')

@section('title', 'Detail Role: ' . $role->name . ' — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between border-b border-white/10 pb-4">
        <div>
            <p class="text-xs uppercase tracking-wider text-brand-400 font-bold">RBAC Management</p>
            <h1 class="font-display text-2xl font-bold text-white">{{ $role->name }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.roles.edit', $role) }}" class="rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white hover:bg-white/20">Edit Hak Akses</a>
            <a href="{{ route('admin.roles.index') }}" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white">&larr; Daftar Role</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- USERS WITH THIS ROLE --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-5 space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-white/5 pb-2">
                Pengguna dengan Role Ini ({{ $role->users->count() }})
            </h2>
            @if ($role->users->isEmpty())
                <p class="py-6 text-center text-xs text-slate-500">Belum ada pengguna dengan role ini.</p>
            @else
                <div class="space-y-2.5 max-h-96 overflow-y-auto scrollbar-thin">
                    @foreach ($role->users as $u)
                        <div class="flex items-center justify-between gap-2 rounded-xl border border-white/5 bg-white/5 p-2.5">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <img src="{{ $u->avatar_url }}" alt="" class="h-8 w-8 rounded-lg object-cover">
                                <div class="min-w-0">
                                    <a href="{{ route('admin.users.show', $u) }}" class="text-xs font-bold text-white hover:text-brand-400 truncate block">
                                        {{ $u->name }}
                                    </a>
                                    <p class="text-[10px] text-slate-500 truncate">{{ $u->email }}</p>
                                </div>
                            </div>
                            <span class="rounded px-2 py-0.5 text-[9px] font-bold {{ $u->status === 'active' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400' }}">
                                {{ $u->status }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- PERMISSIONS GRANTED --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-5 lg:col-span-2">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-white/5 pb-2 mb-4">
                Daftar Hak Akses yang Diberikan ({{ $role->slug === 'super_admin' ? 'Akses Penuh Seluruh Sistem' : $role->permissions->count() . ' Permissions' }})
            </h2>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($allPermissions as $modName => $perms)
                    <div class="rounded-xl border border-white/5 bg-white/5 p-3.5 space-y-2">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-brand-400">{{ strtoupper($modName) }}</span>
                        <div class="space-y-1.5">
                            @foreach ($perms as $p)
                                @php
                                    $has = $role->slug === 'super_admin' || $role->permissions->contains('id', $p->id);
                                @endphp
                                <div class="flex items-center gap-2 text-xs {{ $has ? 'text-white' : 'text-slate-600 line-through' }}">
                                    <span class="{{ $has ? 'text-emerald-400' : 'text-slate-600' }}">{{ $has ? 'Aktif' : 'Nonaktif' }}</span>
                                    <span>{{ $p->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
