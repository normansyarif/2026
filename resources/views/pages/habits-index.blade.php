@extends('layouts.app')

@section('page_eyebrow', 'Routine Builder')
@section('page_title', 'Habits')
@section('page_intro', 'Create recurring habits, choose which weekdays they should appear on, and define the daily amount needed for completion.')

@section('content')
    @php
        $editingHabitId = old('edit_habit_id');
    @endphp

    <section class="card">
        <div class="split-grid">
            <div class="stack">
                <h2>Create new habit</h2>
                <p>Use the button to reveal the form, then set the name, schedule, goal, and unit for the habit you want to track.</p>
            </div>

            <div class="button-row">
                <button
                    type="button"
                    class="button"
                    data-panel-toggle="create-habit-panel"
                    aria-controls="create-habit-panel"
                >
                    Create new habit
                </button>
            </div>
        </div>

        <div
            id="create-habit-panel"
            class="hidden-panel {{ $errors->any() && !$editingHabitId ? 'is-open' : '' }}"
            data-open="{{ $errors->any() && !$editingHabitId ? 'true' : 'false' }}"
            style="margin-top: 18px;"
        >
            <form method="POST" action="{{ route('habits.store') }}" class="field-grid">
                @csrf

                <div class="field">
                    <label class="field-label" for="name">Habit name</label>
                    <input id="name" class="input" type="text" name="name" value="{{ old('name') }}" placeholder="Walk">
                </div>

                <div class="field">
                    <span class="field-label">Days of the week</span>
                    <span class="field-help">Pick every day this habit should appear on the Today page.</span>

                    <div class="checkbox-grid">
                        @foreach ($weekdayOptions as $value => $label)
                            <label class="day-option">
                                <input
                                    type="checkbox"
                                    name="days_of_week[]"
                                    value="{{ $value }}"
                                    {{ in_array($value, old('days_of_week', []), true) ? 'checked' : '' }}
                                >
                                <span class="day-label">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label class="field-label" for="daily_goal">Daily completion goal</label>
                        <input id="daily_goal" class="number-input" type="number" name="daily_goal" min="0.01" step="0.01" inputmode="decimal" value="{{ old('daily_goal') }}" placeholder="6000">
                    </div>

                    <div class="field">
                        <label class="field-label" for="unit">Unit</label>
                        <input id="unit" class="input" type="text" name="unit" value="{{ old('unit') }}" placeholder="step">
                    </div>
                </div>

                <div class="button-row">
                    <button type="submit" class="button">Save habit</button>
                </div>
            </form>
        </div>
    </section>

    <section class="card">
        <div class="split-grid">
            <div class="stack">
                <h2>Created habits</h2>
                <p>Your recurring habits live here. Drag the cards into the order you want, and the Today page will follow that same sequence.</p>
            </div>

            <span class="chip">{{ $habits->count() }} total</span>
        </div>
    </section>

    @if ($habits->isEmpty())
        <section class="card empty-state">
            <h2>No habits yet.</h2>
            <p>Create the first one above and it will start showing up on the matching weekdays.</p>
        </section>
    @else
        <div
            class="habit-list"
            data-reorder-list
            data-reorder-url="{{ route('habits.reorder') }}"
        >
            <div class="reorder-feedback" data-reorder-feedback hidden></div>

            @foreach ($habits as $habit)
                <div class="habit-sort-item" data-reorder-item data-habit-id="{{ $habit->id }}" draggable="true">
                    <section class="card habit-card">
                        <div class="split-grid">
                            <div class="stack">
                                <div class="chip-row">
                                    <span class="chip drag-chip" aria-hidden="true">Drag to reorder</span>
                                    <span class="chip">{{ $habit->goalLabel() }}</span>
                                    <span class="chip">{{ $habit->scheduleLabel() }}</span>
                                </div>
                                <h2>{{ $habit->name }}</h2>
                                <p>
                                    @if ($habit->latestLog)
                                        Latest log: {{ $habit->latestLog->logged_for->format('M j') }} with {{ \App\Models\Todo::formatAmount($habit->latestLog->value) }} {{ $habit->unit }}.
                                    @else
                                        No logs yet. This habit will start tracking as soon as you log it from the Today page.
                                    @endif
                                </p>
                            </div>

                            <div class="button-row">
                                <button
                                    type="button"
                                    class="button-secondary"
                                    data-panel-toggle="edit-habit-panel-{{ $habit->id }}"
                                    aria-controls="edit-habit-panel-{{ $habit->id }}"
                                >
                                    Edit
                                </button>

                                <form method="POST" action="{{ route('habits.destroy', $habit) }}" class="danger-form" onsubmit="return confirm('Delete this habit and its logs?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button-danger">Delete</button>
                                </form>
                            </div>
                        </div>

                        <div class="stats-grid">
                            <div class="stat">
                                <span class="stat-label">Total logs</span>
                                <span class="stat-value">{{ $habit->logs_count }}</span>
                            </div>
                            <div class="stat">
                                <span class="stat-label">Completed days</span>
                                <span class="stat-value">{{ $habit->completed_logs_count }}</span>
                            </div>
                        </div>

                        <div
                            id="edit-habit-panel-{{ $habit->id }}"
                            class="hidden-panel {{ (string) $editingHabitId === (string) $habit->id ? 'is-open' : '' }}"
                            data-open="{{ (string) $editingHabitId === (string) $habit->id ? 'true' : 'false' }}"
                            style="margin-top: 18px;"
                        >
                            <form method="POST" action="{{ route('habits.update', $habit) }}" class="field-grid">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="edit_habit_id" value="{{ $habit->id }}">

                                <div class="field">
                                    <label class="field-label" for="edit-name-{{ $habit->id }}">Habit name</label>
                                    <input
                                        id="edit-name-{{ $habit->id }}"
                                        class="input"
                                        type="text"
                                        name="name"
                                        value="{{ (string) $editingHabitId === (string) $habit->id ? old('name') : $habit->name }}"
                                        placeholder="Walk"
                                    >
                                </div>

                                <div class="field">
                                    <span class="field-label">Days of the week</span>
                                    <span class="field-help">Pick every day this habit should appear on the Today page.</span>

                                    <div class="checkbox-grid">
                                        @php
                                            $selectedDays = (string) $editingHabitId === (string) $habit->id ? old('days_of_week', []) : ($habit->days_of_week ?? []);
                                        @endphp

                                        @foreach ($weekdayOptions as $value => $label)
                                            <label class="day-option">
                                                <input
                                                    type="checkbox"
                                                    name="days_of_week[]"
                                                    value="{{ $value }}"
                                                    {{ in_array($value, $selectedDays, true) ? 'checked' : '' }}
                                                >
                                                <span class="day-label">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="field-row">
                                    <div class="field">
                                        <label class="field-label" for="edit-daily-goal-{{ $habit->id }}">Daily completion goal</label>
                                        <input
                                            id="edit-daily-goal-{{ $habit->id }}"
                                            class="number-input"
                                            type="number"
                                            name="daily_goal"
                                            min="0.01"
                                            step="0.01"
                                            inputmode="decimal"
                                            value="{{ (string) $editingHabitId === (string) $habit->id ? old('daily_goal') : \App\Models\Todo::formatAmount($habit->daily_goal) }}"
                                            placeholder="6000"
                                        >
                                    </div>

                                    <div class="field">
                                        <label class="field-label" for="edit-unit-{{ $habit->id }}">Unit</label>
                                        <input
                                            id="edit-unit-{{ $habit->id }}"
                                            class="input"
                                            type="text"
                                            name="unit"
                                            value="{{ (string) $editingHabitId === (string) $habit->id ? old('unit') : $habit->unit }}"
                                            placeholder="step"
                                        >
                                    </div>
                                </div>

                                <div class="button-row">
                                    <button type="submit" class="button">Update habit</button>
                                </div>
                            </form>
                        </div>
                    </section>
                </div>
            @endforeach
        </div>
    @endif
