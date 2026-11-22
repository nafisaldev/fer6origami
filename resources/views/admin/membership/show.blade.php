@extends('layouts.app')

@section('title', 'Membership ' . $member->name . ' — Admin Fer6origami')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 border-b border-white/10 pb-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-white">Admin</a>
            <span>/</span>
            <a href="{{ route('admin.membership.index') }}" class="hover:text-white">Membership</a>
            <span>/</span>
            <span class="text-white">{{ $member->name }}</span>
        </div>
        <a href="{{ route('admin.membership.index') }}" class="self-start rounded-xl border border-white/10 px-4 py-2 text-xs font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white">&larr; Kembali</a>
    </div>

    {{-- MEMBER OVERVIEW --}}
    <div class="surface-panel rounded-2xl border border-white/10 p-6 shadow-elevated">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
            <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" class="h-16 w-16 shrink-0 rounded-2xl object-cover ring-2 ring-brand-500/40">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="font-display text-2xl font-bold text-white">{{ $member->name }}</h1>
                    @php
                        $statusStyles = [
                            'active' => 'bg-emerald-500/15 text-emerald-300',
                            'pending' => 'bg-amber-500/15 text-amber-300',
                            'expired' => 'bg-slate-500/15 text-slate-300',
                            'cancelled' => 'bg-rose-500/15 text-rose-300',
                        ];
                    @endphp
                    <span class="rounded px-2.5 py-0.5 text-[11px] font-bold {{ $statusStyles[$membership->status] ?? 'bg-slate-500/15 text-slate-300' }}">{{ strtoupper($membership->status) }}</span>
                    <span class="rounded bg-brand-500/15 px-2.5 py-0.5 text-[11px] font-bold text-brand-300">{{ $membership->plan->name ?? '-' }}</span>
                </div>
                <p class="mt-1 text-xs text-slate-400">{{ $member->email }}</p>

                <dl class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-xl border border-white/5 bg-white/[0.02] p-3">
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Paket Saat Ini</dt>
                        <dd class="mt-1 text-xs font-bold text-white">{{ $membership->plan->name ?? '-' }} <span class="font-normal text-slate-500">({{ $membership->plan->slug ?? '-' }})</span></dd>
                    </div>
                    <div class="rounded-xl border border-white/5 bg-white/[0.02] p-3">
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Mulai Berlaku</dt>
                        <dd class="mt-1 text-xs font-bold text-white">{{ optional($membership->starts_at)->format('d M Y H:i') ?: '-' }}</dd>
                    </div>
                    <div class="rounded-xl border border-white/5 bg-white/[0.02] p-3">
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Berakhir</dt>
                        <dd class="mt-1 text-xs font-bold text-white">{{ optional($membership->expires_at)->format('d M Y H:i') ?: 'Seumur hidup' }}</dd>
                    </div>
                    <div class="rounded-xl border border-white/5 bg-white/[0.02] p-3">
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Sumber</dt>
                        <dd class="mt-1 text-xs font-bold text-white">{{ $membership->source ?: '-' }}</dd>
                    </div>
                </dl>

                <div class="mt-4 flex flex-wrap gap-x-5 gap-y-1 text-[11px] text-slate-400">
                    <span>Dibuat oleh: <strong class="font-semibold text-slate-300">{{ $membership->creator->name ?? '-' }}</strong></span>
                    <span>Diperbarui oleh: <strong class="font-semibold text-slate-300">{{ $membership->updater->name ?? '-' }}</strong></span>
                </div>
                @if ($membership->notes)
                    <p class="mt-2 rounded-lg border border-white/5 bg-white/[0.02] px-3 py-2 text-[11px] text-slate-400">Catatan: {{ $membership->notes }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        {{-- ACTIVITY --}}
        <div class="space-y-6 xl:col-span-2">
            <section class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
                <h2 class="border-b border-white/5 px-5 py-4 text-xs font-bold uppercase tracking-wider text-slate-300">Langganan Terbaru ({{ $subscriptions->count() }})</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="border-b border-white/5 bg-[#11131b] text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Aksi</th>
                                <th class="px-4 py-3">Paket</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Durasi</th>
                                <th class="px-4 py-3">Mulai</th>
                                <th class="px-4 py-3">Berakhir</th>
                                <th class="px-4 py-3">Petugas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse ($subscriptions as $subscription)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-white">{{ $subscription->action }}</td>
                                    <td class="px-4 py-3">
                                        <p class="text-white">{{ $subscription->plan->name ?? '-' }}</p>
                                        <p class="text-[10px] text-slate-500">{{ $subscription->price->label ?? '-' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-slate-400">{{ $subscription->status }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ $subscription->duration_days ? $subscription->duration_days . ' hari' : '-' }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ optional($subscription->starts_at)->format('d M Y H:i') ?: '-' }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ optional($subscription->expires_at)->format('d M Y H:i') ?: '-' }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ $subscription->creator->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-8 text-center text-xs text-slate-500">Belum ada riwayat langganan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
                <h2 class="border-b border-white/5 px-5 py-4 text-xs font-bold uppercase tracking-wider text-slate-300">Riwayat Perubahan ({{ $histories->count() }})</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="border-b border-white/5 bg-[#11131b] text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Waktu</th>
                                <th class="px-4 py-3">Aksi</th>
                                <th class="px-4 py-3">Dari</th>
                                <th class="px-4 py-3">Ke</th>
                                <th class="px-4 py-3">Durasi</th>
                                <th class="px-4 py-3">Petugas</th>
                                <th class="px-4 py-3">Alasan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse ($histories as $history)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-400">{{ optional($history->created_at)->format('d M Y H:i') ?: '-' }}</td>
                                    <td class="px-4 py-3 font-semibold text-white">{{ $history->action }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ $history->fromPlan->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ $history->toPlan->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ $history->duration_days ? $history->duration_days . ' hari' : '-' }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ $history->actor->name ?? 'Sistem' }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ $history->reason ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-8 text-center text-xs text-slate-500">Belum ada riwayat perubahan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="surface-panel overflow-hidden rounded-2xl border border-white/10 shadow-elevated">
                <h2 class="border-b border-white/5 px-5 py-4 text-xs font-bold uppercase tracking-wider text-slate-300">Pembayaran Pengguna ({{ $payments->count() }})</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="border-b border-white/5 bg-[#11131b] text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Invoice</th>
                                <th class="px-4 py-3">Paket</th>
                                <th class="px-4 py-3">Total</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Waktu</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse ($payments as $payment)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-white">{{ $payment->invoice_number }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ $payment->plan->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-white">Rp {{ number_format((float) $payment->total_amount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ strtoupper($payment->status) }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ optional($payment->created_at)->format('d M Y H:i') ?: '-' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('admin.payments.show', $payment) }}" class="rounded-lg border border-white/10 px-2.5 py-1 text-[11px] font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-8 text-center text-xs text-slate-500">Belum ada pembayaran.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        {{-- MEMBERSHIP ACTIONS --}}
        <div class="space-y-6 xl:col-span-1">
            @can('manage', \App\Models\UserMembership::class)
                <form method="POST" action="{{ route('admin.membership.grant', $member) }}" class="surface-panel space-y-4 rounded-2xl border border-white/10 p-5 shadow-elevated">
                    @csrf
                    <input type="hidden" name="action" value="grant">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-brand-400">Berikan Membership</h2>
                        <p class="mt-1 text-[11px] text-slate-400">Aktifkan paket baru untuk pengguna ini. Durasi opsional untuk paket Free.</p>
                    </div>
                    @if ($errors->any() && old('action') === 'grant')
                        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-[11px] text-rose-200" role="alert">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif
                    <div>
                        <label for="grant-plan" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-300">Paket <span class="text-brand-500">*</span></label>
                        <select id="grant-plan" name="plan_id" required class="w-full rounded-xl border border-white/10 bg-[#161924] px-3.5 py-2 text-xs text-white focus:border-brand-500">
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" @selected((string) old('plan_id', $membership->plan_id) === (string) $plan->id)>{{ $plan->name }} ({{ $plan->slug }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="grant-duration" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-300">Durasi (Hari)</label>
                        <input id="grant-duration" type="number" name="duration_days" min="1" max="3650" value="{{ old('duration_days') }}" placeholder="Contoh: 30" class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs text-white placeholder:text-slate-500">
                    </div>
                    <div>
                        <label for="grant-reason" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-300">Alasan</label>
                        <textarea id="grant-reason" name="reason" rows="2" maxlength="500" placeholder="Alasan pemberian paket (opsional)" class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs text-white placeholder:text-slate-500">{{ old('reason') }}</textarea>
                    </div>
                    <button type="submit" class="w-full rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white transition hover:bg-brand-400">Berikan Membership</button>
                </form>

                <form method="POST" action="{{ route('admin.membership.renew', $member) }}" class="surface-panel space-y-4 rounded-2xl border border-white/10 p-5 shadow-elevated">
                    @csrf
                    <input type="hidden" name="action" value="renew">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-brand-400">Perpanjang Membership</h2>
                        <p class="mt-1 text-[11px] text-slate-400">Tambah masa aktif paket pengguna sesuai durasi yang ditentukan.</p>
                    </div>
                    @if ($errors->any() && old('action') === 'renew')
                        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-[11px] text-rose-200" role="alert">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif
                    <div>
                        <label for="renew-plan" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-300">Paket <span class="text-brand-500">*</span></label>
                        <select id="renew-plan" name="plan_id" required class="w-full rounded-xl border border-white/10 bg-[#161924] px-3.5 py-2 text-xs text-white focus:border-brand-500">
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" @selected((string) old('plan_id', $membership->plan_id) === (string) $plan->id)>{{ $plan->name }} ({{ $plan->slug }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="renew-duration" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-300">Durasi (Hari) <span class="text-brand-500">*</span></label>
                        <input id="renew-duration" type="number" name="duration_days" min="1" max="3650" value="{{ old('duration_days') }}" placeholder="Contoh: 30" required class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs text-white placeholder:text-slate-500">
                    </div>
                    <button type="submit" class="w-full rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white transition hover:bg-brand-400">Perpanjang Membership</button>
                </form>

                <form method="POST" action="{{ route('admin.membership.change', $member) }}" class="surface-panel space-y-4 rounded-2xl border border-white/10 p-5 shadow-elevated">
                    @csrf
                    <input type="hidden" name="action" value="change">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-brand-400">Ubah Paket</h2>
                        <p class="mt-1 text-[11px] text-slate-400">Ganti tier membership. Masa aktif yang tersisa akan dipertahankan bila durasi dikosongkan.</p>
                    </div>
                    @if ($errors->any() && old('action') === 'change')
                        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-[11px] text-rose-200" role="alert">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif
                    <div>
                        <label for="change-plan" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-300">Paket Baru <span class="text-brand-500">*</span></label>
                        <select id="change-plan" name="plan_id" required class="w-full rounded-xl border border-white/10 bg-[#161924] px-3.5 py-2 text-xs text-white focus:border-brand-500">
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" @selected((string) old('plan_id') === (string) $plan->id)>{{ $plan->name }} ({{ $plan->slug }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="change-duration" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-300">Durasi Baru (Hari)</label>
                        <input id="change-duration" type="number" name="duration_days" min="1" max="3650" value="{{ old('duration_days') }}" placeholder="Kosongkan untuk mempertahankan masa aktif" class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs text-white placeholder:text-slate-500">
                    </div>
                    <div>
                        <label for="change-reason" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-300">Alasan</label>
                        <textarea id="change-reason" name="reason" rows="2" maxlength="500" placeholder="Alasan perubahan paket (opsional)" class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs text-white placeholder:text-slate-500">{{ old('reason') }}</textarea>
                    </div>
                    <button type="submit" class="w-full rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white transition hover:bg-white/20">Ubah Paket</button>
                </form>

                <form method="POST" action="{{ route('admin.membership.revoke', $member) }}" class="surface-panel space-y-4 rounded-2xl border border-rose-500/20 p-5 shadow-elevated">
                    @csrf
                    <input type="hidden" name="action" value="revoke">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-rose-300">Cabut Membership</h2>
                        <p class="mt-1 text-[11px] text-slate-400">Kembalikan pengguna ke paket default Free dan hentikan akses berbayar.</p>
                    </div>
                    @if ($errors->any() && old('action') === 'revoke')
                        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-[11px] text-rose-200" role="alert">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif
                    <div>
                        <label for="revoke-reason" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-300">Alasan Pencabutan</label>
                        <textarea id="revoke-reason" name="reason" rows="3" maxlength="500" placeholder="Contoh: Pelanggaran ketentuan penggunaan" class="w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs text-white placeholder:text-slate-500">{{ old('reason') }}</textarea>
                    </div>
                    <button type="submit" class="w-full rounded-xl bg-rose-500/15 px-4 py-2 text-xs font-bold text-rose-300 transition hover:bg-rose-500 hover:text-white">Cabut Membership</button>
                </form>
            @else
                <div class="surface-panel rounded-2xl border border-white/10 p-5 text-xs text-slate-400">
                    Anda tidak memiliki izin untuk mengubah membership pengguna.
                </div>
            @endcan
        </div>
    </div>
</div>
@endsection
