<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Goal;
use App\Models\GoalMilestone;
use App\Models\Todo;
use App\Models\TodoLog;
use App\Models\WeightLog;
use App\Models\WeightLossGoal;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TodayController extends Controller
{
    public function index(): View
    {
        $today = now()->startOfDay();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        $weekdayKey = strtolower($today->englishDayOfWeek);
        $lastSevenDays = collect(range(6, 0))
            ->map(fn (int $daysAgo) => $today->copy()->subDays($daysAgo));
        $monthDays = collect(range(1, $today->daysInMonth))
            ->map(fn (int $day) => $monthStart->copy()->day($day));

        $habits = Todo::query()
            ->active()
            ->scheduledFor($weekdayKey)
            ->with([
                'logs' => fn ($query) => $query
                    ->whereBetween('logged_for', [$monthStart->toDateString(), $monthEnd->toDateString()])
                    ->latest('logged_for'),
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $monthlyWeightGoals = WeightLossGoal::query()
            ->orderBy('month')
            ->get();
        $latestWeightLog = WeightLog::query()
            ->whereDate('logged_for', '<=', $today->toDateString())
            ->latest('logged_for')
            ->first();
        $todayWeightLog = WeightLog::query()
            ->whereDate('logged_for', $today->toDateString())
            ->first();
        $currentMonthMilestones = GoalMilestone::query()
            ->with('goal')
            ->whereDate('estimated_completion_month', $monthStart->toDateString())
            ->orderByDesc(
                Goal::query()
                    ->select('created_at')
                    ->whereColumn('goals.id', 'goal_milestones.goal_id')
                    ->limit(1)
            )
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $timelineCounters = $this->timelineCounters($today);

        return view('pages.today', [
            'today' => $today,
            'weekdayKey' => $weekdayKey,
            'lastSevenDays' => $lastSevenDays,
            'monthDays' => $monthDays,
            'monthLabel' => $today->format('F Y'),
            'monthLeadingBlanks' => $monthStart->dayOfWeekIso - 1,
            'habits' => $habits,
            'weightSection' => $this->weightSectionData($monthlyWeightGoals, $latestWeightLog, $todayWeightLog, $today),
            'goalMilestones' => $currentMonthMilestones,
            'timelineCounters' => $timelineCounters,
        ]);
    }

    public function store(Request $request, Todo $habit): RedirectResponse|JsonResponse
    {
        $today = now()->startOfDay();

        $data = $request->validate([
            'habit_id' => ['required', 'integer'],
            'logged_for' => ['nullable', 'date', 'before_or_equal:'.$today->toDateString()],
            'value' => ['required', 'numeric', 'min:0'],
        ]);

        $loggedFor = filled($data['logged_for'] ?? null)
            ? now()->parse($data['logged_for'])->startOfDay()
            : $today;
        $weekdayKey = strtolower($loggedFor->englishDayOfWeek);

        abort_unless($habit->isScheduledFor($weekdayKey), 404);

        $value = (float) $data['value'];
        $completed = $habit->goalReached($value);

        $log = TodoLog::query()->updateOrCreate(
            [
                'todo_id' => $habit->id,
                'logged_for' => $loggedFor->toDateString(),
            ],
            [
                'value' => $value,
                'completed' => $completed,
            ]
        );

        $isToday = $loggedFor->isSameDay($today);
        $message = $completed
            ? $habit->name.' is marked complete for '.$this->entryLabel($loggedFor, $today).'.'
            : 'Saved '.$this->entryLabel($loggedFor, $today).'\'s '.$habit->unit.' entry for '.$habit->name.'.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'habit' => [
                    'id' => $habit->id,
                    'name' => $habit->name,
                    'unit' => $habit->unit,
                    'goal' => Todo::formatAmount($habit->daily_goal),
                    'logged_value' => Todo::formatAmount($log->value),
                    'logged_for' => $log->logged_for->toDateString(),
                    'is_today' => $isToday,
                    'completed' => $completed,
                    'day_state' => $completed ? 'complete' : 'partial',
                    'progress_percent' => $isToday ? $this->progressPercent($habit, $log) : null,
                    'remaining_label' => $isToday
                        ? ($completed
                            ? 'Goal reached'
                            : Todo::formatAmount($this->remainingValue($habit, $log)).' '.$habit->unit.' left')
                        : null,
                    'status_label' => $isToday ? ($completed ? 'Completed' : 'In progress') : null,
                    'summary' => $isToday
                        ? ($completed
                            ? 'You are done for today.'
                            : Todo::formatAmount($this->remainingValue($habit, $log)).' '.$habit->unit.' left to reach the daily goal.')
                        : null,
                ],
            ]);
        }

        return redirect()
            ->route('today.index')
            ->with('status', $message);
    }

    public function storeWeight(Request $request): RedirectResponse|JsonResponse
    {
        $today = now()->startOfDay();

        $data = $request->validate([
            'weight' => ['required', 'numeric', 'gt:0'],
        ]);

        $log = WeightLog::query()->updateOrCreate(
            [
                'logged_for' => $today->toDateString(),
            ],
            [
                'weight' => $data['weight'],
                'rolling_average_weight' => 0,
            ]
        );

        $rollingAverageWeight = round((float) WeightLog::query()
            ->whereDate('logged_for', '>=', $today->copy()->subDays(6)->toDateString())
            ->whereDate('logged_for', '<=', $today->toDateString())
            ->avg('weight'), 2);

        $log->update([
            'rolling_average_weight' => $rollingAverageWeight,
        ]);

        $monthlyWeightGoals = WeightLossGoal::query()
            ->orderBy('month')
            ->get();
        $latestWeightLog = WeightLog::query()
            ->whereDate('logged_for', '<=', $today->toDateString())
            ->latest('logged_for')
            ->first();
        $todayWeightLog = WeightLog::query()
            ->whereDate('logged_for', $today->toDateString())
            ->first();
        $weightSection = $this->weightSectionData($monthlyWeightGoals, $latestWeightLog, $todayWeightLog, $today);
        $message = 'Today\'s weight was saved.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'weight' => $weightSection,
            ]);
        }

        return redirect()
            ->route('today.index')
            ->with('status', $message);
    }

    public function toggleMilestone(GoalMilestone $milestone): RedirectResponse|JsonResponse
    {
        $today = now()->startOfDay();
        $currentMonth = $today->copy()->startOfMonth();

        abort_unless($milestone->estimated_completion_month->isSameDay($currentMonth), 404);

        $milestone->update([
            'completed' => ! $milestone->completed,
        ]);

        $message = $milestone->completed
            ? $milestone->name.' marked complete.'
            : $milestone->name.' marked incomplete.';

        if (request()->expectsJson()) {
            return response()->json([
                'message' => $message,
                'milestone' => [
                    'id' => $milestone->id,
                    'completed' => $milestone->completed,
                    'status_label' => $milestone->completed ? 'Completed' : 'In progress',
                ],
            ]);
        }

        return redirect()
            ->route('today.index')
            ->with('status', $message);
    }

    private function progressPercent(Todo $habit, TodoLog $log): float
    {
        $goal = (float) $habit->daily_goal;

        if ($goal <= 0) {
            return 0;
        }

        return min(100, round((((float) $log->value) / $goal) * 100, 2));
    }

    private function remainingValue(Todo $habit, TodoLog $log): float
    {
        return max(0, (float) $habit->daily_goal - (float) $log->value);
    }

    private function entryLabel($loggedFor, $today): string
    {
        return $loggedFor->isSameDay($today) ? 'today' : $loggedFor->format('M j');
    }

    private function weightSectionData(Collection $monthlyWeightGoals, ?WeightLog $latestWeightLog, ?WeightLog $todayWeightLog, CarbonInterface $today): array
    {
        $overallGoal = WeightLossGoal::overallSummary($monthlyWeightGoals);
        $currentMonthGoal = $monthlyWeightGoals->first(
            fn (WeightLossGoal $goal) => $goal->month->isSameDay($today->copy()->startOfMonth())
        );
        $rollingAverageWeight = $latestWeightLog ? (float) $latestWeightLog->rolling_average_weight : null;
        $rollingAverageDays = $latestWeightLog
            ? WeightLog::query()
                ->whereDate('logged_for', '>=', $latestWeightLog->logged_for->copy()->subDays(6)->toDateString())
                ->whereDate('logged_for', '<=', $latestWeightLog->logged_for->toDateString())
                ->count()
            : 0;

        return [
            'today_logged_weight' => $todayWeightLog ? WeightLossGoal::formatWeight($todayWeightLog->weight) : null,
            'today_logged_for' => $todayWeightLog?->logged_for?->toDateString(),
            'today_button_label' => $todayWeightLog ? 'Update today' : 'Add log',
            'rolling_average_weight' => $rollingAverageWeight !== null ? WeightLossGoal::formatWeight($rollingAverageWeight) : null,
            'rolling_average_days' => $rollingAverageDays,
            'metric_date_label' => $latestWeightLog ? $latestWeightLog->logged_for->format('M j') : null,
            'summary' => $rollingAverageWeight !== null
                ? 'Progress bars use the '.($rollingAverageDays ?: 1).'-day rolling average: '.WeightLossGoal::formatWeight($rollingAverageWeight).' kg.'
                : 'Log today\'s weight to start tracking your rolling-average trend.',
            'overall' => $overallGoal ? $this->weightProgressData(
                (float) $overallGoal['starting_weight'],
                (float) $overallGoal['final_goal_weight'],
                $rollingAverageWeight,
                'Overall progress',
                $overallGoal['start_month_label'].' to '.$overallGoal['goal_month_label']
            ) : null,
            'monthly' => $currentMonthGoal ? $this->weightProgressData(
                (float) $currentMonthGoal->starting_weight,
                (float) $currentMonthGoal->goal_weight,
                $rollingAverageWeight,
                'Monthly progress',
                $currentMonthGoal->monthLabel()
            ) : null,
            'has_any_goals' => $monthlyWeightGoals->isNotEmpty(),
            'has_current_month_goal' => (bool) $currentMonthGoal,
            'chart' => $this->weightChartData($currentMonthGoal, $today),
            'gauge' => $this->weightGaugeData($rollingAverageWeight),
        ];
    }

    private function weightProgressData(float $startingWeight, float $goalWeight, ?float $currentWeight, string $title, string $label): array
    {
        $distance = $startingWeight - $goalWeight;
        $percent = 0.0;
        $remainingWeight = null;
        $status = 'Add weight logs to start this progress bar.';

        if ($currentWeight !== null && $distance > 0) {
            $percent = min(100, max(0, round((($startingWeight - $currentWeight) / $distance) * 100, 2)));
            $remainingWeight = max(0, $currentWeight - $goalWeight);
            $status = $currentWeight <= $goalWeight
                ? 'Goal range reached.'
                : WeightLossGoal::formatWeight($remainingWeight).' kg to go.';
        }

        return [
            'title' => $title,
            'label' => $label,
            'starting_weight' => WeightLossGoal::formatWeight($startingWeight),
            'goal_weight' => WeightLossGoal::formatWeight($goalWeight),
            'current_weight' => $currentWeight !== null ? WeightLossGoal::formatWeight($currentWeight) : null,
            'percent' => $percent,
            'status' => $status,
        ];
    }

    private function weightChartData(?WeightLossGoal $currentMonthGoal, CarbonInterface $today): ?array
    {
        if (!$currentMonthGoal) {
            return null;
        }

        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        $daysInMonth = $today->daysInMonth;

        $logs = WeightLog::query()
            ->whereDate('logged_for', '>=', $monthStart->toDateString())
            ->whereDate('logged_for', '<=', $monthEnd->toDateString())
            ->orderBy('logged_for')
            ->get();

        $actualValues = array_fill(0, $daysInMonth, null);

        foreach ($logs as $log) {
            $actualValues[(int) $log->logged_for->day - 1] = round((float) $log->rolling_average_weight, 2);
        }

        $projectedValues = collect(range(1, $daysInMonth))
            ->map(function (int $day) use ($currentMonthGoal, $daysInMonth) {
                if ($daysInMonth === 1) {
                    return round((float) $currentMonthGoal->goal_weight, 2);
                }

                $progress = ($day - 1) / ($daysInMonth - 1);
                $value = (float) $currentMonthGoal->starting_weight
                    + (((float) $currentMonthGoal->goal_weight - (float) $currentMonthGoal->starting_weight) * $progress);

                return round($value, 2);
            })
            ->all();

        $latestActualLog = $logs->last();
        $statusLabel = 'Waiting';
        $statusClass = 'neutral';
        $statusDetail = 'Add a weight log to compare your rolling average against the projected pace.';

        if ($latestActualLog) {
            $comparisonDay = (int) $latestActualLog->logged_for->day;
            $targetWeight = $projectedValues[$comparisonDay - 1] ?? null;
            $actualWeight = round((float) $latestActualLog->rolling_average_weight, 2);

            if ($targetWeight !== null && $actualWeight <= $targetWeight) {
                $statusLabel = 'Success';
                $statusClass = 'success';
            } elseif ($targetWeight !== null) {
                $statusLabel = 'Failed';
                $statusClass = 'danger';
            }

            if ($targetWeight !== null) {
                $statusDetail = 'Day '.$comparisonDay.': '.WeightLossGoal::formatWeight($actualWeight).' kg avg vs '.WeightLossGoal::formatWeight($targetWeight).' kg target.';
            }
        }

        $allValues = collect($actualValues)
            ->filter(fn ($value) => $value !== null)
            ->push((float) $currentMonthGoal->starting_weight)
            ->push((float) $currentMonthGoal->goal_weight)
            ->merge($projectedValues);
        $minValue = (float) $allValues->min();
        $maxValue = (float) $allValues->max();
        $range = max(0.1, $maxValue - $minValue);
        $padding = max(0.5, $range * 0.12);
        $chartMin = floor(($minValue - $padding) * 10) / 10;
        $chartMax = ceil(($maxValue + $padding) * 10) / 10;

        if ($chartMax <= $chartMin) {
            $chartMax = $chartMin + 1;
        }

        return [
            'title' => 'Current month trend',
            'subtitle' => $currentMonthGoal->monthLabel(),
            'actual_points_count' => $logs->count(),
            'projected_goal_weight' => WeightLossGoal::formatWeight($currentMonthGoal->goal_weight),
            'labels' => range(1, $daysInMonth),
            'actual_values' => $actualValues,
            'projected_values' => $projectedValues,
            'status_label' => $statusLabel,
            'status_class' => $statusClass,
            'status_detail' => $statusDetail,
            'y_min' => round($chartMin, 2),
            'y_max' => round($chartMax, 2),
            'legend_actual' => 'Rolling average',
            'legend_projected' => 'Projected pace',
        ];
    }

    private function weightGaugeData(?float $rollingAverageWeight): array
    {
        $zones = [
            ['min' => 70, 'max' => 75, 'label' => '70-75 kg', 'class' => 'success'],
            ['min' => 75, 'max' => 80, 'label' => '75-80 kg', 'class' => 'soft-warning'],
            ['min' => 80, 'max' => 85, 'label' => '80-85 kg', 'class' => 'warning'],
            ['min' => 85, 'max' => 90, 'label' => '85-90 kg', 'class' => 'warm-warning'],
            ['min' => 90, 'max' => 95, 'label' => '90-95 kg', 'class' => 'amber'],
            ['min' => 95, 'max' => 100, 'label' => '95-100 kg', 'class' => 'amber-danger'],
            ['min' => 100, 'max' => 110, 'label' => '100-110 kg', 'class' => 'danger'],
        ];

        $minWeight = 70.0;
        $maxWeight = 110.0;
        $angle = -90.0;
        $zoneLabel = 'Waiting for data';
        $zoneClass = 'neutral';

        if ($rollingAverageWeight !== null) {
            $clampedWeight = min($maxWeight, max($minWeight, $rollingAverageWeight));
            $angle = -90 + ((($clampedWeight - $minWeight) / ($maxWeight - $minWeight)) * 180);

            foreach ($zones as $index => $zone) {
                $isLastZone = $index === array_key_last($zones);

                if ($clampedWeight >= $zone['min'] && ($clampedWeight < $zone['max'] || ($isLastZone && $clampedWeight <= $zone['max']))) {
                    $zoneLabel = $zone['label'];
                    $zoneClass = $zone['class'];
                    break;
                }
            }
        }

        return [
            'value_label' => $rollingAverageWeight !== null ? WeightLossGoal::formatWeight($rollingAverageWeight).' kg avg' : 'Waiting',
            'angle' => round($angle, 2),
            'zone_label' => $zoneLabel,
            'zone_class' => $zoneClass,
        ];
    }

    private function timelineCounters(CarbonInterface $today): array
    {
        $startDateValue = AppSetting::getValue('timeline_start_date');
        $deadlineDateValue = AppSetting::getValue('timeline_deadline_date');
        $startDate = $startDateValue ? now()->parse($startDateValue)->startOfDay() : null;
        $deadlineDate = $deadlineDateValue ? now()->parse($deadlineDateValue)->startOfDay() : null;

        $weekCount = null;
        $dayCount = null;

        if ($startDate) {
            $weekCount = $today->lessThan($startDate)
                ? 1
                : (int) floor($startDate->diffInDays($today) / 7) + 1;
        }

        if ($deadlineDate) {
            $dayCount = max(0, $today->diffInDays($deadlineDate, false));
        }

        return [
            'start_date_label' => $startDate?->format('M j, Y'),
            'deadline_date_label' => $deadlineDate?->format('M j, Y'),
            'week_count' => $weekCount,
            'day_count' => $dayCount,
        ];
    }
}
