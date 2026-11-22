@extends('layouts.app')

@section('title', 'Paket Membership — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 border-b border-white/10 pb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-brand-400">Membership</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Paket Membership</h1>
            <p class="mt-1 text-xs text-slate-400">Kelola tier paket, benefit, dan pilihan harga langganan yang tersedia untuk pengguna.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.membership.index') }}" class="rounded-xl border border-white/10 px-3.5 py-2 text-xs font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white">Kelola Membership</a>
            <a href="{{ route('admin.membership-plans.create') }}" class="rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white transition hover:bg-brand-400">Tambah Paket</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="surface-panel rounded-2xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-xs text-rose-200" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="border-b border-white/5 bg-[#11131b] text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-4 py-3.5">Paket</th>
                        <th class="px-4 py-3.5">Tagline</th>
                        <th class="px-4 py-3.5">Harga</th>
                        <th class="px-4 py-3.5">Benefit</th>
                        <th class="px-4 py-3.5">Urutan</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($plans as $plan)
                        <tr class="transition hover:bg-white/[0.02]">
                            <td class="px-4 py-3">
                                <p class="font-bold text-white">{{ $plan->name }}</p>
                                <p class="text-[10px] uppercase tracking-wider text-brand-400">{{ $plan->slug }}</p>
                            </td>
                            <td class="max-w-xs px-4 py-3 text-slate-400">{{ $plan->tagline ?: '-' }}</td>
                            <td class="px-4 py-3">
                                @forelse ($plan->prices as $price)
                                    <p class="text-[11px] text-slate-300">{{ $price->duration_days }} hari — Rp {{ number_format((float) $price->price, 0, ',', '.') }}</p>
                                @empty
                                    <span class="text-[11px] text-slate-500">Belum ada harga</span>
                                @endforelse
                            </td>
                            <td class="px-4 py-3 text-slate-400">{{ is_array($plan->benefits) ? count($plan->benefits) : 0 }} item</td>
                            <td class="px-4 py-3 text-slate-400">{{ $plan->sort_order }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1.5">
                                    @if ($plan->is_active)
                                        <span class="rounded bg-emerald-500/15 px-2 py-0.5 text-[10px] font-bold text-emerald-300">Aktif</span>
                                    @else
                                        <span class="rounded bg-slate-500/15 px-2 py-0.5 text-[10px] font-bold text-slate-300">Nonaktif</span>
                                    @endif
                                    @if ($plan->is_default)
                                        <span class="rounded bg-brand-500/15 px-2 py-0.5 text-[10px] font-bold text-brand-300">Default</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.membership-plans.edit', $plan) }}" class="rounded-lg bg-white/10 px-2.5 py-1 text-[11px] font-semibold text-white transition hover:bg-white/20">Edit</a>
                                    @if (! $plan->is_default)
                                        <form method="POST" action="{{ route('admin.membership-plans.destroy', $plan) }}" onsubmit="return confirm('Hapus paket {{ addslashes($plan->name) }}?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg bg-rose-500/10 px-2.5 py-1 text-[11px] font-semibold text-rose-300 transition hover:bg-rose-500 hover:text-white">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-xs text-slate-500">Belum ada paket membership. Tambahkan paket pertama Anda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
