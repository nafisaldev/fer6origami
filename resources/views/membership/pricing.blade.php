@extends('layouts.app')

@section('title', 'Paket Membership - AnimeStream')

@section('content')
<div class="space-y-8">
    <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated sm:p-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-wider text-brand-400">Membership</p>
                <h1 class="mt-2 font-display text-2xl font-extrabold text-white sm:text-3xl">Pilih paket sesuai cara Anda menonton</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-300">
                    Semua paket memakai sumber data yang sama. Status aktif, masa berlaku, dan riwayat perubahan tersimpan di akun Anda.
                </p>
            </div>
            @auth
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('membership.show') }}" class="inline-flex min-h-[44px] items-center rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10 hover:text-white">
                        Paket Saya
                    </a>
                    <a href="{{ route('membership.history') }}" class="inline-flex min-h-[44px] items-center rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10 hover:text-white">
                        Riwayat
                    </a>
                </div>
            @endauth
        </div>

        @if ($currentTier)
            <div class="mt-6 rounded-xl border border-brand-500/30 bg-brand-500/10 p-4 text-xs text-slate-200">
                Paket Anda saat ini: <strong class="text-white">{{ strtoupper($currentTier) }}</strong>.
                @if ($current->expires_at)
                    Aktif sampai {{ $current->expires_at->translatedFormat('d M Y H:i') }}.
                @else
                    Tidak ada tanggal kedaluwarsa.
                @endif
            </div>
        @endif
    </div>

    @if ($plans->isEmpty())
        <div class="surface-panel rounded-2xl border border-white/10 p-10 text-center">
            <h2 class="text-sm font-bold text-white">Belum ada paket yang tersedia</h2>
            <p class="mt-2 text-xs text-slate-400">Administrator belum mengaktifkan paket membership. Silakan kembali lagi nanti.</p>
        </div>
    @else
        <div class="grid gap-4 lg:grid-cols-3">
            @foreach ($plans as $plan)
                @php
                    $isCurrent = $currentTier === $plan->slug;
                @endphp
                <div class="surface-panel flex min-w-0 flex-col rounded-2xl border p-5 shadow-elevated {{ $isCurrent ? 'border-brand-500/50' : 'border-white/10' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="font-display text-xl font-extrabold text-white">{{ $plan->name }}</h2>
                            <p class="mt-1 text-xs text-slate-400">{{ $plan->tagline ?: 'Paket membership AnimeStream' }}</p>
                        </div>
                        @if ($isCurrent)
                            <span class="shrink-0 rounded-lg bg-brand-500/15 px-2 py-1 text-[10px] font-bold text-brand-300">Paket Anda</span>
                        @endif
                    </div>

                    @if ($plan->description)
                        <p class="mt-4 text-xs leading-relaxed text-slate-300">{{ $plan->description }}</p>
                    @endif

                    <ul class="mt-5 flex-1 space-y-2.5">
                        @forelse ($plan->benefits ?? [] as $benefit)
                            <li class="flex gap-2 text-xs text-slate-300">
                                <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>{{ $benefit }}</span>
                            </li>
                        @empty
                            <li class="text-xs text-slate-500">Belum ada daftar fitur untuk paket ini.</li>
                        @endforelse
                    </ul>

                    <div class="mt-6 space-y-2 border-t border-white/10 pt-5">
                        @forelse ($plan->activePrices as $price)
                            <a
                                href="{{ auth()->check() ? route('membership.checkout', $price) : route('login') }}"
                                class="flex min-h-[44px] items-center justify-between gap-3 rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-xs transition hover:border-brand-500/40 hover:bg-brand-500/10"
                            >
                                <span class="font-semibold text-slate-200">{{ $price->label }}</span>
                                <span class="font-bold text-white">Rp {{ number_format((float) $price->price, 0, ',', '.') }}</span>
                            </a>
                        @empty
                            @if ($plan->slug === \App\Models\MembershipPlan::FREE)
                                <a href="{{ auth()->check() ? route('membership.show') : route('register') }}" class="flex min-h-[44px] items-center justify-center rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-brand-600">
                                    {{ auth()->check() ? 'Lihat Paket Saya' : 'Daftar Gratis' }}
                                </a>
                            @else
                                <p class="text-xs text-slate-500">Harga belum diatur administrator.</p>
                            @endif
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
