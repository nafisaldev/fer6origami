<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance Mode — Fer6origami</title>
    <meta name="theme-color" content="#090a0f">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-[#090a0f] p-4 font-sans text-slate-100 antialiased">
    <div class="relative w-full max-w-lg rounded-2xl border border-white/10 bg-[#11131b] p-8 text-center shadow-2xl">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-400 border border-amber-500/20">
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h1 class="mt-5 font-display text-2xl font-bold tracking-tight text-white">Maintenance Mode</h1>
        <p class="mt-3 text-sm text-slate-300 leading-relaxed">
            {{ $message ?? 'Fer6origami is currently undergoing scheduled maintenance. We will be back online shortly.' }}
        </p>
        <div class="mt-6 border-t border-white/5 pt-4 text-xs text-slate-400">
            If you are an administrator, you can <a href="{{ route('login') }}" class="font-bold text-brand-400 hover:underline">Log in here</a>.
        </div>
    </div>
</body>
</html>
