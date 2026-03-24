@extends('layouts.app')

@section('page_eyebrow', 'Long View')
@section('page_title', $goal->name)
@section('page_intro', 'Add milestones to this goal, estimate the completion month for each step, and adjust the plan inline without leaving the page.')

@section('content')
    <section class="card">
        <div class="split-grid">
            <div class="stack">
                <div class="chip-row">
                    <a href="{{ route('goals.index') }}" class="chip">Back to goals</a>
                    <span class="chip">{{ $goal->milestones->count() }} milestones</span>
                </div>
                <h2>Milestones</h2>
                <p>Create milestones for this goal, then edit them inline whenever the timeline shifts.</p>
            </div>

            <div class="button-row">
                <button
                    type="button"
                    class="button"
                    data-panel-toggle="create-milestone-panel"
                    aria-controls="create-milestone-panel"
                >
                    Add milestone
                </button>
            </div>
        </div>

        <div
            id="create-milestone-panel"
            class="hidden-panel {{ $errors->any() ? 'is-open' : '' }}"
            data-open="{{ $errors->any() ? 'true' : 'false' }}"
            style="margin-top: 18px;"
        >
            <form method="POST" action="{{ route('goals.milestones.store', $goal) }}" class="field-grid">
                @csrf

                <div class="field">
                    <label class="field-label" for="milestone-name">Milestone name</label>
                    <input
                        id="milestone-name"
                        class="input"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="Finish the first draft"
                    >
                </div>

                <div class="field">
                    <label class="field-label" for="milestone-month">Estimated completion month</label>
                    <input
                        id="milestone-month"
                        class="input"
                        type="month"
                        name="estimated_completion_month"
                        value="{{ old('estimated_completion_month', $suggestedMonth) }}"
                    >
                </div>

                <div class="button-row">
                    <button type="submit" class="button">Save milestone</button>
                </div>
            </form>
        </div>
    </section>

    @if ($goal->milestones->isEmpty())
        <section class="card empty-state">
            <h2>No milestones yet.</h2>
            <p>Add the first milestone above to start mapping this goal out.</p>
        </section>
    @else
        <section class="card">
            <div class="stack">
                <p>Newest milestones start at the top. Drag the rows to reorder them whenever the plan changes.</p>
            </div>

            <div
                class="stack milestone-list"
                data-milestone-reorder-list
                data-reorder-url="{{ route('goals.milestones.reorder', $goal) }}"
                style="margin-top: 18px;"
            >
                <div class="milestone-feedback" data-reorder-feedback hidden></div>

                @foreach ($goal->milestones as $milestone)
                    <div class="milestone-item" data-milestone-item data-milestone-id="{{ $milestone->id }}" draggable="true">
                        <form
                            class="milestone-row"
                            data-milestone-form
                            action="{{ route('goals.milestones.update', [$goal, $milestone]) }}"
                        >
                            @csrf
                            @method('PATCH')

                            <div class="milestone-main">
                                <div class="field">
                                    <label class="field-label" for="milestone-name-{{ $milestone->id }}">Milestone name</label>
                                    <input
                                        id="milestone-name-{{ $milestone->id }}"
                                        class="input"
                                        type="text"
                                        name="name"
                                        value="{{ $milestone->name }}"
                                    >
                                </div>

                                <div class="field">
                                    <label class="field-label" for="milestone-month-{{ $milestone->id }}">Estimated completion month</label>
                                    <input
                                        id="milestone-month-{{ $milestone->id }}"
                                        class="input"
                                        type="month"
                                        name="estimated_completion_month"
                                        value="{{ $milestone->estimated_completion_month->format('Y-m') }}"
                                    >
                                </div>
                            </div>

                            <div class="milestone-side">
                                <button type="submit" class="button" data-milestone-submit>Save</button>
                                <p class="milestone-feedback" data-milestone-feedback aria-live="polite"></p>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endsection

