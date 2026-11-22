@extends('layouts.app')

@section('title', 'My Watchlist — Fer6origami')

@section('content')
    <div class="space-y-6">
        {{-- WATCHLIST HEADER --}}
        <div class="surface-panel flex flex-col justify-between gap-4 rounded-xl p-6 md:flex-row md:items-center">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-brand-400">Personal Collection</span>
                <h1 class="mt-1 font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">
                    My Watchlist
                </h1>
                <p class="mt-1 text-xs text-slate-400">
                    Anime titles saved to your personal queue. Total {{ $watchlists->total() }} anime saved.
                </p>
            </div>

            <a href="{{ route('anime.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-brand-600">
                <span>Browse More Anime</span>
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </a>
        </div>

        {{-- WATCHLIST ITEMS GRID --}}
        <section>
            @if ($watchlists->count())
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                    @foreach ($watchlists as $watchlist)
                        <div class="flex flex-col justify-between space-y-2">
                            <x-anime-card :anime="$watchlist->toAnimeCard()" />
                            <form action="{{ route('watchlist.destroy', $watchlist) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full rounded-lg border border-white/10 bg-[#161924] py-1.5 text-[11px] font-semibold text-rose-300 transition hover:bg-rose-500/20 hover:text-white">
                                    Remove from Watchlist
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $watchlists->links() }}
                </div>
            @else
                <x-empty-state
                    title="Watchlist is Empty"
                    message="You have not saved any anime yet. Visit any anime overview page and click 'Add to Watchlist' to pin it here."
                    :actionUrl="route('anime.index')"
                    actionText="Explore Anime Catalog"
                />
            @endif
        </section>
    </div>
@endsection
