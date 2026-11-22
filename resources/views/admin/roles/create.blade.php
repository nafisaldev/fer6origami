@extends('layouts.app')

@section('title', 'Tambah Role Baru — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between border-b border-white/10 pb-4">
        <div>
            <p class="text-xs uppercase tracking-wider text-brand-400 font-bold">RBAC Management</p>
            <h1 class="font-display text-2xl font-bold text-white">Buat Role Baru</h1>
        </div>
        <a href="{{ route('admin.roles.index') }}" class="rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white">&larr; Kembali</a>
    </div>

    <form method="POST" action="{{ route('admin.roles.store') }}" class="space-y-6">
        @csrf
        <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated space-y-4">
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Nama Role <span class="text-brand-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Media Specialist" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
                @error('name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300">Deskripsi Role</label>
                <textarea name="description" rows="2" placeholder="Tanggung jawab dan cakupan role..." class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs text-white">{{ old('description') }}</textarea>
            </div>
        </div>

        {{-- PERMISSIONS CHECKBOXES GROUPED BY MODULE --}}
        <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated space-y-6">
            <div>
                <h2 class="font-display text-base font-bold text-white">Atur Hak Akses / Permissions</h2>
                <p class="text-xs text-slate-400">Centang izin apa saja yang diperbolehkan untuk role ini.</p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($permissions as $moduleName => $perms)
                    <div class="rounded-xl border border-white/5 bg-white/5 p-4 space-y-3">
                        <div class="flex items-center justify-between border-b border-white/5 pb-2">
                            <span class="text-xs font-extrabold uppercase tracking-wider text-brand-400">{{ strtoupper($moduleName) }}</span>
                            <button type="button" onclick="toggleModuleCheckboxes(this)" class="text-[10px] text-slate-400 hover:text-white">Pilih Semua</button>
                        </div>
                        <div class="space-y-2">
                            @foreach ($perms as $p)
                                <label class="flex items-start gap-2 text-xs text-slate-300 hover:text-white cursor-pointer">
                                    <input type="checkbox" name="permission_ids[]" value="{{ $p->id }}" class="mt-0.5 rounded border-white/20 bg-[#161924] text-brand-500 focus:ring-0">
                                    <span class="leading-snug">{{ $p->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-4 border-t border-white/5 flex justify-end gap-3">
                <a href="{{ route('admin.roles.index') }}" class="rounded-xl border border-white/10 px-5 py-2.5 text-xs font-semibold text-slate-300 hover:bg-white/10">Batal</a>
                <button type="submit" class="rounded-xl bg-brand-500 px-6 py-2.5 text-xs font-bold text-white shadow-glow hover:bg-brand-600">Simpan Role</button>
            </div>
        </div>
    </form>
</div>

<script>
    function toggleModuleCheckboxes(btn) {
        const parent = btn.closest('.rounded-xl');
        const checkboxes = parent.querySelectorAll('input[type="checkbox"]');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
        btn.innerText = allChecked ? 'Pilih Semua' : 'Batal Semua';
    }
</script>
@endsection
