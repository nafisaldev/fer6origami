@extends('layouts.app')

@section('title', 'Notifikasi Membership - Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="surface-panel flex flex-col gap-3 rounded-2xl border border-white/10 p-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-brand-400">Akun</p>
            <h1 class="mt-1 font-display text-2xl font-extrabold text-white">Notifikasi Membership</h1>
        </div>
        <a href="{{ route('membership.show') }}" class="inline-flex min-h-[44px] items-center rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10 hover:text-white">Kembali ke Paket Saya</a>
    </div>

    <div class="surface-panel space-y-3 rounded-2xl border border-white/10 p-5">
        @forelse ($notifications as $notification)
            <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-sm font-bold text-white">{{ $notification->title }}</h2>
                            @unless ($notification->read_at)
                                <span class="rounded-lg bg-brand-500/15 px-2 py-0.5 text-[10px] font-bold text-brand-300">Baru</span>
                            @endunless
                        </div>
                        <p class="mt-1.5 text-xs leading-relaxed text-slate-300">{{ $notification->message }}</p>
                        <p class="mt-2 text-[11px] text-slate-500">{{ optional($notification->created_at)->translatedFormat('d M Y H:i') }}</p>
                    </div>
                    @unless ($notification->read_at)
                        <form action="{{ route('membership.notifications.read', $notification) }}" method="POST" class="shrink-0">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="min-h-[44px] rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10 hover:text-white">Tandai dibaca</button>
                        </form>
                    @endunless
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-white/10 px-4 py-10 text-center">
                <p class="text-sm font-bold text-white">Belum ada notifikasi</p>
                <p class="mt-2 text-xs text-slate-400">Pengingat masa berlaku dan status pembayaran akan muncul di sini.</p>
            </div>
        @endforelse

        <div>{{ $notifications->links() }}</div>
    </div>
</div>
@endsection
