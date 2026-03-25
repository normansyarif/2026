<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_completion_statuses', function (Blueprint $table) {
            $table->id();
            $table->date('tracked_for')->unique();
            $table->string('state', 20);
            $table->decimal('target_weight', 8, 2)->nullable();
            $table->decimal('rolling_average_weight', 8, 2)->nullable();
            $table->unsignedInteger('scheduled_habit_count')->default(0);
            $table->unsignedInteger('completed_habit_count')->default(0);
            $table->json('habit_snapshot')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_completion_statuses');
    }
};
