<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\GeneratesUniqueSlug;
use App\Http\Controllers\Controller;
use App\Models\CustomPage;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomPageController extends Controller
{
    use GeneratesUniqueSlug;

    public function index(): View
    {
        $pages = CustomPage::latest()->paginate(15);

        return view('admin.pages.index', compact('pages'));
    }

    public function create(): View
    {
        return view('admin.pages.create', [
            'page' => new CustomPage(['is_published' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['slug'] = $this->generateUniqueSlug($data['title'], CustomPage::class);
        $data['is_published'] = $request->boolean('is_published');

        $page = CustomPage::create($data);

        ActivityLogger::log('Page', 'created', "Membuat halaman kustom: {$page->title}", ['page_id' => $page->id]);

        return redirect()->route('admin.pages.index')->with('success', 'Halaman berhasil dibuat.');
    }

    public function edit(CustomPage $page): View
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, CustomPage $page): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['slug'] = $this->generateUniqueSlug($data['title'], CustomPage::class, $page);
        $data['is_published'] = $request->boolean('is_published');

        $page->update($data);

        ActivityLogger::log('Page', 'updated', "Memperbarui halaman kustom: {$page->title}", ['page_id' => $page->id]);

        return redirect()->route('admin.pages.index')->with('success', 'Halaman berhasil diperbarui.');
    }

    public function destroy(CustomPage $page): RedirectResponse
    {
        $title = $page->title;
        $id = $page->id;

        $page->delete();

        ActivityLogger::log('Page', 'deleted', "Menghapus halaman kustom: {$title}", ['page_id' => $id]);

        return redirect()->route('admin.pages.index')->with('success', 'Halaman berhasil dihapus.');
    }

    public function showPublic(string $slug): View
    {
        $page = CustomPage::where('slug', $slug)->where('is_published', true)->firstOrFail();

        return view('pages.show', compact('page'));
    }
}
