<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Anime;
use App\Models\Comment;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Report;
use App\Models\Review;
use App\Models\User;
use App\Models\VisitorLog;
use App\Models\WatchHistory;
use App\Services\MembershipService;
use App\Services\SystemHealthService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly SystemHealthService $systemHealth,
        private readonly MembershipService $membership,
    ) {
    }

    public function index(): View
    {
        $now = Carbon::now();
        $todayStart = $now->copy()->startOfDay();
        $weekStart = $now->copy()->subDays(7)->startOfDay();
        $monthStart = $now->copy()->subDays(30)->startOfDay();

        // 1. Overview Counter Stats
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $totalAnime = Anime::count();
        $totalEpisodes = Episode::count();
        $totalGenres = Genre::count();
        $totalViews = WatchHistory::count() + (int) Anime::sum('views');
        $totalComments = Comment::count();
        $totalReports = Report::count();
        $pendingReports = Report::where('status', 'pending')->count();

        // 2. Periodic Visitors Stats (Total & Unique Site Visits)
        $visitorsToday = max(
            VisitorLog::where('created_at', '>=', $todayStart)->distinct('ip_address')->count('ip_address'),
            VisitorLog::where('created_at', '>=', $todayStart)->count(),
            WatchHistory::where('created_at', '>=', $todayStart)->count()
        );
        $visitorsWeek = max(
            VisitorLog::where('created_at', '>=', $weekStart)->distinct('ip_address')->count('ip_address'),
            VisitorLog::where('created_at', '>=', $weekStart)->count(),
            WatchHistory::where('created_at', '>=', $weekStart)->count()
        );
        $visitorsMonth = max(
            VisitorLog::where('created_at', '>=', $monthStart)->distinct('ip_address')->count('ip_address'),
            VisitorLog::where('created_at', '>=', $monthStart)->count(),
            WatchHistory::where('created_at', '>=', $monthStart)->count()
        );

        // 3. User Growth Chart (Last 7 Days)
        $userGrowthDays = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i);
            $startOfDay = $day->copy()->startOfDay();
            $endOfDay = $day->copy()->endOfDay();
            $label = $day->translatedFormat('D, d M');
            $count = User::whereBetween('created_at', [$startOfDay, $endOfDay])->count();
            $userGrowthDays[] = [
                'label' => $label,
                'count' => $count,
            ];
        }

        // 4. Watch Activity Chart (Last 7 Days)
        $watchActivityDays = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i);
            $startOfDay = $day->copy()->startOfDay();
            $endOfDay = $day->copy()->endOfDay();
            $label = $day->translatedFormat('D, d M');
            $count = WatchHistory::whereBetween('created_at', [$startOfDay, $endOfDay])->count();
            $watchActivityDays[] = [
                'label' => $label,
                'count' => $count,
            ];
        }

        // 5. Recent Website Activities
        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->take(8)
            ->get();

        // 5.1 Recent Visitors with Device Names
        $recentVisitors = VisitorLog::with('user')
            ->latest()
            ->take(6)
            ->get();

        // 6. Recent Items
        $latestAnimes = Anime::query()->with('genres')->latest()->take(5)->get();
        $latestEpisodes = Episode::query()->with('anime')->latest()->take(5)->get();
        $latestReports = Report::query()->with('user')->latest()->take(5)->get();
        $latestComments = Comment::query()->with(['user', 'anime'])->latest()->take(5)->get();

        // 7. System & Server Summary
        $systemInfo = $this->systemHealth->getSystemInfo();
        $memoryInfo = $this->systemHealth->getMemoryInfo();
        $diskInfo = $this->systemHealth->getDiskInfo();
        $dbInfo = $this->systemHealth->getDatabaseInfo();

        return view('admin.dashboard', [
            'stats' => [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'total_anime' => $totalAnime,
                'total_episodes' => $totalEpisodes,
                'total_genres' => $totalGenres,
                'total_views' => $totalViews,
                'total_comments' => $totalComments,
                'total_reports' => $totalReports,
                'pending_reports' => $pendingReports,
            ],
            'visitors' => [
                'today' => $visitorsToday,
                'week' => $visitorsWeek,
                'month' => $visitorsMonth,
            ],
            'userGrowthDays' => $userGrowthDays,
            'watchActivityDays' => $watchActivityDays,
            'recentActivities' => $recentActivities,
            'recentVisitors' => $recentVisitors,
            'latestAnimes' => $latestAnimes,
            'latestEpisodes' => $latestEpisodes,
            'latestReports' => $latestReports,
            'latestComments' => $latestComments,
            'membershipStats' => $this->membership->statistics(),
            'systemSummary' => [
                'php_version' => $systemInfo['php_version'],
                'laravel_version' => $systemInfo['laravel_version'],
                'db_status' => $dbInfo['status'],
                'db_size' => $dbInfo['size'],
                'memory_usage' => $memoryInfo['current'],
                'disk_usage' => $diskInfo['used'] . ' / ' . $diskInfo['total'] . ' (' . $diskInfo['used_percent'] . '%)',
                'env' => $systemInfo['app_env'],
            ],
        ]);
    }
}
