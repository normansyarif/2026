<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoalMilestone extends Model
{
    protected $fillable = [
        'goal_id',
        'name',
        'estimated_completion_month',
        'sort_order',
        'completed',
    ];

    protected function casts(): array
    {
        return [
            'estimated_completion_month' => 'date',
            'sort_order' => 'integer',
            'completed' => 'boolean',
        ];
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function monthLabel(): string
    {
        return $this->estimated_completion_month->format('F Y');
    }
}
