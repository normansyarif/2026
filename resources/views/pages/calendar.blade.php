@extends('layouts.app')

@section('page_title', 'Calendar')

@push('page_styles')
    <style>
        .month-calendar-shell {
            display: grid;
            gap: 18px;
        }

        .month-calendar-head {
            align-items: center;
        }

        .month-calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 10px;
        }

        .month-calendar-weekday {
            padding: 8px 6px;
            text-align: center;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
        }

        .month-calendar-day {
            appearance: none;
            min-height: 88px;
            padding: 12px;
            border-radius: 20px;
            border: 1px solid var(--line);
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            background: rgba(255, 255, 255, 0.76);
            text-align: left;
            cursor: pointer;
            transition: transform 160ms ease, border-color 160ms ease;
        }

        .month-calendar-day:hover,
        .month-calendar-day:focus-visible {
            transform: translateY(-1px);
            border-color: rgba(22, 89, 70, 0.24);
            outline: none;
        }

        .month-calendar-day.is-complete {
            background: rgba(221, 244, 230, 0.92);
            border-color: rgba(29, 108, 68, 0.18);
        }

        .month-calendar-day.is-missed {
            background: rgba(255, 244, 241, 0.92);
            border-color: rgba(156, 66, 52, 0.16);
        }

        .month-calendar-day.is-future {
            background: rgba(250, 242, 219, 0.92);
            border-color: rgba(184, 128, 31, 0.18);
        }

        .month-calendar-day.is-today {
            box-shadow: inset 0 0 0 2px rgba(22, 89, 70, 0.16);
        }

        .month-calendar-day.blank {
            min-height: 0;
            padding: 0;
            border: 0;
            background: transparent;
        }

        .month-calendar-date {
            font-size: 1rem;
            font-weight: 700;
            color: var(--ink);
        }

        .month-calendar-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .month-calendar-legend span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: var(--muted);
        }

        .month-calendar-legend i {
            width: 14px;
            height: 14px;
            border-radius: 999px;
            display: inline-block;
            border: 1px solid var(--line);
        }

        .legend-complete {
            background: rgba(221, 244, 230, 0.92);
        }

        .legend-missed {
            background: rgba(255, 244, 241, 0.92);
        }

        .legend-future {
            background: rgba(250, 242, 219, 0.92);
        }

        .month-calendar-feedback {
            min-height: 1.2rem;
            margin: 0;
            color: var(--muted);
            font-size: 0.88rem;
        }

        .month-calendar-feedback[data-state="error"] {
            color: var(--danger);
        }

        .calendar-detail-backdrop {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
            background: rgba(17, 25, 21, 0.58);
            z-index: 80;
        }

        .calendar-detail-backdrop.is-open {
            display: flex;
        }

        .calendar-detail-modal {
            width: min(100%, 460px);
            padding: 22px;
            border-radius: 24px;
            border: 1px solid var(--line);
            background: rgba(255, 252, 245, 0.98);
            box-shadow: 0 28px 80px rgba(18, 27, 23, 0.24);
            display: grid;
            gap: 16px;
        }

        .calendar-detail-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .calendar-detail-copy {
            display: grid;
            gap: 8px;
        }

        .calendar-detail-status {
            font-size: 0.9rem;
            font-weight: 700;
        }

        .calendar-detail-list {
            display: grid;
            gap: 10px;
        }

        .calendar-detail-item {
            padding: 14px;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.72);
            display: grid;
            gap: 6px;
        }

        .calendar-detail-item-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .calendar-detail-item-head strong {
            font-size: 1rem;
        }

        .calendar-detail-empty {
            padding: 14px;
            border-radius: 18px;
            border: 1px dashed var(--line);
            color: var(--muted);
            background: rgba(255, 255, 255, 0.48);
        }

        [data-month-calendar-container][data-loading="true"] {
            opacity: 0.72;
            transition: opacity 140ms ease;
        }

        @media (max-width: 760px) {
            .month-calendar-grid {
                gap: 8px;
            }

            .month-calendar-day {
                min-height: 68px;
                padding: 10px 8px;
            }
        }
    </style>
@endpush

