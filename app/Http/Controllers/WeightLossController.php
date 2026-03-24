<?php

namespace App\Http\Controllers;

use App\Models\WeightLossGoal;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WeightLossController extends Controller
{
    public function index(): View
    {
        $monthlyGoals = WeightLossGoal::query()
            ->orderBy('month')
            ->get();

        $lastGoal = $monthlyGoals->last();

        return view('pages.weight-loss', [
            'monthlyGoals' => $monthlyGoals,
            'overallGoal' => WeightLossGoal::overallSummary($monthlyGoals),
            'suggestedMonth' => optional($lastGoal?->month?->copy()->addMonth())->format('Y-m') ?? now()->format('Y-m'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'starting_weight' => ['required', 'numeric', 'gt:0'],
            'goal_weight' => ['required', 'numeric', 'gt:0', 'lt:starting_weight'],
        ]);

        $month = Carbon::createFromFormat('Y-m', $data['month'])->startOfMonth()->toDateString();

        validator(
            ['month' => $month],
            ['month' => ['required', Rule::unique('weight_loss_goals', 'month')]],
            ['month.unique' => 'That month already has a weight loss goal.']
        )->validate();

        WeightLossGoal::query()->create([
            'month' => $month,
            'starting_weight' => $data['starting_weight'],
            'goal_weight' => $data['goal_weight'],
        ]);

        return redirect()
            ->route('weight-loss.index')
            ->with('status', 'Monthly weight loss goal created.');
    }
}
