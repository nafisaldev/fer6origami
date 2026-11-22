@extends('layouts.app')

@section('title', 'Kelola Halaman Statis — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.25em] text-brand-400 font-bold">Website Management</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Halaman Website (Pages)</h1>
            <p class="mt-1 text-xs text-slate-400">Kelola halaman statis seperti About Us, Hubungi Kami, Syarat Layanan & DMCA.</p>
        </div>
        <a href="{{ route('admin.pages.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-bold text-white shadow-glow transition hover:bg-brand-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Halaman Baru</span>
        </a>
    </div>

    @include('admin.partials.delete-all-button', ['resource' => 'pages', 'label' => 'Halaman'])

    <div class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
        @if ($pages->isEmpty())
            <p class="py-12 text-center text-xs text-slate-500">Belum ada halaman website.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="border-b border-white/5 bg-[#11131b] text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-4 py-3.5">Judul Halaman</th>
                            <th class="px-4 py-3.5">Slug URL</th>
                            <th class="px-4 py-3.5">Status Publikasi</th>
                            <th class="px-4 py-3.5">Terakhir Diperbarui</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach ($pages as $p)
                            <tr class="transition hover:bg-white/[0.02]">
                                <td class="px-4 py-3 font-bold text-white">
                                    {{ $p->title }}
                                </td>
                                <td class="px-4 py-3 font-mono text-[11px] text-slate-400">
                                    /{{ $p->slug }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded px-2.5 py-0.5 text-[10px] font-bold {{ $p->is_published ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-500/20 text-slate-400' }}">
                                        {{ $p->is_published ? 'Published' : 'Draft' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-400 text-[10px]">
                                    {{ $p->updated_at->diffForHumans() }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.pages.edit', $p) }}" class="rounded bg-white/10 px-2.5 py-1 text-[11px] text-white hover:bg-white/20">Edit</a>
                                        <form method="POST" action="{{ route('admin.pages.destroy', $p) }}" onsubmit="return confirm('Hapus halaman ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded bg-red-500/10 px-2.5 py-1 text-[11px] text-red-400 hover:bg-red-500 hover:text-white">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-white/5 p-4">
                {{ $pages->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
