<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GoalController extends Controller
{
    public function index(): View
    {
        return view('pages.goals-index', [
            'goals' => Goal::query()
                ->withCount('milestones')
                ->with('nextMilestone')
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $goal = Goal::query()->create([
            'name' => trim($data['name']),
        ]);

        return redirect()
            ->route('goals.show', $goal)
            ->with('status', 'Goal created. You can add milestones now.');
    }

    public function show(Goal $goal): View
    {
        $goal->load('milestones');

        return view('pages.goal-milestones', [
            'goal' => $goal,
            'suggestedMonth' => optional($goal->milestones->last()?->estimated_completion_month?->copy()->addMonth())->format('Y-m') ?? now()->format('Y-m'),
        ]);
    }
}
