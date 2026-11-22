<?php

use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Admin\AnimeController as AdminAnimeController;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\AppearanceController as AdminAppearanceController;
use App\Http\Controllers\Admin\BadgeController as AdminBadgeController;
use App\Http\Controllers\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Admin\BlacklistController as AdminBlacklistController;
use App\Http\Controllers\Admin\CacheManagementController as AdminCacheManagementController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Admin\MembershipController as AdminMembershipController;
use App\Http\Controllers\Admin\MembershipHistoryController as AdminMembershipHistoryController;
use App\Http\Controllers\Admin\MembershipPlanController as AdminMembershipPlanController;
use App\Http\Controllers\Admin\MembershipPriceController as AdminMembershipPriceController;
use App\Http\Controllers\Admin\ContentCleanupController as AdminContentCleanupController;
use App\Http\Controllers\Admin\CustomPageController as AdminCustomPageController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DatabaseBackupController as AdminDatabaseBackupController;
use App\Http\Controllers\Admin\EngagementController as AdminEngagementController;
use App\Http\Controllers\Admin\EpisodeController as AdminEpisodeController;
use App\Http\Controllers\Admin\GeneralSettingsController as AdminGeneralSettingsController;
use App\Http\Controllers\Admin\GenreController as AdminGenreController;
use App\Http\Controllers\Admin\HomepageController as AdminHomepageController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\RolePermissionController as AdminRolePermissionController;
use App\Http\Controllers\Admin\ScheduleController as AdminScheduleController;
use App\Http\Controllers\Admin\SecuritySettingsController as AdminSecuritySettingsController;
use App\Http\Controllers\Admin\SeoController as AdminSeoController;
use App\Http\Controllers\Admin\StudioController as AdminStudioController;
use App\Http\Controllers\Admin\SystemMonitoringController as AdminSystemMonitoringController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\VisitorLogController as AdminVisitorLogController;
use App\Http\Controllers\Admin\VoucherController as AdminVoucherController;
use App\Http\Controllers\Admin\WatchActivityController as AdminWatchActivityController;
use App\Http\Controllers\AnimeCatalogController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\ImageProxyController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserInteractionController;
use App\Http\Controllers\WatchController;
use App\Http\Controllers\WatchlistController;
use Illuminate\Support\Facades\Route;

Route::middleware('cache.headers:private;max_age=120;etag')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/anime', [AnimeCatalogController::class, 'index'])->name('anime.index');
    Route::get('/anime/{animeId}', [AnimeCatalogController::class, 'show'])
        ->where('animeId', '.*')
        ->name('anime.show');
    Route::get('/watch/{animeId}/{episodeId}', [WatchController::class, 'show'])
        ->where(['animeId' => '.*', 'episodeId' => '.*'])
        ->name('watch.show');
});

Route::get('/home', fn () => redirect()->route('home'));
Route::get('/image-proxy', ImageProxyController::class)->name('image.proxy');

Route::get('/robots.txt', function () {
    $content = \App\Models\WebsiteSetting::get('robots_txt', "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /api/");
    return response($content, 200, ['Content-Type' => 'text/plain']);
});

Route::get('/sitemap.xml', function () {
    $baseUrl = url('/');
    $animes = \App\Models\Anime::latest()->take(500)->get();
    $pages = \App\Models\CustomPage::where('is_published', true)->get();
    $announcements = \App\Models\Announcement::active()->get();

    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    $xml .= '<url><loc>' . $baseUrl . '</loc><priority>1.0</priority><changefreq>daily</changefreq></url>';
    $xml .= '<url><loc>' . route('anime.index') . '</loc><priority>0.9</priority><changefreq>daily</changefreq></url>';

    foreach ($animes as $anime) {
        $xml .= '<url><loc>' . route('anime.show', $anime->route_id) . '</loc><lastmod>' . $anime->updated_at->toAtomString() . '</lastmod><priority>0.8</priority></url>';
    }

    foreach ($pages as $p) {
        $xml .= '<url><loc>' . route('pages.show', $p->slug) . '</loc><lastmod>' . $p->updated_at->toAtomString() . '</lastmod><priority>0.5</priority></url>';
    }

    foreach ($announcements as $a) {
        $xml .= '<url><loc>' . route('announcements.show', $a->slug) . '</loc><lastmod>' . $a->updated_at->toAtomString() . '</lastmod><priority>0.6</priority></url>';
    }

    $xml .= '</urlset>';

    return response($xml, 200, ['Content-Type' => 'application/xml']);
});

