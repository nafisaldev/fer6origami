<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('membership_plan_prices')) {
            return;
        }

        Schema::create('membership_plan_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('membership_plans')->cascadeOnDelete();
            $table->unsignedInteger('duration_days');
            $table->string('label');
            $table->decimal('price', 12, 2)->default(0);
            $table->char('currency', 3)->default('IDR');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['plan_id', 'duration_days']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_plan_prices');
    }
};
