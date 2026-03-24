<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('todos', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('unit');
        });

        $todos = DB::table('todos')
            ->orderBy('id')
            ->get(['id']);

        foreach ($todos as $index => $todo) {
            DB::table('todos')
                ->where('id', $todo->id)
                ->update(['sort_order' => $index + 1]);
        }
    }

    public function down(): void
    {
        Schema::table('todos', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
