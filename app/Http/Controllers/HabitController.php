<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HabitController extends Controller
{
    public function index(): View
    {
        $habits = Todo::query()
            ->with('latestLog')
            ->withCount('logs')
            ->withCount([
                'logs as completed_logs_count' => fn ($query) => $query->where('completed', true),
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('pages.habits-index', [
            'habits' => $habits,
            'weekdayOptions' => Todo::WEEKDAYS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedHabitData($request);

        Todo::query()->create([
            'name' => $data['name'],
            'days_of_week' => array_values($data['days_of_week']),
            'daily_goal' => $data['daily_goal'],
            'unit' => trim($data['unit']),
            'sort_order' => ((int) Todo::query()->max('sort_order')) + 1,
            'is_active' => true,
        ]);

        return redirect()
            ->route('habits.index')
            ->with('status', 'Habit created successfully.');
    }

    public function update(Request $request, Todo $habit): RedirectResponse
    {
        $data = $this->validatedHabitData($request);

        $habit->update([
            'name' => $data['name'],
            'days_of_week' => array_values($data['days_of_week']),
            'daily_goal' => $data['daily_goal'],
            'unit' => trim($data['unit']),
        ]);

        return redirect()
            ->route('habits.index')
            ->with('status', $habit->name.' was updated.');
    }

    public function destroy(Todo $habit): RedirectResponse
    {
        $name = $habit->name;

        $habit->delete();

        return redirect()
            ->route('habits.index')
            ->with('status', $name.' was deleted.');
    }

    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'habit_ids' => ['required', 'array', 'min:1'],
            'habit_ids.*' => ['required', 'integer', 'distinct', Rule::exists('todos', 'id')],
        ]);

        DB::transaction(function () use ($data) {
            foreach (array_values($data['habit_ids']) as $index => $habitId) {
                Todo::query()
                    ->whereKey($habitId)
                    ->update(['sort_order' => $index + 1]);
            }
        });

        return response()->json([
            'message' => 'Habit order saved.',
        ]);
    }

    private function validatedHabitData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'days_of_week' => ['required', 'array', 'min:1'],
            'days_of_week.*' => ['required', 'string', Rule::in(array_keys(Todo::WEEKDAYS))],
            'daily_goal' => ['required', 'numeric', 'gt:0'],
            'unit' => ['required', 'string', 'max:30'],
        ]);
    }
}
