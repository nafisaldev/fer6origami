<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SystemHealthService
{
    public function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug'),
            'timezone' => config('app.timezone'),
            'os' => PHP_OS . ' (' . php_uname('s') . ')',
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'PHP CLI / Built-in',
            'ip' => request()->server('SERVER_ADDR', '127.0.0.1'),
        ];
    }

    public function getMemoryInfo(): array
    {
        $currentBytes = memory_get_usage(true);
        $peakBytes = memory_get_peak_usage(true);
        $limit = ini_get('memory_limit');

        return [
            'current' => $this->formatBytes($currentBytes),
            'peak' => $this->formatBytes($peakBytes),
            'limit' => $limit,
            'current_bytes' => $currentBytes,
        ];
    }

    public function getDiskInfo(): array
    {
        $path = base_path();
        $totalBytes = @disk_total_space($path) ?: 1;
        $freeBytes = @disk_free_space($path) ?: 0;
        $usedBytes = max(0, $totalBytes - $freeBytes);
        $usedPercent = round(($usedBytes / $totalBytes) * 100, 1);

        return [
            'total' => $this->formatBytes($totalBytes),
            'free' => $this->formatBytes($freeBytes),
            'used' => $this->formatBytes($usedBytes),
            'used_percent' => $usedPercent,
        ];
    }

    public function getStorageUsage(): array
    {
        return [
            'app' => $this->formatBytes($this->getDirectorySize(storage_path('app'))),
            'logs' => $this->formatBytes($this->getDirectorySize(storage_path('logs'))),
            'framework' => $this->formatBytes($this->getDirectorySize(storage_path('framework'))),
            'public' => $this->formatBytes($this->getDirectorySize(public_path('storage'))),
        ];
    }

    public function getDatabaseInfo(): array
    {
        try {
            $pdo = DB::connection()->getPdo();
            $dbName = DB::connection()->getDatabaseName();
            $version = $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);

            $tables = DB::select('SHOW TABLE STATUS');
            $tableCount = count($tables);
            $totalSizeBytes = 0;

            foreach ($tables as $table) {
                $totalSizeBytes += ($table->Data_length ?? 0) + ($table->Index_length ?? 0);
            }

            return [
                'status' => 'Connected',
                'database' => $dbName,
                'driver' => DB::connection()->getDriverName(),
                'version' => $version,
                'table_count' => $tableCount,
                'size' => $this->formatBytes($totalSizeBytes),
                'size_bytes' => $totalSizeBytes,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'Error: ' . $e->getMessage(),
                'database' => config('database.connections.mysql.database'),
                'driver' => 'mysql',
                'version' => 'Unknown',
                'table_count' => 0,
                'size' => '0 MB',
                'size_bytes' => 0,
            ];
        }
    }

    public function getLogEntries(int $limit = 50): array
    {
        $logFile = storage_path('logs/laravel.log');
        if (!File::exists($logFile)) {
            return [];
        }

        $content = File::get($logFile);
        $pattern = '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.*?)(?=\n\[\d{4}-\d{2}-\d{2}|\z)/s';
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        $entries = [];
        $matches = array_reverse($matches);

        foreach (array_slice($matches, 0, $limit) as $match) {
            $entries[] = [
                'timestamp' => $match[1],
                'environment' => $match[2],
                'level' => strtoupper($match[3]),
                'message' => trim($match[4]),
            ];
        }

        return $entries;
    }

    public function clearLogs(): bool
    {
        $logFile = storage_path('logs/laravel.log');
        if (File::exists($logFile)) {
            File::put($logFile, '');
            return true;
        }

        return false;
    }

    public function clearCache(string $type = 'all'): array
    {
        $results = [];

        if ($type === 'all' || $type === 'application') {
            Artisan::call('cache:clear');
            $results['application'] = 'Application cache cleared.';
        }

        if ($type === 'all' || $type === 'config') {
            Artisan::call('config:clear');
            $results['config'] = 'Configuration cache cleared.';
        }

        if ($type === 'all' || $type === 'route') {
            Artisan::call('route:clear');
            $results['route'] = 'Route cache cleared.';
        }

        if ($type === 'all' || $type === 'view') {
            Artisan::call('view:clear');
            $results['view'] = 'Compiled views cleared.';
        }

        if ($type === 'optimize') {
            Artisan::call('optimize:clear');
            $results['optimize'] = 'Optimized caches cleared.';
        }

        return $results;
    }

    public function createDatabaseBackup(): string
    {
        $backupDir = storage_path('app/backups');
        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $filename = 'backup_' . date('Y_m_d_His') . '.sql';
        $filepath = $backupDir . '/' . $filename;

        $tables = DB::select('SHOW TABLES');
        $dbName = DB::connection()->getDatabaseName();
        $tableKey = 'Tables_in_' . $dbName;

        $sql = "-- Fer6origami Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $t) {
            $table = $t->$tableKey;
            $createTable = DB::select("SHOW CREATE TABLE `{$table}`");
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createTable[0]->{'Create Table'} . ";\n\n";

            $rows = DB::table($table)->get();
            if ($rows->count() > 0) {
                foreach ($rows as $row) {
                    $rowArray = (array) $row;
                    $columns = array_map(fn ($c) => "`{$c}`", array_keys($rowArray));
                    $values = array_map(function ($val) {
                        if (is_null($val)) return 'NULL';
                        return "'" . addslashes((string) $val) . "'";
                    }, array_values($rowArray));

                    $sql .= "INSERT INTO `{$table}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        File::put($filepath, $sql);

        return $filename;
    }

    public function getBackupsList(): array
    {
        $backupDir = storage_path('app/backups');
        if (!File::isDirectory($backupDir)) {
            return [];
        }

        $files = File::files($backupDir);
        $backups = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'sql') {
                $backups[] = [
                    'filename' => $file->getFilename(),
                    'size' => $this->formatBytes($file->getSize()),
                    'size_bytes' => $file->getSize(),
                    'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
                ];
            }
        }

        usort($backups, fn ($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return $backups;
    }

    public function restoreBackup(string $filename): bool
    {
        $filepath = storage_path('app/backups/' . basename($filename));
        if (!File::exists($filepath)) {
            throw new \Exception("File backup {$filename} tidak ditemukan.");
        }

        $sql = File::get($filepath);
        if (blank($sql)) {
            throw new \Exception("File backup kosong.");
        }

        DB::unprepared($sql);
        return true;
    }

    public function deleteBackup(string $filename): bool
    {
        $filepath = storage_path('app/backups/' . basename($filename));
        if (File::exists($filepath)) {
            return File::delete($filepath);
        }

        return false;
    }

    private function getDirectorySize(string $path): int
    {
        if (!File::isDirectory($path)) {
            return 0;
        }

        $size = 0;
        foreach (File::allFiles($path) as $file) {
            $size += $file->getSize();
        }

        return $size;
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
