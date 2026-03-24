<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weight_loss_goals', function (Blueprint $table) {
            $table->id();
            $table->date('month')->unique();
            $table->decimal('starting_weight', 6, 2);
            $table->decimal('goal_weight', 6, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weight_loss_goals');
    }
};
