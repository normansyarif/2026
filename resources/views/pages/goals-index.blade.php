@extends('layouts.app')

@section('page_eyebrow', 'Long View')
@section('page_title', 'Goals')
@section('page_intro', 'Create a goal, then break it into milestones with target months so you can map the bigger picture over time.')

@section('content')
    <section class="card">
        <div class="split-grid">
            <div class="stack">
                <h2>Create new goal</h2>
                <p>Start with the goal name. After saving it, you will land on its milestone page so you can add the first milestones right away.</p>
            </div>

            <div class="button-row">
                <button
                    type="button"
                    class="button"
                    data-panel-toggle="create-goal-panel"
                    aria-controls="create-goal-panel"
                >
                    Create new goal
                </button>
            </div>
        </div>

        <div
            id="create-goal-panel"
            class="hidden-panel {{ $errors->any() ? 'is-open' : '' }}"
            data-open="{{ $errors->any() ? 'true' : 'false' }}"
            style="margin-top: 18px;"
        >
            <form method="POST" action="{{ route('goals.store') }}" class="field-grid">
                @csrf

                <div class="field">
                    <label class="field-label" for="goal-name">Goal name</label>
                    <input
                        id="goal-name"
                        class="input"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="Launch my portfolio"
                    >
                </div>

                <div class="button-row">
                    <button type="submit" class="button">Save goal</button>
                </div>
            </form>
        </div>
    </section>

    <section class="card">
        <div class="split-grid">
            <div class="stack">
                <h2>Created goals</h2>
                <p>Open any goal to manage its milestones and keep the timeline updated.</p>
            </div>

            <span class="chip">{{ $goals->count() }} total</span>
        </div>
    </section>

    @if ($goals->isEmpty())
        <section class="card empty-state">
            <h2>No goals yet.</h2>
            <p>Create the first goal above, then start adding milestones to it.</p>
        </section>
    @else
        @foreach ($goals as $goal)
            @php
                $nextMilestone = $goal->nextMilestone;
            @endphp

            <a href="{{ route('goals.show', $goal) }}" class="goal-link-card">
                <section class="card">
                    <div class="split-grid">
                        <div class="stack">
                            <div class="chip-row">
                                <span class="chip">{{ $goal->milestones_count }} milestones</span>
                                @if ($nextMilestone)
                                    <span class="chip">Next: {{ $nextMilestone->monthLabel() }}</span>
                                @endif
                            </div>
                            <h2>{{ $goal->name }}</h2>
                            <p>
                                @if ($nextMilestone)
                                    {{ $nextMilestone->name }} is the next milestone on the roadmap.
                                @else
                                    No milestones yet. Open this goal to add the first one.
                                @endif
                            </p>
                        </div>

                        <span class="button-secondary">Open</span>
                    </div>
                </section>
            </a>
        @endforeach
    @endif
@endsection

@push('page_styles')
    <style>
        .goal-link-card {
            display: block;
        }

        .goal-link-card .card {
            transition: transform 160ms ease, border-color 160ms ease, background 160ms ease;
        }

        .goal-link-card:hover .card,
        .goal-link-card:focus-visible .card {
            transform: translateY(-1px);
            border-color: rgba(22, 89, 70, 0.24);
            background: rgba(255, 255, 255, 0.9);
            outline: none;
        }
    </style>
@endpush
