<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyCompletionStatus extends Model
{
    protected $fillable = [
        'tracked_for',
        'state',
        'target_weight',
        'rolling_average_weight',
        'scheduled_habit_count',
        'completed_habit_count',
        'habit_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'tracked_for' => 'date',
            'target_weight' => 'decimal:2',
            'rolling_average_weight' => 'decimal:2',
            'scheduled_habit_count' => 'integer',
            'completed_habit_count' => 'integer',
            'habit_snapshot' => 'array',
        ];
    }
}
