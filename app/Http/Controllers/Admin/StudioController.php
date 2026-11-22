<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\GeneratesUniqueSlug;
use App\Http\Controllers\Controller;
use App\Models\Studio;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StudioController extends Controller
{
    use GeneratesUniqueSlug;

    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $studios = Studio::query()
            ->withCount('animes')
            ->when($search, fn ($q) => $q->where('name', 'like', '%' . $search . '%')->orWhere('country', 'like', '%' . $search . '%'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.studios.index', [
            'studios' => $studios,
            'search' => $search,
        ]);
    }

    public function show(Studio $studio): View
    {
        $studio->load('animes');

        return view('admin.studios.show', [
            'studio' => $studio,
        ]);
    }

    public function create(): View
    {
        return view('admin.studios.create', [
            'studio' => new Studio(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['slug'] = $this->generateUniqueSlug($data['name'], Studio::class);
        $data['logo_path'] = $this->storeLogo($request);

        $studio = Studio::create($data);

        ActivityLogger::log('Studio', 'created', "Menambahkan studio animasi: {$studio->name}", [
            'studio_id' => $studio->id,
            'name' => $studio->name,
        ]);

        return redirect()->route('admin.studios.index')->with('success', 'Studio berhasil ditambahkan.');
    }

    public function edit(Studio $studio): View
    {
        return view('admin.studios.edit', [
            'studio' => $studio,
        ]);
    }

    public function update(Request $request, Studio $studio): RedirectResponse
    {
        $data = $this->validatedData($request, $studio->id);
        $data['slug'] = $this->generateUniqueSlug($data['name'], Studio::class, $studio);
        $data['logo_path'] = $this->storeLogo($request, $studio->logo_path);

        $studio->update($data);

        ActivityLogger::log('Studio', 'updated', "Memperbarui studio: {$studio->name}", [
            'studio_id' => $studio->id,
            'name' => $studio->name,
        ]);

        return redirect()->route('admin.studios.index')->with('success', 'Studio berhasil diperbarui.');
    }

    public function destroy(Studio $studio): RedirectResponse
    {
        $name = $studio->name;
        $id = $studio->id;

        if ($studio->logo_path && !str_starts_with($studio->logo_path, 'http') && Storage::disk('public')->exists($studio->logo_path)) {
            Storage::disk('public')->delete($studio->logo_path);
        }

        $studio->delete();

        ActivityLogger::log('Studio', 'deleted', "Menghapus studio: {$name}", [
            'studio_id' => $id,
        ]);

        return redirect()->route('admin.studios.index')->with('success', 'Studio berhasil dihapus.');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'country' => ['nullable', 'string', 'max:100'],
            'founded_year' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'website' => ['nullable', 'url', 'max:255'],
            'logo_upload' => ['nullable', 'image', 'max:2048'],
        ]);
    }

    private function storeLogo(Request $request, ?string $oldValue = null): ?string
    {
        if (! $request->hasFile('logo_upload')) {
            return $oldValue;
        }

        if ($oldValue && ! str_starts_with($oldValue, 'http') && Storage::disk('public')->exists($oldValue)) {
            Storage::disk('public')->delete($oldValue);
        }

        return $request->file('logo_upload')->store('studios', 'public');
    }
}
