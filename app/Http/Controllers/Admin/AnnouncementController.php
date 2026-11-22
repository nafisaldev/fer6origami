<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\GeneratesUniqueSlug;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\BroadcastNotification;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    use GeneratesUniqueSlug;

    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $category = $request->query('category');
        $status = $request->query('status');

        $announcements = Announcement::query()
            ->with('creator')
            ->when($search, fn ($q) => $q->where('title', 'like', '%' . $search . '%')->orWhere('content', 'like', '%' . $search . '%'))
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.announcements.index', [
            'announcements' => $announcements,
            'search' => $search,
            'selectedCategory' => $category,
            'selectedStatus' => $status,
            'categories' => Announcement::CATEGORIES,
            'statuses' => Announcement::STATUSES,
        ]);
    }

    public function show(Announcement $announcement): View
    {
        $announcement->load('creator');

        return view('admin.announcements.show', [
            'announcement' => $announcement,
        ]);
    }

    public function showPublic(string $slug): View
    {
        $announcement = Announcement::where('slug', $slug)
            ->active()
            ->firstOrFail();

        $announcement->increment('reads_count');

        return view('announcements.show', [
            'announcement' => $announcement,
        ]);
    }

    public function create(): View
    {
        return view('admin.announcements.create', [
            'announcement' => new Announcement([
                'status' => 'published',
                'published_at' => now(),
            ]),
            'categories' => Announcement::CATEGORIES,
            'statuses' => Announcement::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['slug'] = $this->generateUniqueSlug($data['title'], Announcement::class);
        $data['created_by'] = Auth::id();
        $data['is_pinned'] = $request->boolean('is_pinned');
        $data['is_popup'] = $request->boolean('is_popup');
        $data['banner_path'] = $this->storeBanner($request);

        $announcement = Announcement::create($data);

        // Optional: Send notification bell if checked
        if ($request->boolean('send_notification_bell')) {
            BroadcastNotification::create([
                'title' => 'Pengumuman: ' . $announcement->title,
                'message' => substr(strip_tags($announcement->content), 0, 200) . '...',
                'type' => 'announcement',
                'action_url' => route('home'),
                'created_by' => Auth::id(),
            ]);
        }

        ActivityLogger::log('Announcement', 'created', "Membuat pengumuman: {$announcement->title}", [
            'announcement_id' => $announcement->id,
            'category' => $announcement->category,
            'status' => $announcement->status,
        ]);

        return redirect()->route('admin.announcements.index')->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function edit(Announcement $announcement): View
    {
        return view('admin.announcements.edit', [
            'announcement' => $announcement,
            'categories' => Announcement::CATEGORIES,
            'statuses' => Announcement::STATUSES,
        ]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $data = $this->validatedData($request, $announcement->id);
        $data['slug'] = $this->generateUniqueSlug($data['title'], Announcement::class, $announcement);
        $data['is_pinned'] = $request->boolean('is_pinned');
        $data['is_popup'] = $request->boolean('is_popup');
        $data['banner_path'] = $this->storeBanner($request, $announcement->banner_path);

        $announcement->update($data);

        ActivityLogger::log('Announcement', 'updated', "Memperbarui pengumuman: {$announcement->title}", [
            'announcement_id' => $announcement->id,
            'category' => $announcement->category,
            'status' => $announcement->status,
        ]);

        return redirect()->route('admin.announcements.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $title = $announcement->title;
        $id = $announcement->id;

        if ($announcement->banner_path && !str_starts_with($announcement->banner_path, 'http') && Storage::disk('public')->exists($announcement->banner_path)) {
            Storage::disk('public')->delete($announcement->banner_path);
        }

        $announcement->delete();

        ActivityLogger::log('Announcement', 'deleted', "Menghapus pengumuman: {$title}", ['announcement_id' => $id]);

        return redirect()->route('admin.announcements.index')->with('success', 'Pengumuman berhasil dihapus.');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'category' => ['required', 'in:' . implode(',', array_keys(Announcement::CATEGORIES))],
            'status' => ['required', 'in:' . implode(',', array_keys(Announcement::STATUSES))],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:published_at'],
            'banner_upload' => ['nullable', 'image', 'max:3072'],
        ]);
    }

    private function storeBanner(Request $request, ?string $oldValue = null): ?string
    {
        if (! $request->hasFile('banner_upload')) {
            return $oldValue;
        }

        if ($oldValue && ! str_starts_with($oldValue, 'http') && Storage::disk('public')->exists($oldValue)) {
            Storage::disk('public')->delete($oldValue);
        }

        return $request->file('banner_upload')->store('announcements', 'public');
    }
}
