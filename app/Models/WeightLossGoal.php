<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class WeightLossGoal extends Model
{
    protected $fillable = [
        'month',
        'starting_weight',
        'goal_weight',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'date',
            'starting_weight' => 'decimal:2',
            'goal_weight' => 'decimal:2',
        ];
    }

    public function monthLabel(): string
    {
        return $this->month->format('F Y');
    }

    public function startWeightLabel(): string
    {
        return self::formatWeight($this->starting_weight);
    }

    public function goalWeightLabel(): string
    {
        return self::formatWeight($this->goal_weight);
    }

    public function lossAmountLabel(): string
    {
        return self::formatWeight((float) $this->starting_weight - (float) $this->goal_weight);
    }

    public static function overallSummary(Collection $monthlyGoals): ?array
    {
        $firstGoal = $monthlyGoals->first();
        $lastGoal = $monthlyGoals->last();

        if (!$firstGoal || !$lastGoal) {
            return null;
        }

        return [
            'starting_weight' => $firstGoal->starting_weight,
            'goal_weight' => $lastGoal->starting_weight,
            'final_goal_weight' => $lastGoal->goal_weight,
            'start_month_label' => $firstGoal->monthLabel(),
            'goal_month_label' => $lastGoal->monthLabel(),
        ];
    }

    public static function formatWeight(float|int|string $value): string
    {
        $formatted = number_format((float) $value, 2, '.', '');

        return rtrim(rtrim($formatted, '0'), '.');
    }
}
