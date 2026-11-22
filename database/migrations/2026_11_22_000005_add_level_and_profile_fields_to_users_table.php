<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'xp')) {
                $table->unsignedInteger('xp')->default(0)->after('status')->index();
            }
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable()->after('xp');
            }
            if (!Schema::hasColumn('users', 'favorite_anime')) {
                $table->string('favorite_anime')->nullable()->after('bio');
            }
            if (!Schema::hasColumn('users', 'badges')) {
                $table->json('badges')->nullable()->after('favorite_anime');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['xp', 'bio', 'favorite_anime', 'badges']);
        });
    }
};
