<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\SystemHealthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatabaseBackupController extends Controller
{
    public function __construct(private readonly SystemHealthService $systemHealth)
    {
    }

    public function index(): View
    {
        $backups = $this->systemHealth->getBackupsList();
        $dbInfo = $this->systemHealth->getDatabaseInfo();

        return view('admin.system.backup', [
            'backups' => $backups,
            'dbInfo' => $dbInfo,
        ]);
    }

    public function store(): RedirectResponse
    {
        try {
            $filename = $this->systemHealth->createDatabaseBackup();

            ActivityLogger::log('System', 'db_backup_created', "Membuat backup database: {$filename}", [
                'filename' => $filename,
            ]);

            return back()->with('success', "Database berhasil di-backup: {$filename}");
        } catch (\Throwable $e) {
            return back()->withErrors(['backup' => 'Gagal membuat backup: ' . $e->getMessage()]);
        }
    }

    public function download(string $filename): BinaryFileResponse|RedirectResponse
    {
        $filename = basename($filename);
        $filepath = storage_path('app/backups/' . $filename);

        if (!File::exists($filepath)) {
            return back()->withErrors(['backup' => 'File backup tidak ditemukan.']);
        }

        ActivityLogger::log('System', 'db_backup_downloaded', "Mengunduh file backup database: {$filename}");

        return response()->download($filepath);
    }

    public function restore(Request $request): RedirectResponse
    {
        $request->validate([
            'filename' => ['required', 'string'],
            'confirmation' => ['required', 'in:RESTORE'],
        ]);

        $filename = basename($request->input('filename'));

        try {
            $this->systemHealth->restoreBackup($filename);

            ActivityLogger::log('System', 'db_restored', "Melakukan restore database dari backup: {$filename}", [
                'filename' => $filename,
            ]);

            return back()->with('success', "Database berhasil di-restore dari backup {$filename}!");
        } catch (\Throwable $e) {
            return back()->withErrors(['backup' => 'Gagal restore database: ' . $e->getMessage()]);
        }
    }

    public function destroy(string $filename): RedirectResponse
    {
        $filename = basename($filename);
        $deleted = $this->systemHealth->deleteBackup($filename);

        if ($deleted) {
            ActivityLogger::log('System', 'db_backup_deleted', "Menghapus file backup database: {$filename}");
            return back()->with('success', "File backup {$filename} berhasil dihapus.");
        }

        return back()->withErrors(['backup' => 'File backup gagal dihapus atau tidak ditemukan.']);
    }
}
