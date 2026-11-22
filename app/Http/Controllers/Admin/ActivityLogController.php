<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $module = $request->query('module');
        $action = $request->query('action');
        $adminId = $request->query('admin_id');
        $search = $request->string('search')->toString();
        $date = $request->query('date');

        $logs = ActivityLog::query()
            ->with('user')
            ->when($module, fn ($q) => $q->where('module', $module))
            ->when($action, fn ($q) => $q->where('action', $action))
            ->when($adminId, fn ($q) => $q->where('user_id', $adminId))
            ->when($date, fn ($q) => $q->whereDate('created_at', $date))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($nested) use ($search) {
                    $nested->where('description', 'like', '%' . $search . '%')
                        ->orWhere('ip_address', 'like', '%' . $search . '%')
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%' . $search . '%'));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $modules = ActivityLog::distinct('module')->pluck('module')->filter()->values();
        $actions = ActivityLog::distinct('action')->pluck('action')->filter()->values();
        $admins = User::whereIn('role', ['super_admin', 'admin', 'anime_manager', 'content_editor', 'moderator'])
            ->orderBy('name')
            ->get();

        return view('admin.activity_logs.index', [
            'logs' => $logs,
            'modules' => $modules,
            'actions' => $actions,
            'admins' => $admins,
            'selectedModule' => $module,
            'selectedAction' => $action,
            'selectedAdmin' => $adminId,
            'selectedDate' => $date,
            'search' => $search,
        ]);
    }
}
