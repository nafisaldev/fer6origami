<?php

use App\Http\Controllers\Api\AnimeDetailController as PublicAnimeDetailController;
use App\Http\Controllers\Api\AnimeListController;
use App\Http\Controllers\Api\CompleteAnimeController;
use App\Http\Controllers\Api\EpisodeStreamController;
use App\Http\Controllers\Api\GenreAnimeController;
use App\Http\Controllers\Api\GenreListController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\Mobile\AnimeDetailController;
use App\Http\Controllers\Api\Mobile\CatalogController;
use App\Http\Controllers\Api\Mobile\HomeFeedController;
use App\Http\Controllers\Api\OngoingAnimeController;
use App\Http\Controllers\Api\ScrapeRefreshController;
use App\Http\Controllers\Api\ScraperHealthController;
use App\Http\Controllers\Api\SearchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('cache.headers:public;max_age=300;etag')->group(function () {
    Route::get('/home', HomeController::class);
    Route::get('/ongoing-anime', OngoingAnimeController::class);
    Route::get('/complete-anime', CompleteAnimeController::class);
    Route::get('/anime-list', AnimeListController::class);
    Route::get('/genres', GenreListController::class);
    Route::get('/genres/{genre}', GenreAnimeController::class)->where('genre', '.*');
    Route::get('/search', SearchController::class);
    Route::get('/anime/{animeId}', PublicAnimeDetailController::class)->where('animeId', '.*');
    Route::get('/episode/{episodeId}', EpisodeStreamController::class)->where('episodeId', '.*');

    Route::prefix('mobile')->group(function () {
        Route::get('/home', HomeFeedController::class);
        Route::get('/catalog', CatalogController::class);
        Route::get('/anime/{animeId}', AnimeDetailController::class)->where('animeId', '.*');
    });

    // Compatibility aliases for clients that already use the /api/v1 prefix.
    Route::prefix('v1')->group(function () {
        Route::get('/home', HomeFeedController::class);
        Route::get('/catalog', CatalogController::class);
        Route::get('/anime-list', AnimeListController::class);
        Route::get('/ongoing-anime', OngoingAnimeController::class);
        Route::get('/complete-anime', CompleteAnimeController::class);
        Route::get('/genres', GenreListController::class);
        Route::get('/genres/{genre}', GenreAnimeController::class)->where('genre', '.*');
        Route::get('/search', SearchController::class);
        Route::get('/anime/{animeId}', AnimeDetailController::class)->where('animeId', '.*');
        Route::get('/episode/{episodeId}', EpisodeStreamController::class)->where('episodeId', '.*');
    });
});

Route::post('/scrape/refresh', ScrapeRefreshController::class);
Route::get('/scraper-health', ScraperHealthController::class);
