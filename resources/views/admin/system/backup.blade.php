@extends('layouts.app')

@section('title', 'Backup & Restore Database — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.25em] text-brand-400 font-bold">System Management</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Backup & Restore Database</h1>
            <p class="mt-1 text-xs text-slate-400">Buat cadangan data MySQL lengkap, unduh arsip SQL, atau pulihkan data bila terjadi insiden.</p>
        </div>
        <form method="POST" action="{{ route('admin.system.backup.store') }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-xs font-bold text-white shadow-glow hover:bg-brand-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span>Backup Database Sekarang</span>
            </button>
        </form>
    </div>

    {{-- DB OVERVIEW SUMMARY --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="surface-panel rounded-2xl border border-white/10 p-5">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nama Database</span>
            <p class="mt-2 font-display text-lg font-bold text-white">{{ $dbInfo['database'] }}</p>
            <p class="text-xs text-emerald-400 font-semibold">{{ $dbInfo['status'] }}</p>
        </div>
        <div class="surface-panel rounded-2xl border border-white/10 p-5">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Tabel</span>
            <p class="mt-2 font-display text-lg font-bold text-white">{{ $dbInfo['table_count'] }} tabel</p>
            <p class="text-xs text-slate-400">Driver {{ $dbInfo['driver'] }}</p>
        </div>
        <div class="surface-panel rounded-2xl border border-white/10 p-5">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Ukuran Database</span>
            <p class="mt-2 font-display text-lg font-bold text-white">{{ $dbInfo['size'] }}</p>
            <p class="text-xs text-slate-400">Tersimpan di storage/app/backups</p>
        </div>
    </div>

    {{-- BACKUPS LIST --}}
    <div class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
        <div class="border-b border-white/5 bg-[#11131b] p-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-300">Daftar Berkas Backup Tersimpan ({{ count($backups) }})</h2>
        </div>

        @if (empty($backups))
            <p class="py-12 text-center text-xs text-slate-500">Belum ada file backup database yang dibuat.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="border-b border-white/5 text-[10px] uppercase font-bold text-slate-400">
                        <tr>
                            <th class="px-4 py-3.5">Nama Berkas SQL</th>
                            <th class="px-4 py-3.5">Ukuran Berkas</th>
                            <th class="px-4 py-3.5">Tanggal Pembuatan</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach ($backups as $b)
                            <tr class="transition hover:bg-white/[0.02]">
                                <td class="px-4 py-3 font-mono font-bold text-white">
                                    {{ $b['filename'] }}
                                </td>
                                <td class="px-4 py-3 text-slate-300 font-mono">
                                    {{ $b['size'] }}
                                </td>
                                <td class="px-4 py-3 text-slate-400 text-[10px]">
                                    {{ $b['created_at'] }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.system.backup.download', $b['filename']) }}" class="rounded bg-white/10 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-white/20">
                                            Unduh SQL
                                        </a>
                                        <button type="button" onclick="openRestoreModal('{{ $b['filename'] }}')" class="rounded bg-amber-500/10 px-2.5 py-1 text-[11px] font-semibold text-amber-400 hover:bg-amber-500 hover:text-white">
                                            Restore
                                        </button>
                                        <form method="POST" action="{{ route('admin.system.backup.destroy', $b['filename']) }}" onsubmit="return confirm('Hapus file backup ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded bg-red-500/10 px-2.5 py-1 text-[11px] font-semibold text-red-400 hover:bg-red-500 hover:text-white">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- MODAL RESTORE CONFIRMATION --}}
<div id="restoreModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/85 p-4">
    <div class="surface-panel w-full max-w-md rounded-2xl border border-red-500/30 p-6 shadow-2xl space-y-4">
        <div class="flex items-center gap-3 text-red-400">
            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <h3 class="font-display text-base font-bold text-white">Konfirmasi Pemulihan (Restore) Database</h3>
        </div>
        <p class="text-xs text-slate-300 leading-relaxed">
            Peringatan: Tindakan ini akan menggantikan seluruh tabel dan data database yang sedang berjalan dengan isi berkas backup <strong class="text-white" id="restoreFilenameText"></strong>.
        </p>

        <form method="POST" action="{{ route('admin.system.backup.restore') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="filename" id="restoreFilenameInput">
            <div>
                <label class="mb-1 block text-xs text-slate-400">Ketik kata <strong class="text-red-400 font-mono">RESTORE</strong> untuk melanjutkan:</label>
                <input type="text" name="confirmation" required placeholder="RESTORE" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-mono uppercase text-white focus:border-red-500">
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-white/5">
                <button type="button" onclick="closeRestoreModal()" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10">Batal</button>
                <button type="submit" class="rounded-xl bg-red-600 px-5 py-2 text-xs font-bold text-white shadow-glow hover:bg-red-500">Lakukan Restore Sekarang</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRestoreModal(filename) {
        document.getElementById('restoreFilenameText').innerText = filename;
        document.getElementById('restoreFilenameInput').value = filename;
        document.getElementById('restoreModal').classList.remove('hidden');
    }
    function closeRestoreModal() {
        document.getElementById('restoreModal').classList.add('hidden');
    }
</script>
@endsection
