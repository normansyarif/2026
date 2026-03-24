<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('todo_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('todo_id')->constrained()->cascadeOnDelete();
            $table->date('logged_for');
            $table->decimal('value', 10, 2);
            $table->boolean('completed')->default(false);
            $table->timestamps();

            $table->unique(['todo_id', 'logged_for']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('todo_logs');
    }
};