Route::get('/membership', [MembershipController::class, 'pricing'])->name('membership.pricing');
Route::post('/payments/midtrans/webhook', [PaymentController::class, 'webhook'])
    ->middleware('throttle:webhook')
    ->name('payments.webhook');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // USER PROFILE & LEVEL
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/profile/weekly-quest/claim', [ProfileController::class, 'claimWeeklyQuest'])->name('profile.weekly-quest.claim');

    // MEMBERSHIP & PEMBAYARAN
    Route::get('/membership/account', [MembershipController::class, 'show'])->name('membership.show');
    Route::get('/membership/history', [MembershipController::class, 'history'])->name('membership.history');
    Route::get('/membership/notifications', [MembershipController::class, 'notifications'])->name('membership.notifications');
    Route::patch('/membership/notifications/{notification}/read', [MembershipController::class, 'markNotificationRead'])->name('membership.notifications.read');
    Route::get('/membership/export', [MembershipController::class, 'exportHistory'])
        ->middleware('membership:premium')
        ->name('membership.export');
    Route::get('/membership/checkout/{membershipPrice}', [CheckoutController::class, 'create'])->name('membership.checkout');
    Route::post('/membership/checkout', [CheckoutController::class, 'store'])
        ->middleware('throttle:checkout')
        ->name('membership.checkout.store');
    Route::post('/membership/voucher/check', [CheckoutController::class, 'checkVoucher'])
        ->middleware('throttle:voucher')
        ->name('membership.voucher.check');

    Route::get('/payments/callback', [PaymentController::class, 'callback'])->name('payments.callback');
    Route::get('/payments/{invoice}', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('/payments/{invoice}/proof', [PaymentController::class, 'proof'])->name('payments.proof');

    Route::get('/watchlist', [WatchlistController::class, 'index'])->name('watchlist.index');
    Route::post('/watchlist', [WatchlistController::class, 'store'])->name('watchlist.store');
    Route::delete('/watchlist/{watchlist}', [WatchlistController::class, 'destroy'])->name('watchlist.destroy');

    Route::post('/comments', [UserInteractionController::class, 'storeComment'])->name('comments.store');
    Route::post('/reviews', [UserInteractionController::class, 'storeReview'])->name('reviews.store');
    Route::post('/notifications/read-all', [UserInteractionController::class, 'markNotificationsRead'])->name('notifications.read-all');
});

Route::get('/profile/{user}', [ProfileController::class, 'publicProfile'])->name('profile.public');

Route::post('/reports', [UserInteractionController::class, 'storeReport'])->name('reports.store');
Route::get('/announcements/{slug}', [AdminAnnouncementController::class, 'showPublic'])->name('announcements.show');
Route::get('/pages/{slug}', [AdminCustomPageController::class, 'showPublic'])->name('pages.show');

