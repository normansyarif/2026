<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goal_milestones', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('estimated_completion_month');
        });

        $milestones = DB::table('goal_milestones')
            ->orderBy('goal_id')
            ->orderBy('estimated_completion_month')
            ->orderBy('id')
            ->get(['id', 'goal_id']);

        $positionByGoal = [];

        foreach ($milestones as $milestone) {
            $positionByGoal[$milestone->goal_id] = ($positionByGoal[$milestone->goal_id] ?? 0) + 1;

            DB::table('goal_milestones')
                ->where('id', $milestone->id)
                ->update([
                    'sort_order' => $positionByGoal[$milestone->goal_id],
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('goal_milestones', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
