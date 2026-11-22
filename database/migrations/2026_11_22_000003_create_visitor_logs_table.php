<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('visitor_logs')) {
            Schema::create('visitor_logs', function (Blueprint $table) {
                $table->id();
                $table->string('ip_address', 45)->index();
                $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->string('url', 500)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->timestamps();
                $table->index(['created_at', 'ip_address']);
            });
        }

        // Add anime_slug & episode_title to watch_histories if not exists
        if (Schema::hasTable('watch_histories')) {
            Schema::table('watch_histories', function (Blueprint $table) {
                if (!Schema::hasColumn('watch_histories', 'anime_slug')) {
                    $table->string('anime_slug')->nullable()->after('anime_id');
                }
                if (!Schema::hasColumn('watch_histories', 'episode_title')) {
                    $table->string('episode_title')->nullable()->after('episode_id');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_logs');
    }
};