// Admin Panel Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.dashboard'));

    // OVERVIEW
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/export', [AdminAnalyticsController::class, 'export'])->name('analytics.export');
    Route::get('/visitor-logs', [AdminVisitorLogController::class, 'index'])->name('visitor-logs.index');
    Route::delete('/visitor-logs/{visitorLog}', [AdminVisitorLogController::class, 'destroy'])->name('visitor-logs.destroy');
    Route::post('/visitor-logs/clear-old', [AdminVisitorLogController::class, 'clearOld'])->name('visitor-logs.clear-old');

    // CONTENT MANAGEMENT
    Route::post('/animes/bulk', [AdminAnimeController::class, 'bulkAction'])->name('animes.bulk');
    Route::resource('animes', AdminAnimeController::class);
    Route::resource('episodes', AdminEpisodeController::class);
    Route::resource('genres', AdminGenreController::class);
    Route::resource('studios', AdminStudioController::class);
    Route::get('/schedules', [AdminScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules/update', [AdminScheduleController::class, 'update'])->name('schedules.update');
    Route::post('/blacklist/{blacklist}/restore', [AdminBlacklistController::class, 'restore'])->name('blacklist.restore');
    Route::resource('blacklist', AdminBlacklistController::class)->only(['index', 'create', 'store', 'destroy']);

    // USER MANAGEMENT
    Route::resource('users', AdminUserController::class);
    Route::patch('/users/{user}/level', [AdminUserController::class, 'updateLevel'])->name('users.level.update');
    Route::post('/users/{user}/ban', [AdminUserController::class, 'ban'])->name('users.ban');
    Route::post('/users/{user}/unban', [AdminUserController::class, 'unban'])->name('users.unban');
    Route::post('/users/{user}/toggle-verify', [AdminUserController::class, 'toggleVerify'])->name('users.toggle-verify');
    Route::get('/engagement', [AdminEngagementController::class, 'index'])->name('engagement.index');
    Route::get('/watch-activity', [AdminWatchActivityController::class, 'index'])->name('watch-activity.index');
    Route::delete('/watch-histories/{watchHistory}', [AdminWatchActivityController::class, 'destroyHistory'])->name('watch-histories.destroy');
    Route::delete('/watchlists/{watchlist}', [AdminWatchActivityController::class, 'destroyWatchlist'])->name('watchlists.destroy');
    Route::get('/badges', [AdminBadgeController::class, 'index'])->name('badges.index');
    Route::get('/badges/{user}/edit', [AdminBadgeController::class, 'edit'])->name('badges.edit');
    Route::patch('/badges/{user}', [AdminBadgeController::class, 'update'])->name('badges.update');
    Route::resource('roles', AdminRolePermissionController::class);
    Route::resource('comments', AdminCommentController::class)->only(['index', 'edit', 'update', 'destroy']);
    Route::post('/comments/{comment}/status', [AdminCommentController::class, 'setStatus'])->name('comments.status');
    Route::resource('reviews', AdminReviewController::class)->only(['index', 'destroy']);
    Route::post('/reviews/{review}/status', [AdminReviewController::class, 'setStatus'])->name('reviews.status');
    Route::resource('reports', AdminReportController::class)->only(['index', 'show', 'update', 'destroy']);

    // COMMUNICATION
    Route::resource('announcements', AdminAnnouncementController::class);
    Route::resource('notifications', AdminNotificationController::class)->only(['index', 'create', 'store', 'destroy']);

    // WEBSITE MANAGEMENT
    Route::get('/appearance/homepage', [AdminHomepageController::class, 'index'])->name('appearance.homepage');
    Route::post('/appearance/homepage', [AdminHomepageController::class, 'update']);
    Route::get('/appearance/theme', [AdminAppearanceController::class, 'index'])->name('appearance.theme');
    Route::post('/appearance/theme', [AdminAppearanceController::class, 'update']);
    Route::resource('banners', AdminBannerController::class)->except(['show']);
    Route::resource('pages', AdminCustomPageController::class);
    Route::get('/settings/seo', [AdminSeoController::class, 'index'])->name('settings.seo');
    Route::post('/settings/seo', [AdminSeoController::class, 'update']);

    // SYSTEM MANAGEMENT
    Route::get('/activity-logs', [AdminActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/system/monitoring', [AdminSystemMonitoringController::class, 'index'])->name('system.monitoring');
    Route::post('/system/monitoring/clear-logs', [AdminSystemMonitoringController::class, 'clearLogs'])->name('system.monitoring.clear-logs');
    Route::get('/system/cache', [AdminCacheManagementController::class, 'index'])->name('system.cache');
    Route::post('/system/cache/clear', [AdminCacheManagementController::class, 'clear'])->name('system.cache.clear');
    Route::get('/system/backup', [AdminDatabaseBackupController::class, 'index'])->name('system.backup');
    Route::post('/system/backup', [AdminDatabaseBackupController::class, 'store'])->name('system.backup.store');
    Route::get('/system/backup/download/{filename}', [AdminDatabaseBackupController::class, 'download'])->name('system.backup.download');
    Route::post('/system/backup/restore', [AdminDatabaseBackupController::class, 'restore'])->name('system.backup.restore');
    Route::delete('/system/backup/{filename}', [AdminDatabaseBackupController::class, 'destroy'])->name('system.backup.destroy');
    Route::get('/system/delete-all-content', [AdminContentCleanupController::class, 'index'])->name('system.delete-all-content');
    Route::delete('/system/delete-all-content', [AdminContentCleanupController::class, 'destroy'])->name('system.delete-all-content.destroy');
    Route::delete('/delete-all/{resource}', [AdminContentCleanupController::class, 'destroyResource'])->name('delete-all');
    Route::get('/settings/security', [AdminSecuritySettingsController::class, 'index'])->name('settings.security');
    Route::post('/settings/security', [AdminSecuritySettingsController::class, 'update']);
    Route::get('/settings/general', [AdminGeneralSettingsController::class, 'index'])->name('settings.general');
    Route::post('/settings/general', [AdminGeneralSettingsController::class, 'update']);

    // MEMBERSHIP MANAGEMENT
    Route::middleware('permission:membership.view')->group(function () {
        Route::get('/membership', [AdminMembershipController::class, 'index'])->name('membership.index');
        Route::get('/membership/history', [AdminMembershipHistoryController::class, 'index'])->name('membership-history.index');
        Route::get('/membership/{user}', [AdminMembershipController::class, 'show'])->name('membership.show');
    });

    Route::middleware('permission:membership.manage')->group(function () {
        Route::post('/membership/{user}/grant', [AdminMembershipController::class, 'grant'])->name('membership.grant');
        Route::post('/membership/{user}/renew', [AdminMembershipController::class, 'renew'])->name('membership.renew');
        Route::post('/membership/{user}/change', [AdminMembershipController::class, 'changePlan'])->name('membership.change');
        Route::post('/membership/{user}/revoke', [AdminMembershipController::class, 'revoke'])->name('membership.revoke');
        Route::post('/membership/sync', [AdminMembershipController::class, 'runSync'])->name('membership.sync');
    });

    Route::middleware('permission:membership.plans')->group(function () {
        Route::resource('membership-plans', AdminMembershipPlanController::class)->except(['show']);
        Route::post('/membership-plans/{membershipPlan}/prices', [AdminMembershipPriceController::class, 'store'])->name('membership-prices.store');
        Route::patch('/membership-plans/{membershipPlan}/prices/{membershipPrice}', [AdminMembershipPriceController::class, 'update'])->name('membership-prices.update');
        Route::delete('/membership-plans/{membershipPlan}/prices/{membershipPrice}', [AdminMembershipPriceController::class, 'destroy'])->name('membership-prices.destroy');
        Route::resource('vouchers', AdminVoucherController::class);
    });

    Route::middleware('permission:membership.payments')->group(function () {
        Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/{payment}', [AdminPaymentController::class, 'show'])->name('payments.show');
        Route::post('/payments/{payment}/verify', [AdminPaymentController::class, 'verify'])->name('payments.verify');
        Route::post('/payments/{payment}/failed', [AdminPaymentController::class, 'markFailed'])->name('payments.failed');
        Route::post('/payments/{payment}/refunded', [AdminPaymentController::class, 'markRefunded'])->name('payments.refunded');
        Route::post('/payments/{payment}/expired', [AdminPaymentController::class, 'markExpired'])->name('payments.expired');
    });
});
