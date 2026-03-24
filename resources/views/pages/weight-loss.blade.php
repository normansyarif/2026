@extends('layouts.app')

@section('page_eyebrow', 'Health Track')
@section('page_title', 'Weight Loss')
@section('page_intro', 'Plan your weight loss month by month. Each monthly goal stores a starting weight and a target weight, and the page also keeps the overall path ready for a future progress bar.')

@section('content')
    <section class="card">
        <div class="split-grid">
            <div class="stack">
                <h2>Monthly goals</h2>
                <p>Create one goal per month, like April 2026 or May 2026, and set the starting weight together with the target weight for that month.</p>
            </div>

            <div class="button-row">
                <button
                    type="button"
                    class="button"
                    data-panel-toggle="create-weight-loss-panel"
                    aria-controls="create-weight-loss-panel"
                >
                    Add monthly goal
                </button>
            </div>
        </div>

        <div
            id="create-weight-loss-panel"
            class="hidden-panel {{ $errors->any() ? 'is-open' : '' }}"
            data-open="{{ $errors->any() ? 'true' : 'false' }}"
            style="margin-top: 18px;"
        >
            <form method="POST" action="{{ route('weight-loss.store') }}" class="field-grid">
                @csrf

                <div class="field-row">
                    <div class="field">
                        <label class="field-label" for="month">Month</label>
                        <input
                            id="month"
                            class="input"
                            type="month"
                            name="month"
                            value="{{ old('month', $suggestedMonth) }}"
                        >
                    </div>

                    <div class="field">
                        <label class="field-label" for="starting_weight">Starting weight</label>
                        <input
                            id="starting_weight"
                            class="number-input"
                            type="number"
                            name="starting_weight"
                            min="0.1"
                            step="0.1"
                            inputmode="decimal"
                            value="{{ old('starting_weight') }}"
                            placeholder="84.5"
                        >
                    </div>
                </div>

                <div class="field">
                    <label class="field-label" for="goal_weight">Goal weight</label>
                    <input
                        id="goal_weight"
                        class="number-input"
                        type="number"
                        name="goal_weight"
                        min="0.1"
                        step="0.1"
                        inputmode="decimal"
                        value="{{ old('goal_weight') }}"
                        placeholder="82"
                    >
                    <span class="field-help">Use the target weight you want to reach by the end of that month.</span>
                </div>

                <div class="button-row">
                    <button type="submit" class="button">Save monthly goal</button>
                </div>
            </form>
        </div>
    </section>

    @if ($overallGoal)
        <section class="card">
            <h2>Overall goal</h2>
            <p>This summary keeps the first starting weight and the latest month checkpoint ready for the progress bar you want to add next.</p>

            <div class="stats-grid">
                <div class="stat">
                    <span class="stat-label">Overall start</span>
                    <span class="stat-value">{{ \App\Models\WeightLossGoal::formatWeight($overallGoal['starting_weight']) }} kg</span>
                </div>
                <div class="stat">
                    <span class="stat-label">Goal checkpoint</span>
                    <span class="stat-value">{{ \App\Models\WeightLossGoal::formatWeight($overallGoal['goal_weight']) }} kg</span>
                </div>
                <div class="stat">
                    <span class="stat-label">Final month target</span>
                    <span class="stat-value">{{ \App\Models\WeightLossGoal::formatWeight($overallGoal['final_goal_weight']) }} kg</span>
                </div>
            </div>

            <div class="chip-row" style="margin-top: 16px;">
                <span class="chip">Start month: {{ $overallGoal['start_month_label'] }}</span>
                <span class="chip">Latest month: {{ $overallGoal['goal_month_label'] }}</span>
            </div>
        </section>
    @endif

    @if ($monthlyGoals->isEmpty())
        <section class="card empty-state">
            <h2>No monthly goals yet.</h2>
            <p>Add your first month and the plan will start building from there.</p>
        </section>
    @else
        <div class="weight-loss-grid">
            @foreach ($monthlyGoals as $goal)
                <section class="card">
                    <div class="split-grid">
                        <div class="stack">
                            <h2>{{ $goal->monthLabel() }}</h2>
                            <p>{{ $goal->lossAmountLabel() }} kg planned loss for the month.</p>
                        </div>

                        <span class="chip">{{ $goal->month->format('M Y') }}</span>
                    </div>

                    <div class="stats-grid">
                        <div class="stat">
                            <span class="stat-label">Starting weight</span>
                            <span class="stat-value">{{ $goal->startWeightLabel() }} kg</span>
                        </div>
                        <div class="stat">
                            <span class="stat-label">Goal weight</span>
                            <span class="stat-value">{{ $goal->goalWeightLabel() }} kg</span>
                        </div>
                    </div>
                </section>
            @endforeach
        </div>
    @endif
@endsection

@push('page_styles')
    <style>
        .weight-loss-grid {
            display: grid;
            gap: 18px;
        }
    </style>
@endpush
