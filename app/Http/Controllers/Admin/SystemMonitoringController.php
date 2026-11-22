<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\SystemHealthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemMonitoringController extends Controller
{
    public function __construct(private readonly SystemHealthService $systemHealth)
    {
    }

    public function index(Request $request): View
    {
        $logLevel = $request->query('level');

        $systemInfo = $this->systemHealth->getSystemInfo();
        $memoryInfo = $this->systemHealth->getMemoryInfo();
        $diskInfo = $this->systemHealth->getDiskInfo();
        $storageUsage = $this->systemHealth->getStorageUsage();
        $databaseInfo = $this->systemHealth->getDatabaseInfo();
        $logs = $this->systemHealth->getLogEntries(60);

        if ($logLevel) {
            $logs = array_filter($logs, fn ($item) => $item['level'] === strtoupper($logLevel));
        }

        return view('admin.system.monitoring', [
            'systemInfo' => $systemInfo,
            'memoryInfo' => $memoryInfo,
            'diskInfo' => $diskInfo,
            'storageUsage' => $storageUsage,
            'databaseInfo' => $databaseInfo,
            'logs' => $logs,
            'selectedLevel' => $logLevel,
        ]);
    }

    public function clearLogs(): RedirectResponse
    {
        $this->systemHealth->clearLogs();

        ActivityLogger::log('System', 'logs_cleared', 'Membersihkan file log Laravel storage/logs/laravel.log');

        return back()->with('success', 'File error log Laravel berhasil dibersihkan.');
    }
}
