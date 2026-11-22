@extends('layouts.app')

@section('title', 'Kelola Notifikasi — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.25em] text-brand-400 font-bold">Communication</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Notifikasi Broadcast</h1>
            <p class="mt-1 text-xs text-slate-400">Kirim pemberitahuan penting ke lonceng notifikasi pengguna atau role tertentu.</p>
        </div>
        <a href="{{ route('admin.notifications.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-bold text-white shadow-glow transition hover:bg-brand-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Kirim Notifikasi Baru</span>
        </a>
    </div>

    @include('admin.partials.delete-all-button', ['resource' => 'notifications', 'label' => 'Notifikasi'])

    {{-- NOTIFICATIONS TABLE --}}
    <div class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
        @if ($notifications->isEmpty())
            <p class="py-12 text-center text-xs text-slate-500">Belum ada riwayat notifikasi broadcast.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="border-b border-white/5 bg-[#11131b] text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-4 py-3.5">Judul & Pesan</th>
                            <th class="px-4 py-3.5">Kategori</th>
                            <th class="px-4 py-3.5">Target Pengguna</th>
                            <th class="px-4 py-3.5">Status Dibaca</th>
                            <th class="px-4 py-3.5">Waktu Kirim</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach ($notifications as $notif)
                            <tr class="transition hover:bg-white/[0.02]">
                                <td class="px-4 py-3 max-w-md">
                                    <span class="font-bold text-white block">{{ $notif->title }}</span>
                                    <p class="mt-0.5 text-slate-400 line-clamp-1">{{ $notif->message }}</p>
                                    @if ($notif->action_url)
                                        <a href="{{ $notif->action_url }}" target="_blank" class="text-[10px] text-brand-400 hover:underline">Link Tindakan &rarr;</a>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded bg-brand-500/20 px-2 py-0.5 text-[10px] font-bold text-brand-400">
                                        {{ $types[$notif->type] ?? $notif->type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded bg-white/10 px-2 py-0.5 text-[10px] font-bold text-slate-300">
                                        {{ $notif->target_role ? strtoupper($notif->target_role) : 'Seluruh Pengguna' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-300 font-semibold">
                                    {{ number_format($notif->reads_count) }} pengguna
                                </td>
                                <td class="px-4 py-3 text-slate-400 text-[10px]">
                                    {{ $notif->created_at->diffForHumans() }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <form method="POST" action="{{ route('admin.notifications.destroy', $notif) }}" onsubmit="return confirm('Hapus notifikasi ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded bg-red-500/10 px-2 py-1 text-[11px] text-red-400 hover:bg-red-500 hover:text-white">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-white/5 p-4">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
