<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\GoalMilestone;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GoalMilestoneController extends Controller
{
    public function store(Request $request, Goal $goal): RedirectResponse
    {
        $data = $this->validatedData($request);

        DB::transaction(function () use ($goal, $data) {
            $goal->milestones()->increment('sort_order');

            $goal->milestones()->create([
                'name' => trim($data['name']),
                'estimated_completion_month' => Carbon::createFromFormat('Y-m', $data['estimated_completion_month'])->startOfMonth()->toDateString(),
                'sort_order' => 1,
            ]);
        });

        return redirect()
            ->route('goals.show', $goal)
            ->with('status', 'Milestone created.');
    }

    public function update(Request $request, Goal $goal, GoalMilestone $milestone): JsonResponse|RedirectResponse
    {
        abort_unless($milestone->goal_id === $goal->id, 404);

        $data = $this->validatedData($request);

        $milestone->update([
            'name' => trim($data['name']),
            'estimated_completion_month' => Carbon::createFromFormat('Y-m', $data['estimated_completion_month'])->startOfMonth()->toDateString(),
        ]);

        $message = 'Milestone updated.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'milestone' => [
                    'id' => $milestone->id,
                    'name' => $milestone->name,
                    'estimated_completion_month' => $milestone->estimated_completion_month->format('Y-m'),
                    'estimated_completion_label' => $milestone->monthLabel(),
                ],
            ]);
        }

        return redirect()
            ->route('goals.show', $goal)
            ->with('status', $message);
    }

    public function reorder(Request $request, Goal $goal): JsonResponse
    {
        $data = $request->validate([
            'milestone_ids' => ['required', 'array', 'min:1'],
            'milestone_ids.*' => ['required', 'integer', 'distinct', Rule::exists('goal_milestones', 'id')],
        ]);

        DB::transaction(function () use ($goal, $data) {
            foreach (array_values($data['milestone_ids']) as $index => $milestoneId) {
                GoalMilestone::query()
                    ->where('goal_id', $goal->id)
                    ->whereKey($milestoneId)
                    ->update([
                        'sort_order' => $index + 1,
                    ]);
            }
        });

        return response()->json([
            'message' => 'Milestone order saved.',
        ]);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'estimated_completion_month' => ['required', 'date_format:Y-m'],
        ]);
    }
}
