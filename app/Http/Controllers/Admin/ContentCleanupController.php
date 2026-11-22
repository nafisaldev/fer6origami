<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Announcement;
use App\Models\Banner;
use App\Models\BroadcastNotification;
use App\Models\Comment;
use App\Models\CustomPage;
use App\Models\DeletedAnime;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Report;
use App\Models\Review;
use App\Models\ScraperSnapshot;
use App\Models\Studio;
use App\Models\User;
use App\Models\UserNotificationRead;
use App\Models\UserWeeklyQuest;
use App\Models\VisitorLog;
use App\Models\WatchHistory;
use App\Models\Watchlist;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ContentCleanupController extends Controller
{
    private const CONFIRMATION_PHRASE = 'HAPUS SEMUA KONTEN';

    public function index(): View
    {
        $this->ensureSuperAdmin();

        return view('admin.system.delete-all-content', [
            'counts' => $this->contentCounts(),
            'confirmationPhrase' => self::CONFIRMATION_PHRASE,
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->ensureSuperAdmin();

        $request->validate([
            'confirmation' => ['required', 'string', 'in:' . self::CONFIRMATION_PHRASE],
        ]);

        $counts = $this->contentCounts();
        $animes = Anime::all(['id', 'title', 'slug', 'poster_path', 'banner_path']);
        $filePaths = [];

        foreach ($animes as $anime) {
            $filePaths[] = $anime->poster_path;
            $filePaths[] = $anime->banner_path;
        }

        foreach (Studio::query()->select('logo_path')->get() as $studio) {
            $filePaths[] = $studio->logo_path;
        }

        foreach (Banner::query()->select('image_path')->get() as $banner) {
            $filePaths[] = $banner->image_path;
        }

        foreach (Announcement::query()->select('banner_path')->get() as $announcement) {
            $filePaths[] = $announcement->banner_path;
        }

        DB::transaction(function () {
            // Local rows are removed only. API-sourced catalog entries stay visible;
            // use the blacklist panel when an anime must be hidden deliberately.
            UserNotificationRead::query()->delete();
            BroadcastNotification::query()->delete();
            Watchlist::query()->delete();
            WatchHistory::query()->delete();
            Comment::query()->delete();
            Review::query()->delete();
            Report::query()->delete();
            Episode::query()->delete();
            Anime::query()->delete();

            if (Schema::hasTable('anime_genre')) {
                DB::table('anime_genre')->delete();
            }

            Genre::query()->delete();
            Studio::query()->delete();
            Banner::query()->delete();
            Announcement::query()->delete();
            CustomPage::query()->delete();
            VisitorLog::query()->delete();

            if (Schema::hasTable('scraper_snapshots')) {
                ScraperSnapshot::query()->delete();
            }
        });

        foreach (array_unique(array_filter($filePaths)) as $path) {
            if (! str_starts_with($path, 'http') && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        cache()->flush();

        ActivityLogger::log('System', 'content_purged', 'Menghapus seluruh konten admin sekaligus.', $counts);

        return redirect()->route('admin.system.delete-all-content')
            ->with('success', 'Semua konten berhasil dihapus. Akun, role, pengaturan, dan backup tidak ikut terhapus.');
    }

    public function destroyResource(string $resource): RedirectResponse
    {
        $this->ensureSuperAdmin();

        $labels = [
            'animes' => 'Anime',
            'episodes' => 'Episode',
            'genres' => 'Genre',
            'studios' => 'Studio',
            'banners' => 'Banner',
            'announcements' => 'Pengumuman',
            'pages' => 'Halaman',
            'comments' => 'Komentar',
            'reviews' => 'Review',
            'reports' => 'Laporan',
            'notifications' => 'Notifikasi',
            'visitor-logs' => 'Log Pengunjung',
            'watch-histories' => 'Riwayat Tonton',
            'watchlists' => 'Watchlist',
            'weekly-quests' => 'Misi Mingguan',
            'blacklist' => 'Blacklist Anime',
            'badges' => 'Lencana Manual',
        ];

        $modelMap = [
            'animes' => Anime::class,
            'episodes' => Episode::class,
            'genres' => Genre::class,
            'studios' => Studio::class,
            'banners' => Banner::class,
            'announcements' => Announcement::class,
            'pages' => CustomPage::class,
            'comments' => Comment::class,
            'reviews' => Review::class,
            'reports' => Report::class,
            'notifications' => BroadcastNotification::class,
            'visitor-logs' => VisitorLog::class,
            'watch-histories' => WatchHistory::class,
            'watchlists' => Watchlist::class,
            'weekly-quests' => UserWeeklyQuest::class,
            'blacklist' => DeletedAnime::class,
        ];

        abort_unless(array_key_exists($resource, $labels), 404);

        $modelClass = $modelMap[$resource] ?? null;
        $count = $resource === 'badges'
            ? User::whereNotNull('badges')->count()
            : $modelClass::count();
        $filePaths = [];
        $animes = [];

        if ($resource === 'animes') {
            $animes = Anime::all(['id', 'title', 'slug', 'poster_path', 'banner_path']);

            foreach ($animes as $anime) {
                $filePaths[] = $anime->poster_path;
                $filePaths[] = $anime->banner_path;
            }
        } elseif ($resource === 'studios') {
            foreach (Studio::query()->select('logo_path')->get() as $studio) {
                $filePaths[] = $studio->logo_path;
            }
        } elseif ($resource === 'banners') {
            foreach (Banner::query()->select('image_path')->get() as $banner) {
                $filePaths[] = $banner->image_path;
            }
        } elseif ($resource === 'announcements') {
            foreach (Announcement::query()->select('banner_path')->get() as $announcement) {
                $filePaths[] = $announcement->banner_path;
            }
        }

        DB::transaction(function () use ($resource, $modelClass) {
            if ($resource === 'animes') {
                Watchlist::query()->delete();
                WatchHistory::query()->delete();

                if (Schema::hasTable('scraper_snapshots')) {
                    ScraperSnapshot::query()->delete();
                }
            }

            if ($resource === 'notifications') {
                UserNotificationRead::query()->delete();
            }

            if ($resource === 'badges') {
                User::query()->update(['badges' => null]);
            } else {
                $modelClass::query()->delete();
            }

            if ($resource === 'animes' && Schema::hasTable('anime_genre')) {
                DB::table('anime_genre')->delete();
            }
        });

        foreach (array_unique(array_filter($filePaths)) as $path) {
            if (! str_starts_with($path, 'http') && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        if ($resource === 'blacklist') {
            cache()->forget('deleted_anime_identifiers');
        }

        cache()->flush();

        ActivityLogger::log('System', 'bulk_deleted', "Menghapus semua {$labels[$resource]} ({$count} data).", [
            'resource' => $resource,
            'count' => $count,
        ]);

        return back()->with('success', "Semua {$labels[$resource]} ({$count} data) berhasil dihapus.");
    }

    private function ensureSuperAdmin(): void
    {
        abort_unless(Auth::user()?->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat menghapus semua konten.');
    }

    private function contentCounts(): array
    {
        $counts = [
            ['label' => 'Anime', 'count' => Anime::count()],
            ['label' => 'Episode', 'count' => Episode::count()],
            ['label' => 'Genre', 'count' => Genre::count()],
            ['label' => 'Studio', 'count' => Studio::count()],
            ['label' => 'Banner', 'count' => Banner::count()],
            ['label' => 'Pengumuman', 'count' => Announcement::count()],
            ['label' => 'Notifikasi broadcast', 'count' => BroadcastNotification::count()],
            ['label' => 'Halaman kustom', 'count' => CustomPage::count()],
            ['label' => 'Komentar', 'count' => Comment::count()],
            ['label' => 'Review', 'count' => Review::count()],
            ['label' => 'Laporan', 'count' => Report::count()],
            ['label' => 'Watchlist', 'count' => Watchlist::count()],
            ['label' => 'Riwayat tonton', 'count' => WatchHistory::count()],
            ['label' => 'Log pengunjung', 'count' => VisitorLog::count()],
        ];

        if (Schema::hasTable('scraper_snapshots')) {
            $counts[] = ['label' => 'Snapshot scraper', 'count' => ScraperSnapshot::count()];
        }

        return $counts;
    }
}
