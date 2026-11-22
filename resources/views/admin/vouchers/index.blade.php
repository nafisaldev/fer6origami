@extends('layouts.app')

@section('title', 'Kelola Voucher — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 border-b border-white/10 pb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-brand-400">Membership</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white sm:text-3xl">Kelola Voucher</h1>
            <p class="mt-1 text-xs text-slate-400">Buat dan pantau kode diskon untuk pembelian paket membership.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.membership-plans.index') }}" class="rounded-xl border border-white/10 px-3.5 py-2 text-xs font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white">Paket Membership</a>
            <a href="{{ route('admin.vouchers.create') }}" class="rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white transition hover:bg-brand-400">Tambah Voucher</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="surface-panel rounded-2xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-xs text-rose-200" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- FILTER & SEARCH --}}
    <div class="surface-panel rounded-2xl border border-white/10 p-4 shadow-elevated">
        <form method="GET" action="{{ route('admin.vouchers.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="sm:col-span-2">
                <label for="voucher-search" class="sr-only">Cari kode atau nama voucher</label>
                <input id="voucher-search" type="text" name="search" value="{{ $search }}" placeholder="Cari kode atau nama voucher..." class="w-full rounded-xl border border-white/10 bg-[#161924] px-3.5 py-2 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">
            </div>
            <div class="flex items-center gap-2 lg:col-span-2">
                <button type="submit" class="w-full rounded-xl bg-white/10 px-4 py-2 text-xs font-semibold text-white transition hover:bg-white/20">Filter</button>
            </div>
        </form>
    </div>

    {{-- VOUCHER TABLE --}}
    <div class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="border-b border-white/5 bg-[#11131b] text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-4 py-3.5">Kode</th>
                        <th class="px-4 py-3.5">Nama</th>
                        <th class="px-4 py-3.5">Diskon</th>
                        <th class="px-4 py-3.5">Pemakaian</th>
                        <th class="px-4 py-3.5">Masa Berlaku</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($vouchers as $voucher)
                        <tr class="transition hover:bg-white/[0.02]">
                            <td class="px-4 py-3">
                                <span class="rounded bg-brand-500/15 px-2 py-0.5 font-bold tracking-wider text-brand-300">{{ $voucher->code }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-bold text-white">{{ $voucher->name }}</p>
                                <p class="max-w-xs truncate text-[10px] text-slate-500">{{ $voucher->description ?: '-' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @if ($voucher->type === 'percent')
                                    <span class="font-bold text-white">{{ $voucher->value }}%</span>
                                    @if ($voucher->max_discount)
                                        <p class="text-[10px] text-slate-500">Maks. Rp {{ number_format((float) $voucher->max_discount, 0, ',', '.') }}</p>
                                    @endif
                                @else
                                    <span class="font-bold text-white">Rp {{ number_format((float) $voucher->value, 0, ',', '.') }}</span>
                                @endif
                                @if ($voucher->min_purchase)
                                    <p class="text-[10px] text-slate-500">Min. Rp {{ number_format((float) $voucher->min_purchase, 0, ',', '.') }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-400">
                                <span class="font-bold text-white">{{ $voucher->used_count }}</span>
                                @if ($voucher->usage_limit)
                                    / {{ $voucher->usage_limit }}
                                @else
                                    <span class="text-slate-500">/ tanpa batas</span>
                                @endif
                                <p class="text-[10px] text-slate-500">Maks. {{ $voucher->per_user_limit }} / pengguna</p>
                            </td>
                            <td class="px-4 py-3 text-slate-400">
                                <p>{{ optional($voucher->valid_from)->format('d M Y H:i') ?: '-' }}</p>
                                <p class="text-[10px]">s.d. {{ optional($voucher->valid_until)->format('d M Y H:i') ?: '-' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @if ($voucher->is_active)
                                    <span class="rounded bg-emerald-500/15 px-2 py-0.5 text-[10px] font-bold text-emerald-300">Aktif</span>
                                @else
                                    <span class="rounded bg-slate-500/15 px-2 py-0.5 text-[10px] font-bold text-slate-300">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.vouchers.edit', $voucher) }}" class="rounded-lg bg-white/10 px-2.5 py-1 text-[11px] font-semibold text-white transition hover:bg-white/20">Edit</a>
                                    <form method="POST" action="{{ route('admin.vouchers.destroy', $voucher) }}" onsubmit="return confirm('Hapus voucher {{ addslashes($voucher->code) }}?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg bg-rose-500/10 px-2.5 py-1 text-[11px] font-semibold text-rose-300 transition hover:bg-rose-500 hover:text-white">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-xs text-slate-500">Belum ada voucher yang cocok dengan pencarian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/5 p-4">
            {{ $vouchers->links() }}
        </div>
    </div>
</div>
@endsection
