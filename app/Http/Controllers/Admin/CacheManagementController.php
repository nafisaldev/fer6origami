<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\SystemHealthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CacheManagementController extends Controller
{
    public function __construct(private readonly SystemHealthService $systemHealth)
    {
    }

    public function index(): View
    {
        $storageUsage = $this->systemHealth->getStorageUsage();

        return view('admin.system.cache', [
            'storageUsage' => $storageUsage,
        ]);
    }

    public function clear(Request $request): RedirectResponse
    {
        $type = $request->input('type', 'all');
        $validTypes = ['all', 'application', 'config', 'route', 'view', 'optimize'];

        if (!in_array($type, $validTypes)) {
            return back()->withErrors(['type' => 'Jenis cache tidak valid.']);
        }

        $results = $this->systemHealth->clearCache($type);

        ActivityLogger::log('System', 'cache_cleared', "Membersihkan cache sistem: {$type}", $results);

        $msg = match ($type) {
            'application' => 'Application cache berhasil dibersihkan.',
            'config' => 'Configuration cache berhasil dibersihkan.',
            'route' => 'Route cache berhasil dibersihkan.',
            'view' => 'Compiled views cache berhasil dibersihkan.',
            'optimize' => 'Optimization cache berhasil di-reset.',
            default => 'Seluruh cache sistem (Application, Config, Route, View) berhasil dibersihkan!',
        };

        return back()->with('success', $msg);
    }
}
