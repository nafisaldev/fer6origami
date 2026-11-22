<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WatchHistory;
use App\Models\Watchlist;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WatchActivityController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->query('type') === 'watchlist' ? 'watchlist' : 'history';
        $search = $request->string('search')->toString();

        $query = $type === 'watchlist'
            ? Watchlist::query()->with('user')
            : WatchHistory::query()->with(['user', 'anime', 'episode']);

        $query->when($search, function ($nested) use ($search, $type) {
            $nested->where(function ($inner) use ($search, $type) {
                if ($type === 'watchlist') {
                    $inner->where('anime_title', 'like', '%' . $search . '%')
                        ->orWhere('anime_api_id', 'like', '%' . $search . '%');
                } else {
                    $inner->where('anime_title', 'like', '%' . $search . '%')
                        ->orWhere('anime_slug', 'like', '%' . $search . '%')
                        ->orWhere('episode_title', 'like', '%' . $search . '%');
                }

                $inner->orWhereHas('user', fn ($user) => $user
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%'));
            });
        })->latest();

        return view('admin.watch-activity.index', [
            'items' => $query->paginate(15)->withQueryString(),
            'type' => $type,
            'search' => $search,
        ]);
    }

    public function destroyHistory(WatchHistory $watchHistory): RedirectResponse
    {
        $id = $watchHistory->id;
        $watchHistory->delete();

        ActivityLogger::log('WatchHistory', 'deleted', "Menghapus riwayat tontonan ID {$id}", [
            'watch_history_id' => $id,
        ]);

        return back()->with('success', 'Riwayat tontonan berhasil dihapus.');
    }

    public function destroyWatchlist(Watchlist $watchlist): RedirectResponse
    {
        $title = $watchlist->anime_title;
        $watchlist->delete();

        ActivityLogger::log('Watchlist', 'deleted', "Menghapus watchlist {$title}", [
            'watchlist_id' => $watchlist->id,
            'anime_title' => $title,
        ]);

        return back()->with('success', "Watchlist {$title} berhasil dihapus.");
    }
}
