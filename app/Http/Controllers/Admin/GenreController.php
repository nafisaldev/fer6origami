<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\GeneratesUniqueSlug;
use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GenreController extends Controller
{
    use GeneratesUniqueSlug;

    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $genres = Genre::query()
            ->withCount('animes')
            ->when($search, fn ($q) => $q->where('name', 'like', '%' . $search . '%'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.genres.index', [
            'genres' => $genres,
            'search' => $search,
        ]);
    }

    public function show(Genre $genre): View
    {
        $genre->load('animes');

        return view('admin.genres.show', [
            'genre' => $genre,
        ]);
    }

    public function create(): View
    {
        return view('admin.genres.create', [
            'genre' => new Genre(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['slug'] = $this->generateUniqueSlug($data['name'], Genre::class);

        $genre = Genre::create($data);

        ActivityLogger::log('Genre', 'created', "Menambahkan genre: {$genre->name}", [
            'genre_id' => $genre->id,
            'name' => $genre->name,
        ]);

        return redirect()->route('admin.genres.index')->with('success', 'Genre berhasil ditambahkan.');
    }

    public function edit(Genre $genre): View
    {
        return view('admin.genres.edit', compact('genre'));
    }

    public function update(Request $request, Genre $genre): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['slug'] = $this->generateUniqueSlug($data['name'], Genre::class, $genre);

        $genre->update($data);

        ActivityLogger::log('Genre', 'updated', "Memperbarui genre: {$genre->name}", [
            'genre_id' => $genre->id,
            'name' => $genre->name,
        ]);

        return redirect()->route('admin.genres.index')->with('success', 'Genre berhasil diperbarui.');
    }

    public function destroy(Genre $genre): RedirectResponse
    {
        $name = $genre->name;
        $id = $genre->id;

        $genre->delete();

        ActivityLogger::log('Genre', 'deleted', "Menghapus genre: {$name}", [
            'genre_id' => $id,
        ]);

        return redirect()->route('admin.genres.index')->with('success', 'Genre berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
    }
}
