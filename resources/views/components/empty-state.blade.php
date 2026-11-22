@props(['title', 'message', 'actionUrl' => null, 'actionText' => null])

<div class="surface-panel flex flex-col items-center justify-center rounded-2xl border border-dashed border-white/10 px-6 py-16 text-center">
    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/5 text-slate-400">
        <svg class="h-7 w-7 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
        </svg>
    </div>
    <h3 class="mt-4 font-display text-xl font-bold text-white">{{ $title }}</h3>
    <p class="mt-2 max-w-md text-sm text-slate-400">{{ $message }}</p>
    @if ($actionUrl && $actionText)
        <a href="{{ $actionUrl }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white transition hover:bg-brand-600">
            {{ $actionText }}
        </a>
    @endif
</div>