@endsection

@push('page_styles')
    <style>
        .habit-list {
            display: grid;
            gap: 18px;
        }

        .habit-sort-item {
            transition: opacity 160ms ease, transform 160ms ease;
        }

        .habit-sort-item:hover .drag-chip {
            border-color: rgba(22, 89, 70, 0.3);
            color: var(--accent);
        }

        .habit-sort-item.is-dragging {
            opacity: 0.58;
        }

        .habit-sort-item.is-dragging .habit-card {
            box-shadow: 0 20px 48px rgba(22, 89, 70, 0.16);
        }

        .habit-sort-item .habit-card {
            cursor: grab;
        }

        .habit-sort-item .habit-card:active {
            cursor: grabbing;
        }

        .drag-chip {
            background: rgba(22, 89, 70, 0.08);
        }

        .reorder-feedback {
            padding: 12px 16px;
            border-radius: 18px;
            border: 1px solid rgba(29, 108, 68, 0.18);
            background: rgba(240, 251, 245, 0.9);
            color: var(--success);
        }

        .reorder-feedback.is-error {
            border-color: rgba(156, 66, 52, 0.22);
            background: rgba(254, 245, 242, 0.92);
            color: var(--danger);
        }
    </style>
@endpush

@push('page_scripts')
    <script>
        (function () {
            const reorderList = document.querySelector('[data-reorder-list]');

            if (!reorderList) {
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const feedback = reorderList.querySelector('[data-reorder-feedback]');
            let draggedItem = null;
            let previousOrder = [];

            function getItems() {
                return Array.from(reorderList.querySelectorAll('[data-reorder-item]'));
            }

            function getOrder() {
                return getItems().map(function (item) {
                    return item.dataset.habitId;
                });
            }

            function restoreOrder(order) {
                const itemsById = new Map(getItems().map(function (item) {
                    return [item.dataset.habitId, item];
                }));

                order.forEach(function (id) {
                    const item = itemsById.get(id);

                    if (item) {
                        reorderList.appendChild(item);
                    }
                });
            }

            function showFeedback(message, isError) {
                if (!feedback) {
                    return;
                }

                feedback.textContent = message;
                feedback.hidden = false;
                feedback.classList.toggle('is-error', !!isError);

                window.clearTimeout(showFeedback.timeoutId);
                showFeedback.timeoutId = window.setTimeout(function () {
                    feedback.hidden = true;
                }, 2200);
            }

            function getDragAfterElement(y) {
                return getItems()
                    .filter(function (item) {
                        return item !== draggedItem;
                    })
                    .reduce(function (closest, item) {
                        const box = item.getBoundingClientRect();
                        const offset = y - box.top - (box.height / 2);

                        if (offset < 0 && offset > closest.offset) {
                            return { offset: offset, element: item };
                        }

                        return closest;
                    }, { offset: Number.NEGATIVE_INFINITY, element: null }).element;
            }

            async function persistOrder() {
                const nextOrder = getOrder();

                if (JSON.stringify(nextOrder) === JSON.stringify(previousOrder)) {
                    return;
                }

                try {
                    const response = await fetch(reorderList.dataset.reorderUrl, {
                        method: 'PATCH',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            habit_ids: nextOrder,
                        }),
                    });

                    if (!response.ok) {
                        throw new Error('Unable to save the new order.');
                    }

                    showFeedback('Habit order saved.', false);
                } catch (error) {
                    restoreOrder(previousOrder);
                    showFeedback(error.message || 'Unable to save the new order.', true);
                }
            }

            getItems().forEach(function (item) {
                item.addEventListener('dragstart', function (event) {
                    if (event.target.closest('button, form, input, label')) {
                        event.preventDefault();
                        return;
                    }

                    draggedItem = item;
                    previousOrder = getOrder();
                    item.classList.add('is-dragging');
                });

                item.addEventListener('dragend', function () {
                    item.classList.remove('is-dragging');
                    draggedItem = null;
                });
            });

            reorderList.addEventListener('dragover', function (event) {
                if (!draggedItem) {
                    return;
                }

                event.preventDefault();

                const afterElement = getDragAfterElement(event.clientY);

                if (!afterElement) {
                    reorderList.appendChild(draggedItem);
                    return;
                }

                reorderList.insertBefore(draggedItem, afterElement);
            });

            reorderList.addEventListener('drop', function (event) {
                if (!draggedItem) {
                    return;
                }

                event.preventDefault();
                persistOrder();
            });
        })();
    </script>
@endpush