@push('page_styles')
    <style>
        .milestone-list {
            gap: 16px;
        }

        .milestone-item {
            transition: opacity 160ms ease;
        }

        .milestone-item.is-dragging {
            opacity: 0.58;
        }

        .milestone-row {
            display: grid;
            gap: 16px;
            padding: 18px;
            border-radius: 20px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.66);
            cursor: grab;
        }

        .milestone-row:active {
            cursor: grabbing;
        }

        .milestone-row.is-saving {
            opacity: 0.7;
        }

        .milestone-main {
            display: grid;
            gap: 16px;
        }

        .milestone-side {
            display: grid;
            gap: 10px;
            align-content: start;
        }

        .milestone-feedback {
            min-height: 1.2rem;
            margin: 0;
            color: var(--muted);
            font-size: 0.9rem;
        }

        .milestone-feedback[data-state="success"] {
            color: var(--success);
        }

        .milestone-feedback[data-state="error"] {
            color: var(--danger);
        }

        @media (min-width: 900px) {
            .milestone-row {
                grid-template-columns: minmax(0, 1fr) 220px;
                align-items: end;
            }

            .milestone-main {
                grid-template-columns: minmax(0, 1.35fr) minmax(220px, 0.65fr);
            }
        }
    </style>
@endpush

@push('page_scripts')
    <script>
        (function () {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!token || !window.fetch) {
                return;
            }

            document.querySelectorAll('[data-milestone-form]').forEach(function (form) {
                const submitButton = form.querySelector('[data-milestone-submit]');
                const feedback = form.querySelector('[data-milestone-feedback]');

                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    const formData = new FormData(form);
                    const originalLabel = submitButton.textContent;

                    form.classList.add('is-saving');
                    submitButton.disabled = true;
                    submitButton.textContent = 'Saving...';
                    feedback.textContent = '';
                    feedback.dataset.state = '';

                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData,
                    })
                        .then(async function (response) {
                            const payload = await response.json().catch(function () {
                                return {};
                            });

                            if (!response.ok) {
                                throw payload;
                            }

                            return payload;
                        })
                        .then(function (payload) {
                            feedback.textContent = payload.message || 'Saved.';
                            feedback.dataset.state = 'success';
                        })
                        .catch(function (payload) {
                            let message = 'Could not save this milestone right now.';

                            if (payload && payload.message) {
                                message = payload.message;
                            }

                            if (payload && payload.errors) {
                                const firstField = Object.keys(payload.errors)[0];

                                if (firstField && payload.errors[firstField] && payload.errors[firstField][0]) {
                                    message = payload.errors[firstField][0];
                                }
                            }

                            feedback.textContent = message;
                            feedback.dataset.state = 'error';
                        })
                        .finally(function () {
                            form.classList.remove('is-saving');
                            submitButton.disabled = false;
                            submitButton.textContent = originalLabel;
                        });
                });
            });

            const reorderList = document.querySelector('[data-milestone-reorder-list]');

            if (!reorderList) {
                return;
            }

            const reorderFeedback = reorderList.querySelector('[data-reorder-feedback]');
            let draggedItem = null;
            let previousOrder = [];

            function getItems() {
                return Array.from(reorderList.querySelectorAll('[data-milestone-item]'));
            }

            function getOrder() {
                return getItems().map(function (item) {
                    return item.dataset.milestoneId;
                });
            }

            function restoreOrder(order) {
                const itemsById = new Map(getItems().map(function (item) {
                    return [item.dataset.milestoneId, item];
                }));

                order.forEach(function (id) {
                    const item = itemsById.get(id);

                    if (item) {
                        reorderList.appendChild(item);
                    }
                });
            }

            function showReorderFeedback(message, isError) {
                if (!reorderFeedback) {
                    return;
                }

                reorderFeedback.textContent = message;
                reorderFeedback.hidden = false;
                reorderFeedback.dataset.state = isError ? 'error' : 'success';

                window.clearTimeout(showReorderFeedback.timeoutId);
                showReorderFeedback.timeoutId = window.setTimeout(function () {
                    reorderFeedback.hidden = true;
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
                            'X-CSRF-TOKEN': token,
                        },
                        body: JSON.stringify({
                            milestone_ids: nextOrder,
                        }),
                    });

                    if (!response.ok) {
                        throw new Error('Unable to save milestone order.');
                    }

                    showReorderFeedback('Milestone order saved.', false);
                } catch (error) {
                    restoreOrder(previousOrder);
                    showReorderFeedback(error.message || 'Unable to save milestone order.', true);
                }
            }

            getItems().forEach(function (item) {
                item.addEventListener('dragstart', function (event) {
                    if (event.target.closest('button, input, label')) {
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
