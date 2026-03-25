<?php

namespace App\Services;

use App\Models\DailyCompletionStatus;
use App\Models\Todo;
use App\Models\TodoLog;
use App\Models\WeightLog;
use App\Models\WeightLossGoal;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class DailyCompletionService
{
    public function syncForDate(CarbonInterface|string $trackedFor): DailyCompletionStatus
    {
        $date = $trackedFor instanceof CarbonInterface
            ? $trackedFor->copy()->startOfDay()
            : Carbon::parse($trackedFor, config('app.timezone'))->startOfDay();

        $status = DailyCompletionStatus::query()->firstOrNew([
            'tracked_for' => $date->toDateString(),
        ]);

        $habitBase = $this->habitSnapshotBase($status, $date);
        $habitLogs = TodoLog::query()
            ->whereDate('logged_for', $date->toDateString())
            ->get()
            ->keyBy('todo_id');

        $habitSnapshot = $habitBase->map(function (array $habit) use ($habitLogs) {
            $log = $habit['todo_id'] ? $habitLogs->get($habit['todo_id']) : null;
            $goalLabel = Todo::formatAmount($habit['goal'] ?? 0);
            $valueLabel = $log
                ? Todo::formatAmount($log->value).' / '.$goalLabel.' '.($habit['unit'] ?? '')
                : 'No log';

            return [
                'todo_id' => $habit['todo_id'] ?? null,
                'name' => $habit['name'] ?? 'Habit',
                'goal' => (float) ($habit['goal'] ?? 0),
                'unit' => $habit['unit'] ?? '',
                'completed' => (bool) ($log?->completed),
                'status' => $log?->completed ? 'Completed' : 'Incomplete',
                'value' => trim($valueLabel),
            ];
        })->values();

        $targetWeight = $status->exists && !is_null($status->target_weight)
            ? (float) $status->target_weight
            : $this->projectedTargetForDate($date);
        $weightLog = WeightLog::query()
            ->whereDate('logged_for', $date->toDateString())
            ->first();
        $rollingAverageWeight = $weightLog ? round((float) $weightLog->rolling_average_weight, 2) : null;
        $scheduledHabitCount = $habitSnapshot->count();
        $completedHabitCount = $habitSnapshot->where('completed', true)->count();
        $allHabitsCompleted = $scheduledHabitCount === 0 || $completedHabitCount === $scheduledHabitCount;
        $weightOnTarget = !is_null($targetWeight)
            && !is_null($rollingAverageWeight)
            && $rollingAverageWeight <= $targetWeight;

        $status->fill([
            'state' => $weightOnTarget && $allHabitsCompleted ? 'complete' : 'missed',
            'target_weight' => $targetWeight,
            'rolling_average_weight' => $rollingAverageWeight,
            'scheduled_habit_count' => $scheduledHabitCount,
            'completed_habit_count' => $completedHabitCount,
            'habit_snapshot' => $habitSnapshot->all(),
        ]);

        $status->save();

        return $status->fresh();
    }

    public function projectedTargetForDate(CarbonInterface|string $trackedFor): ?float
    {
        $date = $trackedFor instanceof CarbonInterface
            ? $trackedFor->copy()->startOfDay()
            : Carbon::parse($trackedFor, config('app.timezone'))->startOfDay();
        $goal = WeightLossGoal::query()
            ->whereDate('month', $date->copy()->startOfMonth()->toDateString())
            ->first();

        if (!$goal) {
            return null;
        }

        $daysInMonth = $goal->month->daysInMonth;

        if ($daysInMonth <= 1) {
            return round((float) $goal->goal_weight, 2);
        }

        $progress = ($date->day - 1) / ($daysInMonth - 1);
        $value = (float) $goal->starting_weight
            + (((float) $goal->goal_weight - (float) $goal->starting_weight) * $progress);

        return round($value, 2);
    }

    private function habitSnapshotBase(DailyCompletionStatus $status, CarbonInterface $date): Collection
    {
        if ($status->exists) {
            return collect($status->habit_snapshot ?? [])->map(function (array $habit) {
                return [
                    'todo_id' => $habit['todo_id'] ?? null,
                    'name' => $habit['name'] ?? 'Habit',
                    'goal' => (float) ($habit['goal'] ?? 0),
                    'unit' => $habit['unit'] ?? '',
                ];
            });
        }

        $weekdayKey = strtolower($date->englishDayOfWeek);

        return Todo::query()
            ->active()
            ->scheduledFor($weekdayKey)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (Todo $habit) {
                return [
                    'todo_id' => $habit->id,
                    'name' => $habit->name,
                    'goal' => (float) $habit->daily_goal,
                    'unit' => $habit->unit,
                ];
            });
    }
}
