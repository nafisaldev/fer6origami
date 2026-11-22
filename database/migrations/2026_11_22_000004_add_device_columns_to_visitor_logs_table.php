<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('visitor_logs')) {
            Schema::table('visitor_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('visitor_logs', 'device_name')) {
                    $table->string('device_name', 100)->nullable()->after('user_agent');
                }
                if (!Schema::hasColumn('visitor_logs', 'device_type')) {
                    $table->string('device_type', 30)->default('Desktop')->after('device_name'); // Desktop, Mobile, Tablet, Bot
                }
                if (!Schema::hasColumn('visitor_logs', 'browser')) {
                    $table->string('browser', 50)->nullable()->after('device_type');
                }
                if (!Schema::hasColumn('visitor_logs', 'platform')) {
                    $table->string('platform', 50)->nullable()->after('browser');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('visitor_logs')) {
            Schema::table('visitor_logs', function (Blueprint $table) {
                $columns = ['device_name', 'device_type', 'browser', 'platform'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('visitor_logs', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