@section('content')

    <section class="card">
        <div data-month-calendar-container>
            @include('pages.partials.month-completion-calendar', $monthView)
        </div>

        <p class="month-calendar-feedback" data-calendar-feedback aria-live="polite"></p>
    </section>

    <div class="calendar-detail-backdrop" data-calendar-detail-modal hidden>
        <div class="calendar-detail-modal" role="dialog" aria-modal="true" aria-labelledby="calendar-detail-title">
            <div class="calendar-detail-head">
                <div class="calendar-detail-copy">
                    <span class="eyebrow" style="margin:0;">Day details</span>
                    <h2 id="calendar-detail-title">Date</h2>
                    <span class="calendar-detail-status" data-calendar-detail-status></span>
                </div>

                <button type="button" class="button-secondary" data-close-calendar-detail>Close</button>
            </div>

            <div class="card" style="padding:16px;">
                <div class="stack" style="gap:8px;">
                    <strong>Weight</strong>
                    <p data-calendar-detail-weight style="margin:0;"></p>
                </div>
            </div>

            <div class="card" style="padding:16px;">
                <div class="stack" style="gap:10px;">
                    <strong>Habits</strong>
                    <p data-calendar-detail-habit-summary style="margin:0;"></p>
                    <div class="calendar-detail-list" data-calendar-detail-habits></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page_scripts')
    <script>
        (function () {
            const container = document.querySelector('[data-month-calendar-container]');
            const feedback = document.querySelector('[data-calendar-feedback]');
            const detailModal = document.querySelector('[data-calendar-detail-modal]');
            const detailTitle = document.getElementById('calendar-detail-title');
            const detailStatus = document.querySelector('[data-calendar-detail-status]');
            const detailWeight = document.querySelector('[data-calendar-detail-weight]');
            const detailHabitSummary = document.querySelector('[data-calendar-detail-habit-summary]');
            const detailHabits = document.querySelector('[data-calendar-detail-habits]');
            let dayDetails = {};

            if (!container || !window.fetch) {
                return;
            }

            function syncDayDetails() {
                const detailsScript = container.querySelector('[data-calendar-day-details]');

                if (!detailsScript) {
                    dayDetails = {};
                    return;
                }

                try {
                    dayDetails = JSON.parse(detailsScript.textContent || '{}');
                } catch (error) {
                    dayDetails = {};
                }
            }

            function closeDetailModal() {
                if (!detailModal) {
                    return;
                }

                detailModal.classList.remove('is-open');
                detailModal.hidden = true;
            }

            function openDetailModal(detail) {
                if (!detailModal || !detail) {
                    return;
                }

                detailTitle.textContent = detail.date_label || 'Day details';
                detailStatus.textContent = detail.status_label || '';
                detailWeight.textContent = detail.weight_summary || 'No weight details.';
                detailHabitSummary.textContent = detail.habit_summary || 'No habit details.';
                detailHabits.innerHTML = '';

                if (Array.isArray(detail.habits) && detail.habits.length) {
                    detail.habits.forEach(function (habit) {
                        const item = document.createElement('div');
                        item.className = 'calendar-detail-item';
                        const head = document.createElement('div');
                        head.className = 'calendar-detail-item-head';

                        const name = document.createElement('strong');
                        name.textContent = habit.name || '';

                        const status = document.createElement('span');
                        status.textContent = habit.status || '';

                        const value = document.createElement('span');
                        value.textContent = habit.value || '';

                        head.appendChild(name);
                        head.appendChild(status);
                        item.appendChild(head);
                        item.appendChild(value);
                        detailHabits.appendChild(item);
                    });
                } else {
                    const empty = document.createElement('div');
                    empty.className = 'calendar-detail-empty';
                    empty.textContent = 'No habit entries for this date.';
                    detailHabits.appendChild(empty);
                }

                detailModal.hidden = false;
                detailModal.classList.add('is-open');
            }

            function loadMonth(monthKey) {
                if (!monthKey) {
                    return;
                }

                closeDetailModal();
                container.dataset.loading = 'true';

                if (feedback) {
                    feedback.textContent = 'Loading ' + monthKey + '...';
                    feedback.dataset.state = '';
                }

                fetch(@json(route('calendar.month')) + '?month=' + encodeURIComponent(monthKey), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
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
                        container.innerHTML = payload.html || '';
                        syncDayDetails();

                        if (feedback) {
                            feedback.textContent = '';
                            feedback.dataset.state = '';
                        }

                        if (window.history && window.history.replaceState) {
                            window.history.replaceState({}, '', @json(route('calendar.index')) + '?month=' + encodeURIComponent(payload.month));
                        }
                    })
                    .catch(function () {
                        if (feedback) {
                            feedback.textContent = 'Could not load that month right now.';
                            feedback.dataset.state = 'error';
                        }
                    })
                    .finally(function () {
                        container.dataset.loading = 'false';
                    });
            }

            container.addEventListener('click', function (event) {
                const button = event.target.closest('[data-calendar-month-button]');

                if (!button) {
                    const dayButton = event.target.closest('[data-calendar-day-button]');

                    if (!dayButton) {
                        return;
                    }

                    openDetailModal(dayDetails[dayButton.dataset.date] || null);

                    return;
                }

                loadMonth(button.dataset.month);
            });

            if (detailModal) {
                detailModal.addEventListener('click', function (event) {
                    if (event.target === detailModal || event.target.hasAttribute('data-close-calendar-detail')) {
                        closeDetailModal();
                    }
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && detailModal.classList.contains('is-open')) {
                        closeDetailModal();
                    }
                });
            }

            syncDayDetails();
        })();
    </script>
@endpush
