<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('watch_histories')) {
            return;
        }

        Schema::table('watch_histories', function (Blueprint $table) {
            $table->dropForeign(['anime_id']);
        });

        DB::statement('ALTER TABLE `watch_histories` MODIFY `anime_id` BIGINT UNSIGNED NULL');

        Schema::table('watch_histories', function (Blueprint $table) {
            $table->foreign('anime_id')->references('id')->on('animes')->cascadeOnDelete();

            if (! Schema::hasColumn('watch_histories', 'anime_title')) {
                $table->string('anime_title')->nullable()->after('anime_slug');
            }
            if (! Schema::hasColumn('watch_histories', 'anime_poster')) {
                $table->string('anime_poster')->nullable()->after('anime_title');
            }
            if (! Schema::hasColumn('watch_histories', 'episode_route_id')) {
                $table->string('episode_route_id')->nullable()->after('episode_title');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('watch_histories')) {
            return;
        }

        DB::table('watch_histories')->whereNull('anime_id')->delete();

        Schema::table('watch_histories', function (Blueprint $table) {
            $table->dropForeign(['anime_id']);
            $table->dropColumn(['anime_title', 'anime_poster', 'episode_route_id']);
        });

        DB::statement('ALTER TABLE `watch_histories` MODIFY `anime_id` BIGINT UNSIGNED NOT NULL');

        Schema::table('watch_histories', function (Blueprint $table) {
            $table->foreign('anime_id')->references('id')->on('animes')->cascadeOnDelete();
        });
    }
};
