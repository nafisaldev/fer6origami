<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VisitorLog;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitorLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $deviceType = $request->query('device_type');
        $dateFilter = $request->query('date');

        $query = VisitorLog::with('user')->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                    ->orWhere('device_name', 'like', "%{$search}%")
                    ->orWhere('browser', 'like', "%{$search}%")
                    ->orWhere('platform', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($deviceType && in_array($deviceType, ['Desktop', 'Mobile', 'Tablet', 'Bot'])) {
            $query->where('device_type', $deviceType);
        }

        if ($dateFilter === 'today') {
            $query->where('created_at', '>=', Carbon::now()->startOfDay());
        } elseif ($dateFilter === 'week') {
            $query->where('created_at', '>=', Carbon::now()->subDays(7)->startOfDay());
        } elseif ($dateFilter === 'month') {
            $query->where('created_at', '>=', Carbon::now()->subDays(30)->startOfDay());
        }

        $visitorLogs = $query->paginate(25)->withQueryString();

        // Summary metrics
        $now = Carbon::now();
        $totalLogs = VisitorLog::count();
        $todayUnique = VisitorLog::where('created_at', '>=', $now->copy()->startOfDay())->distinct('ip_address')->count('ip_address');
        $mobileCount = VisitorLog::where('device_type', 'Mobile')->count();
        $desktopCount = VisitorLog::where('device_type', 'Desktop')->count();
        $tabletCount = VisitorLog::where('device_type', 'Tablet')->count();

        $topDevices = VisitorLog::selectRaw('device_name, device_type, count(*) as count')
            ->whereNotNull('device_name')
            ->groupBy('device_name', 'device_type')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        return view('admin.visitor_logs.index', [
            'visitorLogs' => $visitorLogs,
            'search' => $search,
            'deviceType' => $deviceType,
            'dateFilter' => $dateFilter,
            'metrics' => [
                'total' => $totalLogs,
                'today_unique' => $todayUnique,
                'mobile' => $mobileCount,
                'desktop' => $desktopCount,
                'tablet' => $tabletCount,
                'mobile_pct' => $totalLogs > 0 ? round(($mobileCount / $totalLogs) * 100, 1) : 0,
                'desktop_pct' => $totalLogs > 0 ? round(($desktopCount / $totalLogs) * 100, 1) : 0,
            ],
            'topDevices' => $topDevices,
        ]);
    }

    public function destroy(VisitorLog $visitorLog): RedirectResponse
    {
        $visitorLog->delete();

        return back()->with('success', 'Log pengunjung berhasil dihapus.');
    }

    public function clearOld(): RedirectResponse
    {
        $count = VisitorLog::where('created_at', '<', Carbon::now()->subDays(30))->delete();

        ActivityLogger::log('System', 'cleaned', "Membersihkan {$count} data log pengunjung yang berusia lebih dari 30 hari.");

        return back()->with('success', "{$count} data log pengunjung lama berhasil dibersihkan.");
    }
}
