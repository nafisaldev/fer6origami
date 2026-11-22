<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\User;
use App\Models\VisitorLog;
use App\Models\WatchHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->query('period', '30'); // 7, 30, 90, all
        $startDate = match ($period) {
            '7' => Carbon::now()->subDays(7)->startOfDay(),
            '30' => Carbon::now()->subDays(30)->startOfDay(),
            '90' => Carbon::now()->subDays(90)->startOfDay(),
            default => Carbon::createFromTimestamp(0),
        };

        // Total views in period
        $historyQuery = WatchHistory::query();
        if ($period !== 'all') {
            $historyQuery->where('created_at', '>=', $startDate);
        }

        $totalViews = (clone $historyQuery)->count();
        $totalDurationSeconds = (clone $historyQuery)->sum('duration_seconds');
        $uniqueUsers = (clone $historyQuery)->whereNotNull('user_id')->distinct('user_id')->count('user_id');
        $completedViews = (clone $historyQuery)->where('is_completed', true)->count();

        // Most Popular Anime in period
        $popularAnime = Anime::query()
            ->withCount(['watchHistories' => function ($q) use ($startDate, $period) {
                if ($period !== 'all') {
                    $q->where('created_at', '>=', $startDate);
                }
            }])
            ->orderByDesc('watch_histories_count')
            ->orderByDesc('views')
            ->take(10)
            ->get();

        // Most Watched Episodes
        $popularEpisodes = Episode::query()
            ->with('anime')
            ->withCount(['watchHistories' => function ($q) use ($startDate, $period) {
                if ($period !== 'all') {
                    $q->where('created_at', '>=', $startDate);
                }
            }])
            ->orderByDesc('watch_histories_count')
            ->take(10)
            ->get();

        // Daily Trend Data for Chart
        $daysCount = $period === '7' ? 7 : ($period === '90' ? 90 : 30);
        $trendData = [];
        for ($i = $daysCount - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $start = $date->copy()->startOfDay();
            $end = $date->copy()->endOfDay();
            $viewsCount = WatchHistory::whereBetween('created_at', [$start, $end])->count();
            $trendData[] = [
                'date' => $date->format('d M'),
                'views' => $viewsCount,
            ];
        }

        // Active Users by Views
        $topUsers = User::query()
            ->withCount(['watchHistories' => function ($q) use ($startDate, $period) {
                if ($period !== 'all') {
                    $q->where('created_at', '>=', $startDate);
                }
            }])
            ->having('watch_histories_count', '>', 0)
            ->orderByDesc('watch_histories_count')
            ->take(8)
            ->get();

        // Top Visiting Devices in period
        $topDevices = VisitorLog::selectRaw('device_name, device_type, count(*) as count')
            ->whereNotNull('device_name')
            ->when($period !== 'all', fn ($q) => $q->where('created_at', '>=', $startDate))
            ->groupBy('device_name', 'device_type')
            ->orderByDesc('count')
            ->take(6)
            ->get();

        return view('admin.analytics.index', [
            'period' => $period,
            'totalViews' => $totalViews,
            'totalDurationHours' => round($totalDurationSeconds / 3600, 1),
            'uniqueUsers' => $uniqueUsers,
            'completedViews' => $completedViews,
            'popularAnime' => $popularAnime,
            'popularEpisodes' => $popularEpisodes,
            'trendData' => $trendData,
            'topUsers' => $topUsers,
            'topDevices' => $topDevices,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'analytics_report_' . date('Y_m_d_His') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Anime Title', 'Episode', 'User Name', 'IP Address', 'Duration (Seconds)', 'Completed', 'Watched At']);

            WatchHistory::with(['anime', 'episode', 'user'])
                ->latest()
                ->chunk(200, function ($records) use ($handle) {
                    foreach ($records as $item) {
                        fputcsv($handle, [
                            $item->id,
                            $item->anime->title ?? 'N/A',
                            $item->episode ? "Ep {$item->episode->episode_number}" : 'Series',
                            $item->user->name ?? 'Guest',
                            $item->ip_address,
                            $item->duration_seconds,
                            $item->is_completed ? 'Yes' : 'No',
                            $item->created_at->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}
