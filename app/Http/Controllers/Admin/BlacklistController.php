<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeletedAnime;
use App\Services\ActivityLogger;
use App\Services\DeletedAnimeRestoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlacklistController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $items = DeletedAnime::query()
            ->with('deletedBy')
            ->when($search, fn ($q) => $q
                ->where('title', 'like', '%' . $search . '%')
                ->orWhere('slug', 'like', '%' . $search . '%'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.blacklist.index', [
            'items' => $items,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.blacklist.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:deleted_animes,slug'],
        ]);

        $slug = filled($data['slug'] ?? null) ? Str::slug($data['slug']) : Str::slug($data['title']);

        if ($slug === '') {
            return back()->withErrors(['slug' => 'Slug tidak valid.'])->withInput();
        }

        if (DeletedAnime::where('slug', $slug)->exists()) {
            return back()->withErrors(['slug' => 'Slug ini sudah ada dalam blacklist.'])->withInput();
        }

        DeletedAnime::blacklistAnime($data['title'], $slug, Auth::id());

        ActivityLogger::log('DeletedAnime', 'created', "Memasukkan {$data['title']} ke blacklist anime", [
            'slug' => $slug,
        ]);

        return redirect()->route('admin.blacklist.index')
            ->with('success', "{$data['title']} berhasil dimasukkan ke blacklist.");
    }

    public function destroy(DeletedAnime $blacklist): RedirectResponse
    {
        $title = $blacklist->title;
        $slug = $blacklist->slug;
        $blacklist->delete();

        cache()->forget('deleted_anime_identifiers');

        ActivityLogger::log('DeletedAnime', 'deleted', "Menghapus {$title} dari blacklist anime", [
            'slug' => $slug,
        ]);

        return back()->with('success', "{$title} berhasil dihapus dari blacklist.");
    }

    public function restore(DeletedAnime $blacklist, DeletedAnimeRestoreService $restorer): RedirectResponse
    {
        $result = $restorer->restore($blacklist);

        if (! in_array($result['status'], ['restored', 'exists'], true)) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }
}
