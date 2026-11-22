<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('episodes')) {
            return;
        }

        Schema::table('episodes', function (Blueprint $table) {
            if (! Schema::hasColumn('episodes', 'minimum_plan')) {
                $table->string('minimum_plan')->nullable()->default(null)->after('is_published');
            }

            if (! Schema::hasColumn('episodes', 'early_access_until')) {
                $table->timestamp('early_access_until')->nullable()->after('minimum_plan');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('episodes')) {
            return;
        }

        Schema::table('episodes', function (Blueprint $table) {
            if (Schema::hasColumn('episodes', 'early_access_until')) {
                $table->dropColumn('early_access_until');
            }

            if (Schema::hasColumn('episodes', 'minimum_plan')) {
                $table->dropColumn('minimum_plan');
            }
        });
    }
};
