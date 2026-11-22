<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BroadcastNotification;
use App\Models\Role;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = BroadcastNotification::query()
            ->with('creator')
            ->withCount('reads')
            ->latest()
            ->paginate(15);

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'types' => BroadcastNotification::TYPES,
            'roles' => Role::all(),
        ]);
    }

    public function create(): View
    {
        return view('admin.notifications.create', [
            'types' => BroadcastNotification::TYPES,
            'roles' => Role::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
            'type' => ['required', 'in:' . implode(',', array_keys(BroadcastNotification::TYPES))],
            'target_role' => ['nullable', 'string', 'exists:roles,slug'],
            'action_url' => ['nullable', 'url', 'max:255'],
        ]);

        $data['created_by'] = Auth::id();

        $notification = BroadcastNotification::create($data);

        ActivityLogger::log('Notification', 'broadcast_sent', "Mengirim notifikasi broadcast: {$notification->title}", [
            'notification_id' => $notification->id,
            'target_role' => $notification->target_role ?: 'Semua Pengguna',
            'type' => $notification->type,
        ]);

        return redirect()->route('admin.notifications.index')->with('success', 'Notifikasi berhasil dikirim kepada pengguna.');
    }

    public function destroy(BroadcastNotification $notification): RedirectResponse
    {
        $id = $notification->id;
        $title = $notification->title;

        $notification->delete();

        ActivityLogger::log('Notification', 'deleted', "Menghapus notifikasi ID: {$id} ({$title})", ['notification_id' => $id]);

        return redirect()->route('admin.notifications.index')->with('success', 'Notifikasi berhasil dihapus.');
    }
}
