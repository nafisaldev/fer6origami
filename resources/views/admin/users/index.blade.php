@extends('layouts.app')

@section('title', 'Kelola Pengguna — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.25em] text-brand-400 font-bold">User Management</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Kelola Pengguna</h1>
            <p class="mt-1 text-xs text-slate-400">Pantau seluruh pengguna, kelola role RBAC, moderasi akun, suspend, dan ban.</p>
        </div>
    </div>

    {{-- FILTER & SEARCH BAR --}}
    <div class="surface-panel rounded-2xl border border-white/10 p-4 shadow-elevated">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau email pengguna..." class="w-full rounded-xl border border-white/10 bg-[#161924] px-3.5 py-2 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">
            </div>

            <div>
                <select name="role" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white">
                    <option value="">Semua Role</option>
                    @foreach ($roles as $r)
                        <option value="{{ $r->slug }}" @selected($selectedRole === $r->slug)>{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <select name="status" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white">
                    <option value="">Semua Status</option>
                    @foreach ($statuses as $sKey => $sLabel)
                        <option value="{{ $sKey }}" @selected($selectedStatus === $sKey)>{{ $sLabel }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-xl bg-white/10 px-4 py-2 text-xs font-semibold text-white transition hover:bg-white/20">
                    Filter
                </button>
            </div>
        </form>
    </div>

    {{-- USERS TABLE --}}
    <div class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="border-b border-white/5 bg-[#11131b] text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-4 py-3.5">Pengguna</th>
                        <th class="px-4 py-3.5">Role</th>
                        <th class="px-4 py-3.5">Level</th>
                        <th class="px-4 py-3.5">Status Akun</th>
                        <th class="px-4 py-3.5">Aktivitas</th>
                        <th class="px-4 py-3.5">Login Terakhir</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach ($users as $user)
                        <tr class="transition hover:bg-white/[0.02]">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-9 w-9 shrink-0 rounded-xl object-cover ring-1 ring-white/10">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <a href="{{ route('admin.users.show', $user) }}" class="font-bold text-white hover:text-brand-400 truncate block">
                                                {{ $user->name }}
                                            </a>
                                            @if ($user->email_verified_at)
                                                <span title="Email Terverifikasi" class="text-emerald-400">Terverifikasi</span>
                                            @endif
                                        </div>
                                        <p class="text-[10px] text-slate-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $roleSlug = $user->roleModel->slug ?? $user->role;
                                @endphp
                                <span class="rounded px-2.5 py-0.5 text-[10px] font-bold {{ $roleSlug === 'super_admin' ? 'bg-purple-500/20 text-purple-400' : ($roleSlug === 'admin' ? 'bg-brand-500/20 text-brand-400' : 'bg-slate-500/20 text-slate-300') }}">
                                    {{ $user->roleModel->name ?? strtoupper($user->role) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.users.show', $user) }}#level-control" class="font-bold text-white hover:text-brand-400">Lv. {{ $user->level }}</a>
                                <p class="text-[10px] text-slate-500">{{ number_format($user->xp) }} XP</p>
                                @if ($user->xp_manual)
                                    <p class="text-[9px] font-bold text-amber-400">Manual</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($user->status === 'banned')
                                    <span class="rounded bg-red-500/20 px-2 py-0.5 text-[10px] font-bold text-red-400">Banned</span>
                                @elseif ($user->status === 'suspended')
                                    <span class="rounded bg-amber-500/20 px-2 py-0.5 text-[10px] font-bold text-amber-400">Suspended</span>
                                @else
                                    <span class="rounded bg-emerald-500/20 px-2 py-0.5 text-[10px] font-bold text-emerald-400">Active</span>
                                @endif
                                @if ($user->banned_reason)
                                    <p class="text-[9px] text-slate-500 truncate max-w-xs mt-0.5">{{ $user->banned_reason }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-400">
                                <span class="text-white font-semibold">{{ $user->watch_histories_count }}</span> views • <span class="text-white font-semibold">{{ $user->watchlists_count }}</span> saved
                            </td>
                            <td class="px-4 py-3 text-slate-400">
                                @if ($user->last_login_at)
                                    <span class="text-slate-300">{{ $user->last_login_at->diffForHumans() }}</span>
                                    <p class="text-[9px] text-slate-500">IP: {{ $user->last_login_ip ?: '-' }}</p>
                                @else
                                    <span class="text-slate-600">Belum pernah</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.users.show', $user) }}" class="rounded-lg border border-white/10 px-2.5 py-1 text-[11px] font-semibold text-slate-300 hover:bg-white/10 hover:text-white">
                                        Profil
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="rounded-lg bg-white/10 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-white/20">
                                        Edit
                                    </a>
                                    @if (!$user->isSuperAdmin() && $user->id !== auth()->id())
                                        @if ($user->status === 'active')
                                            <button type="button" onclick="openBanModal({{ $user->id }}, '{{ addslashes($user->name) }}')" class="rounded-lg bg-amber-500/10 px-2.5 py-1 text-[11px] font-semibold text-amber-400 hover:bg-amber-500 hover:text-white">
                                                Sanksi
                                            </button>
                                        @else
                                            <form method="POST" action="{{ route('admin.users.unban', $user) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="rounded-lg bg-emerald-500/10 px-2.5 py-1 text-[11px] font-semibold text-emerald-400 hover:bg-emerald-500 hover:text-white">
                                                    Unban
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus akun pengguna {{ addslashes($user->name) }} secara permanen?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg bg-red-500/10 px-2.5 py-1 text-[11px] font-semibold text-red-400 hover:bg-red-500 hover:text-white">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/5 p-4">
            {{ $users->links() }}
        </div>
    </div>
</div>

{{-- MODAL BAN / SUSPEND --}}
<div id="banModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/80 p-4">
    <div class="surface-panel w-full max-w-md rounded-2xl border border-white/10 p-6 shadow-2xl">
        <h3 class="font-display text-lg font-bold text-white">Sanksi Pengguna: <span id="banUserName"></span></h3>
        <p class="mt-1 text-xs text-slate-400">Pilih jenis sanksi penonaktifan akun untuk pengguna ini.</p>

        <form id="banForm" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-xs font-bold uppercase text-slate-300">Jenis Sanksi</label>
                <select name="status" id="banStatusSelect" required class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2 text-xs text-white">
                    <option value="suspended">Suspend Sementara</option>
                    <option value="banned">Ban Permanen</option>
                </select>
            </div>
            <div id="suspendDaysGroup">
                <label class="mb-1 block text-xs font-bold uppercase text-slate-300">Durasi Suspend (Hari)</label>
                <input type="number" name="banned_days" min="1" value="7" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs text-white">
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold uppercase text-slate-300">Alasan Sanksi</label>
                <textarea name="banned_reason" required rows="3" placeholder="Contoh: Melanggar aturan komentar / spam..." class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs text-white"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-white/5">
                <button type="button" onclick="closeBanModal()" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10">Batal</button>
                <button type="submit" class="rounded-xl bg-red-600 px-5 py-2 text-xs font-bold text-white shadow-glow hover:bg-red-500">Terapkan Sanksi</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openBanModal(userId, userName) {
        document.getElementById('banUserName').innerText = userName;
        document.getElementById('banForm').action = `/admin/users/${userId}/ban`;
        document.getElementById('banModal').classList.remove('hidden');
    }

    function closeBanModal() {
        document.getElementById('banModal').classList.add('hidden');
    }

    const banStatus = document.getElementById('banStatusSelect');
    if (banStatus) {
        banStatus.addEventListener('change', function() {
            document.getElementById('suspendDaysGroup').style.display = this.value === 'suspended' ? 'block' : 'none';
        });
    }
</script>
@endsection
