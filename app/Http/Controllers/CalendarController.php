<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\TodoLog;
use App\Models\WeightLog;
use App\Models\WeightLossGoal;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $month = $this->resolveMonth($request->query('month'));

        return view('pages.calendar', [
            'monthView' => $this->monthViewData($month),
        ]);
    }

    public function showMonth(Request $request): JsonResponse
    {
        $data = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $monthView = $this->monthViewData($this->resolveMonth($data['month']));

        return response()->json([
            'month' => $monthView['monthKey'],
            'month_label' => $monthView['monthLabel'],
            'html' => view('pages.partials.month-completion-calendar', $monthView)->render(),
        ]);
    }

    private function monthViewData(CarbonInterface $month): array
    {
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();
        $today = now()->startOfDay();
        $activeHabits = Todo::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $habitLogsByDate = TodoLog::query()
            ->whereBetween('logged_for', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get()
            ->groupBy(fn (TodoLog $log) => $log->logged_for->toDateString())
            ->map(fn (Collection $logs) => $logs->keyBy('todo_id'));
        $weightLogsByDate = WeightLog::query()
            ->whereBetween('logged_for', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->orderBy('logged_for')
            ->get()
            ->keyBy(fn (WeightLog $log) => $log->logged_for->toDateString());
        $weightGoal = WeightLossGoal::query()
            ->whereDate('month', $monthStart->toDateString())
            ->first();

        $days = collect(range(1, $monthStart->daysInMonth))
            ->map(function (int $day) use ($monthStart, $today, $activeHabits, $habitLogsByDate, $weightLogsByDate, $weightGoal) {
                $date = $monthStart->copy()->day($day);
                $dateKey = $date->toDateString();
                $targetWeight = $weightGoal ? $this->projectedWeightForDate($weightGoal, $date) : null;

                if ($date->greaterThan($today)) {
                    return [
                        'date' => $dateKey,
                        'date_label' => $date->format('D, M j, Y'),
                        'day_number' => $date->day,
                        'state' => 'future',
                        'state_class' => 'is-future',
                        'status_label' => 'Upcoming',
                        'weight_label' => $targetWeight !== null
                            ? 'Target '.WeightLossGoal::formatWeight($targetWeight).' kg'
                            : 'No target',
                        'habit_label' => 'Not graded yet',
                        'tooltip' => $targetWeight !== null
                            ? 'Future date. Daily target: '.WeightLossGoal::formatWeight($targetWeight).' kg.'
                            : 'Future date. Add a monthly weight goal to score this day later.',
                        'is_today' => false,
                        'details' => [
                            'date_label' => $date->format('D, M j, Y'),
                            'status_label' => 'Upcoming',
                            'weight_summary' => $targetWeight !== null
                                ? 'Projected target: '.WeightLossGoal::formatWeight($targetWeight).' kg'
                                : 'No monthly weight target set.',
                            'habit_summary' => 'This day has not been graded yet.',
                            'habits' => [],
                        ],
                    ];
                }

                $weekdayKey = strtolower($date->englishDayOfWeek);
                $scheduledHabits = $activeHabits->filter(
                    fn (Todo $habit) => $habit->isScheduledFor($weekdayKey)
                );
                $habitLogs = $habitLogsByDate->get($dateKey, collect());
                $completedHabitCount = $scheduledHabits->filter(
                    fn (Todo $habit) => (bool) optional($habitLogs->get($habit->id))->completed
                )->count();
                $allHabitsCompleted = $scheduledHabits->every(
                    fn (Todo $habit) => (bool) optional($habitLogs->get($habit->id))->completed
                );

                $weightLog = $weightLogsByDate->get($dateKey);
                $rollingAverageWeight = $weightLog ? (float) $weightLog->rolling_average_weight : null;
                $weightOnTarget = $targetWeight !== null
                    && $rollingAverageWeight !== null
                    && $rollingAverageWeight <= $targetWeight;
                $isComplete = $weightOnTarget && $allHabitsCompleted;
                $habitDetails = $scheduledHabits->map(function (Todo $habit) use ($habitLogs) {
                    $log = $habitLogs->get($habit->id);

                    return [
                        'name' => $habit->name,
                        'status' => $log?->completed ? 'Completed' : 'Incomplete',
                        'value' => $log
                            ? Todo::formatAmount($log->value).' / '.Todo::formatAmount($habit->daily_goal).' '.$habit->unit
                            : 'No log',
                    ];
                })->values()->all();
                $weightSummary = $targetWeight === null
                    ? 'No monthly weight target set.'
                    : ($rollingAverageWeight === null
                        ? 'No rolling-average weight logged. Target: '.WeightLossGoal::formatWeight($targetWeight).' kg'
                        : 'Avg '.WeightLossGoal::formatWeight($rollingAverageWeight).' kg vs target '.WeightLossGoal::formatWeight($targetWeight).' kg');

                return [
                    'date' => $dateKey,
                    'date_label' => $date->format('D, M j, Y'),
                    'day_number' => $date->day,
                    'state' => $isComplete ? 'complete' : 'missed',
                    'state_class' => $isComplete ? 'is-complete' : 'is-missed',
                    'status_label' => $isComplete ? 'Finished' : 'Incomplete',
                    'weight_label' => $rollingAverageWeight !== null
                        ? 'Avg '.WeightLossGoal::formatWeight($rollingAverageWeight).' kg'
                        : 'No weight log',
                    'habit_label' => 'Habits '.$completedHabitCount.'/'.$scheduledHabits->count(),
                    'tooltip' => $this->dayTooltip(
                        $isComplete,
                        $targetWeight,
                        $rollingAverageWeight,
                        $completedHabitCount,
                        $scheduledHabits->count()
                    ),
                    'is_today' => $date->isSameDay($today),
                    'details' => [
                        'date_label' => $date->format('D, M j, Y'),
                        'status_label' => $isComplete ? 'Finished' : 'Incomplete',
                        'weight_summary' => $weightSummary,
                        'habit_summary' => $scheduledHabits->isEmpty()
                            ? 'No habits were scheduled for this day.'
                            : 'Completed '.$completedHabitCount.' of '.$scheduledHabits->count().' scheduled habits.',
                        'habits' => $habitDetails,
                    ],
                ];
            });

        return [
            'monthKey' => $monthStart->format('Y-m'),
            'monthLabel' => $monthStart->format('F Y'),
            'monthSummary' => 'Green means the day finished, red means it missed the rule, and yellow marks future dates.',
            'previousMonthKey' => $monthStart->copy()->subMonth()->format('Y-m'),
            'nextMonthKey' => $monthStart->copy()->addMonth()->format('Y-m'),
            'leadingBlankDays' => $monthStart->dayOfWeekIso - 1,
            'days' => $days,
            'weekdays' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        ];
    }

    private function resolveMonth(?string $monthKey): CarbonInterface
    {
        if (!$monthKey || !preg_match('/^\d{4}-\d{2}$/', $monthKey)) {
            return now()->startOfMonth();
        }

        try {
            return Carbon::createFromFormat('Y-m', $monthKey, config('app.timezone'))->startOfMonth();
        } catch (\Throwable) {
            return now()->startOfMonth();
        }
    }

    private function projectedWeightForDate(WeightLossGoal $goal, CarbonInterface $date): float
    {
        $daysInMonth = $goal->month->daysInMonth;

        if ($daysInMonth <= 1) {
            return round((float) $goal->goal_weight, 2);
        }

        $progress = ($date->day - 1) / ($daysInMonth - 1);
        $value = (float) $goal->starting_weight
            + (((float) $goal->goal_weight - (float) $goal->starting_weight) * $progress);

        return round($value, 2);
    }

    private function dayTooltip(
        bool $isComplete,
        ?float $targetWeight,
        ?float $rollingAverageWeight,
        int $completedHabitCount,
        int $scheduledHabitCount
    ): string {
        $parts = [
            $isComplete ? 'Finished day.' : 'Incomplete day.',
            'Habits '.$completedHabitCount.'/'.$scheduledHabitCount.'.',
        ];

        if ($targetWeight === null) {
            $parts[] = 'No monthly weight target.';
        } elseif ($rollingAverageWeight === null) {
            $parts[] = 'No weight log.';
        } else {
            $parts[] = 'Avg '.WeightLossGoal::formatWeight($rollingAverageWeight).' kg vs target '.WeightLossGoal::formatWeight($targetWeight).' kg.';
        }

        return implode(' ', $parts);
    }
}
