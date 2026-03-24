@extends('layouts.app')

@section('page_eyebrow', 'Daily Focus')
@section('page_title', 'Today')
@section('page_intro', 'Only the habits scheduled for '.$today->format('l').', '.$today->format('F j').' show up here. Log today\'s progress and the app will mark a habit complete once the goal is reached.')

@push('page_styles')
    <style>
        .today-overview {
            align-items: center;
        }

        .today-overview-stats {
            margin-top: 0;
            justify-content: flex-end;
        }

        .today-list-card {
            padding: 10px;
        }

        .weight-card {
            display: grid;
            gap: 18px;
        }

        .weight-head {
            align-items: center;
        }

        .weight-metrics {
            display: grid;
            gap: 12px;
        }

        .weight-metric-grid,
        .weight-progress-grid {
            display: grid;
            gap: 12px;
        }

        .weight-insights-grid {
            display: grid;
            gap: 12px;
        }

        .weight-progress-card {
            padding: 16px;
            border-radius: 20px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.66);
            display: grid;
            gap: 12px;
        }

        .weight-progress-copy {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .weight-progress-copy strong {
            color: var(--ink);
        }

        .weight-progress-status,
        .weight-summary,
        .weight-feedback {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
        }

        .weight-chart-card {
            display: grid;
            gap: 14px;
            padding: 18px;
            border-radius: 20px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.68);
        }

        .weight-chart-shell {
            position: relative;
            width: 100%;
            min-height: 320px;
        }

        .weight-chart {
            display: block;
            width: 100%;
            height: 320px;
        }

        .weight-chart-legend,
        .weight-chart-foot {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            color: var(--muted);
            font-size: 0.9rem;
        }

        .weight-chart-legend span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .weight-chart-legend i {
            width: 24px;
            height: 3px;
            display: inline-block;
            border-radius: 999px;
        }

        .legend-actual-line {
            background: var(--accent);
        }

        .legend-projected-line {
            background: repeating-linear-gradient(
                90deg,
                rgba(184, 128, 31, 0.9) 0 10px,
                transparent 10px 16px
            );
        }

        .weight-gauge-card {
            display: grid;
            gap: 14px;
            padding: 18px;
            border-radius: 20px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.68);
            align-content: start;
        }

        .weight-gauge-shell {
            position: relative;
            width: 100%;
            max-width: 320px;
            margin: 0 auto;
            aspect-ratio: 2 / 1.2;
        }

        .weight-gauge-arc {
            position: absolute;
            inset: 0;
            border-radius: 100% 100% 0 0 / 100% 100% 0 0;
            background:
                conic-gradient(
                    from 270deg at 50% 100%,
                    #2da20d 0deg 22.5deg,
                    #efe59a 22.5deg 45deg,
                    #ffd34d 45deg 67.5deg,
                    #f2b233 67.5deg 90deg,
                    #ff9f1c 90deg 112.5deg,
                    #f26a1b 112.5deg 135deg,
                    #ef2f22 135deg 180deg
                );
            overflow: hidden;
        }

        .weight-gauge-arc::after {
            content: "";
            position: absolute;
            inset: 28% 16% 0;
            border-radius: inherit;
            background: var(--surface-strong);
        }

        .weight-gauge-needle {
            position: absolute;
            left: 50%;
            bottom: 0;
            width: 4px;
            height: 62%;
            border-radius: 999px;
            background: var(--ink);
            transform-origin: center bottom;
            transform: translateX(-50%) rotate(-90deg);
            box-shadow: 0 4px 12px rgba(27, 37, 31, 0.18);
            z-index: 2;
        }

        .weight-gauge-needle::after {
            content: "";
            position: absolute;
            left: 50%;
            bottom: -8px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: var(--ink);
            transform: translateX(-50%);
        }

        .weight-gauge-copy {
            display: grid;
            gap: 8px;
            justify-items: center;
            text-align: center;
        }

        .weight-gauge-scale {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: var(--muted);
            font-size: 0.88rem;
        }

        .weight-feedback[data-state="success"] {
            color: var(--success);
        }

        .weight-feedback[data-state="error"] {
            color: var(--danger);
        }

        .weight-note {
            margin: 0;
            color: var(--muted);
        }

        .pill.danger {
            background: rgba(255, 244, 241, 0.92);
            color: var(--danger);
            border-color: rgba(156, 66, 52, 0.16);
        }

        .pill.soft-warning {
            background: rgba(248, 244, 212, 0.94);
            color: #8d7420;
            border-color: rgba(197, 181, 112, 0.28);
        }

        .pill.warm-warning {
            background: linear-gradient(135deg, rgba(255, 231, 161, 0.94), rgba(255, 205, 132, 0.94));
            color: #b06e16;
            border-color: rgba(232, 156, 54, 0.24);
        }

        .pill.amber {
            background: rgba(255, 228, 194, 0.96);
            color: #c96a12;
            border-color: rgba(236, 137, 35, 0.24);
        }

        .pill.amber-danger {
            background: linear-gradient(135deg, rgba(255, 205, 132, 0.92), rgba(255, 230, 214, 0.96));
            color: #d1571f;
            border-color: rgba(220, 102, 29, 0.24);
        }

        .today-list {
            display: grid;
            gap: 10px;
        }

        .today-goals-card {
            padding: 20px;
        }

        .today-goals-list {
            display: grid;
            gap: 10px;
        }

        .goal-milestone-row {
            display: grid;
            padding: 10px;
            border-radius: 20px;
            border: 1px solid var(--line);
            background: rgba(250, 242, 219, 0.88);
            cursor: pointer;
            transition: transform 160ms ease, border-color 160ms ease, background 160ms ease;
        }

        .goal-milestone-row:hover,
        .goal-milestone-row:focus-visible {
            transform: translateY(-1px);
            border-color: rgba(22, 89, 70, 0.24);
            background: rgba(255, 248, 233, 0.94);
            outline: none;
        }

        .goal-milestone-row.is-pending {
            background: rgba(250, 242, 219, 0.9);
            border-color: rgba(184, 128, 31, 0.16);
        }

        .goal-milestone-row.is-complete {
            background: rgba(221, 244, 230, 0.9);
            border-color: rgba(29, 108, 68, 0.18);
        }

        .goal-milestone-row.is-saving {
            opacity: 0.7;
        }

        .goal-milestone-copy {
            display: grid;
        }

        .goal-milestone-name {
            font-size: 1.2rem;
            margin: 0;
        }

        .goal-milestone-meta {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
        }

        .goal-milestone-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }

        .goal-milestone-feedback {
            min-height: 1.2rem;
            margin: 0;
            color: var(--muted);
            font-size: 0.88rem;
        }

        .goal-milestone-feedback[data-state="success"] {
            color: var(--success);
        }

        .goal-milestone-feedback[data-state="error"] {
            color: var(--danger);
        }

        .habit-row {
            display: grid;
            gap: 14px;
            padding: 16px;
            border-radius: 20px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.62);
            cursor: pointer;
            transition: transform 160ms ease, border-color 160ms ease, background 160ms ease;
        }

        .habit-row:hover,
        .habit-row:focus-visible {
            transform: translateY(-1px);
            border-color: rgba(22, 89, 70, 0.24);
            background: rgba(255, 255, 255, 0.78);
            outline: none;
        }

        .habit-row.is-saving {
            opacity: 0.72;
        }

        .habit-main {
            display: grid;
            gap: 10px;
        }

        .habit-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
        }

        .habit-name {
            font-size: 1.28rem;
            margin: 0;
        }

        .habit-history {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        .habit-history-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .history-day {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.82);
            color: var(--muted);
            font-size: 0.88rem;
            font-weight: 700;
            line-height: 1;
        }

        .history-day.is-today {
            box-shadow: inset 0 0 0 2px rgba(22, 89, 70, 0.12);
        }

        .history-day.is-complete {
            background: rgba(221, 244, 230, 0.92);
            border-color: rgba(29, 108, 68, 0.18);
            color: var(--success);
        }

        .history-day.is-partial {
            background: rgba(250, 242, 219, 0.92);
            border-color: rgba(184, 128, 31, 0.16);
            color: var(--warning);
        }

        .history-day.is-missed {
            background: rgba(255, 244, 241, 0.92);
            border-color: rgba(156, 66, 52, 0.14);
            color: var(--danger);
        }

        .history-day.is-off {
            background: rgba(246, 242, 234, 0.92);
            color: #98a39d;
        }

        .habit-summary {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
            font-size: 0.96rem;
        }

        .habit-progress-copy {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .habit-progress-copy strong {
            color: var(--ink);
        }

        .habit-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            flex-wrap: wrap;
        }

        .habit-feedback {
            min-height: 1.2rem;
            margin: 0;
            font-size: 0.88rem;
            color: var(--muted);
        }

        .habit-feedback[data-state="success"] {
            color: var(--success);
        }

        .habit-feedback[data-state="error"] {
            color: var(--danger);
        }

        .today-modal-backdrop {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
            background: rgba(17, 25, 21, 0.58);
            z-index: 70;
        }

        .today-modal-backdrop.is-open {
            display: flex;
        }

        .today-modal {
            width: min(100%, 440px);
            padding: 22px;
            border-radius: 24px;
            border: 1px solid var(--line);
            background: rgba(255, 252, 245, 0.98);
            box-shadow: 0 28px 80px rgba(18, 27, 23, 0.24);
        }

        .today-modal-head {
            display: grid;
            gap: 8px;
            margin-bottom: 18px;
        }

        .today-modal-kicker {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--accent);
        }

        .today-modal-title {
            font-size: 1.8rem;
        }

        .today-modal-copy {
            margin: 0;
            color: var(--muted);
            line-height: 1.7;
        }

        .today-modal-form {
            display: grid;
            gap: 14px;
        }

        .today-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .today-modal-feedback {
            min-height: 1.2rem;
            margin: 0;
            font-size: 0.9rem;
            color: var(--muted);
        }

        .today-modal-feedback[data-state="error"] {
            color: var(--danger);
        }

        .month-button {
            appearance: none;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.76);
            color: var(--ink);
            border-radius: 999px;
            padding: 9px 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .calendar-modal {
            width: min(100%, 860px);
        }

        .calendar-head {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 18px;
        }

        .calendar-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 8px;
        }

        .calendar-weekday {
            padding: 6px 4px;
            text-align: center;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
        }

        .calendar-day {
            min-height: 84px;
            padding: 10px 8px;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.8);
            display: grid;
            align-content: space-between;
            gap: 8px;
        }

        .calendar-day.is-editable {
            cursor: pointer;
            transition: transform 160ms ease, border-color 160ms ease;
        }

        .calendar-day.is-editable:hover,
        .calendar-day.is-editable:focus-visible {
            transform: translateY(-1px);
            border-color: rgba(22, 89, 70, 0.24);
            outline: none;
        }

        .calendar-day.is-complete {
            background: rgba(221, 244, 230, 0.92);
            border-color: rgba(29, 108, 68, 0.18);
        }

        .calendar-day.is-partial {
            background: rgba(250, 242, 219, 0.92);
            border-color: rgba(184, 128, 31, 0.16);
        }

        .calendar-day.is-missed {
            background: rgba(255, 244, 241, 0.92);
            border-color: rgba(156, 66, 52, 0.14);
        }

        .calendar-day.is-off {
            background: rgba(246, 242, 234, 0.92);
            color: #98a39d;
        }

        .calendar-day.is-today {
            box-shadow: inset 0 0 0 2px rgba(22, 89, 70, 0.12);
        }

        .calendar-day.blank {
            min-height: 0;
            padding: 0;
            border: 0;
            background: transparent;
        }

        .calendar-date {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--ink);
        }

        .calendar-value {
            font-size: 0.84rem;
            line-height: 1.4;
            color: inherit;
            word-break: break-word;
        }

        .calendar-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 16px;
        }

        .calendar-legend span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.88rem;
            color: var(--muted);
        }

        .calendar-legend i {
            width: 12px;
            height: 12px;
            border-radius: 999px;
            display: inline-block;
            border: 1px solid var(--line);
        }

        .legend-complete { background: rgba(221, 244, 230, 0.92); }
        .legend-partial { background: rgba(250, 242, 219, 0.92); }
        .legend-missed { background: rgba(255, 244, 241, 0.92); }
        .legend-off { background: rgba(246, 242, 234, 0.92); }

        @media (min-width: 900px) {
            .weight-metric-grid,
            .weight-progress-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .weight-insights-grid {
                grid-template-columns: minmax(0, 1.8fr) minmax(280px, 0.9fr);
                align-items: start;
            }

            .habit-row {
                grid-template-columns: minmax(0, 1fr);
            }

            .habit-actions {
                width: 100%;
                justify-content: flex-start;
            }
        }
    </style>
@endpush

@section('content')
    <section class="card">
        <div class="split-grid today-overview">
            <div class="stack">
                <h2>{{ $today->format('l') }}</h2>
                <p>{{ $today->format('F j, Y') }} in {{ config('app.timezone') }}.</p>
            </div>

            @if (!is_null($timelineCounters['week_count']) || !is_null($timelineCounters['day_count']))
                <div class="stats-grid today-overview-stats">
                @if (!is_null($timelineCounters['week_count']))
                    <div class="stat">
                        <span class="stat-label">Current week</span>
                        <span class="stat-value">Week {{ $timelineCounters['week_count'] }}</span>
                    </div>
                @endif

                @if (!is_null($timelineCounters['day_count']))
                    <div class="stat">
                        <span class="stat-label">Days until deadline</span>
                        <span class="stat-value">{{ $timelineCounters['day_count'] }}</span>
                    </div>
                @endif
                </div>
            @endif
        </div>
    </section>

    <section class="card weight-card" data-weight-section>
        <div class="split-grid weight-head">
            <div class="stack">
                <h2>Weight loss</h2>
            </div>

            <div class="button-row">
                <button
                    type="button"
                    class="button"
                    data-open-weight-modal
                    data-action="{{ route('today.weight.store') }}"
                    data-weight-value="{{ $weightSection['today_logged_weight'] ?? '' }}"
                    data-submit-label="{{ $weightSection['today_button_label'] }}"
                >
                    {{ $weightSection['today_button_label'] }}
                </button>
            </div>
        </div>

        <div class="weight-metrics">
            <div class="weight-metric-grid">
                <div class="stat">
                    <span class="stat-label">Today&apos;s weight</span>
                    <span class="stat-value" data-weight-today>{{ $weightSection['today_logged_weight'] ? $weightSection['today_logged_weight'].' kg' : 'No log yet' }}</span>
                </div>
                <div class="stat">
                    <span class="stat-label">7-day average</span>
                    <span class="stat-value" data-weight-average>{{ $weightSection['rolling_average_weight'] ? $weightSection['rolling_average_weight'].' kg' : 'Waiting' }}</span>
                </div>
            </div>

            <p class="weight-note" data-weight-metric-date>
                @if (!$weightSection['metric_date_label'])
                    The rolling average will appear after your first weight log.
                @endif
            </p>
        </div>

        <div class="weight-progress-grid">
            <section class="weight-progress-card">
                <div class="stack">
                    <h3>Overall progress</h3>
                    @if ($weightSection['overall'])
                        <p>{{ $weightSection['overall']['label'] }}</p>
                    @else
                        <p>Set up at least one monthly goal from the Weight Loss page to start this bar.</p>
                    @endif
                </div>

                @if ($weightSection['overall'])
                    <div class="weight-progress-copy">
                        <span>{{ $weightSection['overall']['starting_weight'] }} kg start</span>
                        <span><strong data-overall-current>{{ $weightSection['overall']['current_weight'] ? $weightSection['overall']['current_weight'].' kg avg' : 'No average yet' }}</strong></span>
                        <span>{{ $weightSection['overall']['goal_weight'] }} kg goal</span>
                    </div>

                    <p class="weight-progress-status"><strong data-overall-percent>{{ number_format((float) $weightSection['overall']['percent'], 2) }}%</strong> complete</p>

                    <div class="progress-track" aria-hidden="true">
                        <div class="progress-bar" data-overall-progress style="width: {{ $weightSection['overall']['percent'] }}%;"></div>
                    </div>

                    <p class="weight-progress-status" data-overall-status>{{ $weightSection['overall']['status'] }}</p>
                @endif
            </section>

            <section class="weight-progress-card">
                <div class="stack">
                    <h3>Monthly progress</h3>
                    @if ($weightSection['monthly'])
                        <p>{{ $weightSection['monthly']['label'] }}</p>
                    @else
                        <p>Add a goal for {{ $monthLabel }} on the Weight Loss page to start this month&apos;s bar.</p>
                    @endif
                </div>

                @if ($weightSection['monthly'])
                    <div class="weight-progress-copy">
                        <span>{{ $weightSection['monthly']['starting_weight'] }} kg start</span>
                        <span><strong data-monthly-current>{{ $weightSection['monthly']['current_weight'] ? $weightSection['monthly']['current_weight'].' kg avg' : 'No average yet' }}</strong></span>
                        <span>{{ $weightSection['monthly']['goal_weight'] }} kg goal</span>
                    </div>

                    <p class="weight-progress-status"><strong data-monthly-percent>{{ number_format((float) $weightSection['monthly']['percent'], 2) }}%</strong> complete</p>

                    <div class="progress-track" aria-hidden="true">
                        <div class="progress-bar" data-monthly-progress style="width: {{ $weightSection['monthly']['percent'] }}%;"></div>
                    </div>

                    <p class="weight-progress-status" data-monthly-status>{{ $weightSection['monthly']['status'] }}</p>
                @endif
            </section>
        </div>

        <div class="weight-insights-grid">
            <div data-weight-chart>
                @if ($weightSection['chart'])
                    <section class="weight-chart-card" data-weight-chart-card>
                        <div class="split-grid">
                            <div class="stack">
                                <h3>{{ $weightSection['chart']['title'] }}</h3>
                            </div>

                            <span
                                class="pill {{ $weightSection['chart']['status_class'] }}"
                                data-weight-chart-status
                                title="{{ $weightSection['chart']['status_detail'] }}"
                            >
                                {{ $weightSection['chart']['status_label'] }}
                            </span>
                        </div>

                        <div class="weight-chart-legend" aria-hidden="true">
                            <span><i class="legend-actual-line"></i>{{ $weightSection['chart']['legend_actual'] }}</span>
                            <span><i class="legend-projected-line"></i>{{ $weightSection['chart']['legend_projected'] }}</span>
                        </div>

                        <div class="weight-chart-shell">
                            <canvas class="weight-chart" data-weight-chart-canvas aria-label="{{ $weightSection['chart']['subtitle'] }} weight trend chart"></canvas>
                        </div>

                        <div class="weight-chart-foot">
                            <span>Day of month</span>
                            <span data-weight-chart-foot>{{ $weightSection['chart']['projected_goal_weight'] }} kg projected target by month end</span>
                        </div>
                    </section>
                @else
                    <section class="weight-chart-card">
                        <div class="stack">
                            <h3>Current month trend</h3>
                            <p>Add a goal for {{ $monthLabel }} on the Weight Loss page to see the rolling-average trend and the projected line.</p>
                        </div>
                    </section>
                @endif
            </div>

            <section class="weight-gauge-card" data-weight-gauge>
                <div class="split-grid">
                    <div class="stack">
                        <h3>Average gauge</h3>
                    </div>

                    <span class="pill {{ $weightSection['gauge']['zone_class'] }}" data-weight-gauge-zone>
                        {{ $weightSection['gauge']['zone_label'] }}
                    </span>
                </div>

                <div class="weight-gauge-shell" aria-hidden="true">
                    <div class="weight-gauge-arc"></div>
                    <div
                        class="weight-gauge-needle"
                        data-weight-gauge-needle
                        style="transform: translateX(-50%) rotate({{ $weightSection['gauge']['angle'] }}deg);"
                    ></div>
                </div>

                <div class="weight-gauge-copy">
                    <strong data-weight-gauge-value>{{ $weightSection['gauge']['value_label'] }}</strong>
                    <div class="weight-gauge-scale">
                        <span>70 kg</span>
                        <span>110 kg</span>
                    </div>
                </div>
            </section>
        </div>

        <p class="weight-feedback" data-weight-feedback aria-live="polite"></p>
    </section>

    @if ($habits->isEmpty())
        <section class="card empty-state">
            <h2>Nothing is scheduled for today.</h2>
            <p>Create a recurring habit from the Habits page and pick the weekdays you want it to appear.</p>
        </section>
    @else
        <section class="card today-list-card">
            <div class="today-list">
                @foreach ($habits as $habit)
                    @php
                        $logsByDate = $habit->logs->keyBy(fn ($log) => $log->logged_for->toDateString());
                        $todayLog = $logsByDate->get($today->toDateString());
                        $currentValue = $todayLog ? (float) $todayLog->value : 0;
                        $goalValue = (float) $habit->daily_goal;
                        $progress = $goalValue > 0 ? min(100, ($currentValue / $goalValue) * 100) : 0;
                        $remaining = max(0, $goalValue - $currentValue);
                        $inputValue = old('habit_id') == $habit->id ? old('value') : ($todayLog ? \App\Models\Todo::formatAmount($todayLog->value) : '');
                        $statusLabel = $todayLog && $todayLog->completed ? 'Completed' : ($todayLog ? 'In progress' : 'Waiting for log');
                        $statusClass = $todayLog && $todayLog->completed ? 'success' : ($todayLog ? 'warning' : 'neutral');
                        $summary = $todayLog && $todayLog->completed
                            ? 'You are done for today.'
                            : ($todayLog
                                ? \App\Models\Todo::formatAmount($remaining).' '.$habit->unit.' left to reach the daily goal.'
                                : 'Nothing logged yet for this habit today.');
                        $remainingLabel = $todayLog && $todayLog->completed
                            ? 'Goal reached'
                            : \App\Models\Todo::formatAmount($remaining).' '.$habit->unit.' left';
                    @endphp

                    <article
                        class="habit-row"
                        data-habit-row
                        data-open-log-modal
                        data-action="{{ route('today.logs.store', $habit) }}"
                        data-habit-id="{{ $habit->id }}"
                        data-habit-name="{{ $habit->name }}"
                        data-habit-unit="{{ $habit->unit }}"
                        data-habit-value="{{ $inputValue }}"
                        data-logged-for="{{ $today->toDateString() }}"
                        data-log-date-label="{{ $today->format('D, M j') }}"
                        data-submit-label="{{ $todayLog ? 'Save changes' : 'Save' }}"
                        tabindex="0"
                        role="button"
                        aria-label="Log today's progress for {{ $habit->name }}"
                    >
                        <div class="habit-main">
                            <div class="habit-heading">
                                <h2 class="habit-name">{{ $habit->name }}</h2>
                                <span class="pill {{ $statusClass }}" data-status-pill>{{ $statusLabel }}</span>
                            </div>

                            <div class="habit-history">
                                <div class="habit-history-strip" aria-label="Completion status over the last 7 days">
                                    @foreach ($lastSevenDays as $date)
                                        @php
                                            $dateKey = $date->toDateString();
                                            $historyLog = $logsByDate->get($dateKey);
                                            $isScheduled = $habit->isScheduledFor(strtolower($date->englishDayOfWeek));
                                            $historyClass = 'is-off';

                                            if ($isScheduled && $historyLog?->completed) {
                                                $historyClass = 'is-complete';
                                            } elseif ($isScheduled && $historyLog) {
                                                $historyClass = 'is-partial';
                                            } elseif ($isScheduled) {
                                                $historyClass = 'is-missed';
                                            }
                                        @endphp

                                        <span
                                            class="history-day {{ $historyClass }} {{ $date->isSameDay($today) ? 'is-today' : '' }}"
                                            data-day-status
                                            data-date="{{ $dateKey }}"
                                            title="{{ $date->format('D, M j') }}"
                                        >
                                            {{ $date->format('j') }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <p class="habit-summary" data-summary>{{ $summary }}</p>

                            <div class="habit-progress-copy">
                                <span><strong data-today-value>{{ $todayLog ? \App\Models\Todo::formatAmount($todayLog->value) : '0' }}</strong> / {{ \App\Models\Todo::formatAmount($habit->daily_goal) }} {{ $habit->unit }}</span>
                                <span data-remaining>{{ $remainingLabel }}</span>
                            </div>

                            <div class="progress-track" aria-hidden="true">
                                <div class="progress-bar" data-progress style="width: {{ $progress }}%;"></div>
                            </div>
                        </div>

                        <div class="habit-actions">
                            <button
                                type="button"
                                class="month-button"
                                data-open-calendar-modal
                                aria-controls="calendar-modal-{{ $habit->id }}"
                                aria-label="View {{ $habit->name }} calendar for {{ $monthLabel }}"
                            >
                                View month
                            </button>
                            <p class="habit-feedback" data-feedback aria-live="polite"></p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        @foreach ($habits as $habit)
            @php
                $calendarLogsByDate = $habit->logs->keyBy(fn ($log) => $log->logged_for->toDateString());
            @endphp

            <div class="today-modal-backdrop" data-calendar-modal="calendar-modal-{{ $habit->id }}" hidden>
                <div class="today-modal calendar-modal" role="dialog" aria-modal="true" aria-labelledby="calendar-title-{{ $habit->id }}">
                    <div class="calendar-head">
                        <div class="today-modal-head" style="margin-bottom: 0;">
                            <span class="today-modal-kicker">Month view</span>
                            <h2 class="today-modal-title" id="calendar-title-{{ $habit->id }}">{{ $habit->name }}</h2>
                            <p class="today-modal-copy">{{ $monthLabel }} completion and logged values.</p>
                        </div>

                        <div class="calendar-meta">
                            <span class="chip">Goal: {{ \App\Models\Todo::formatAmount($habit->daily_goal) }} {{ $habit->unit }}</span>
                            <button type="button" class="button-secondary" data-close-calendar-modal>Close</button>
                        </div>
                    </div>

                    <div class="calendar-grid" aria-label="{{ $monthLabel }} calendar">
                        @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $weekday)
                            <div class="calendar-weekday">{{ $weekday }}</div>
                        @endforeach

                        @for ($blank = 0; $blank < $monthLeadingBlanks; $blank++)
                            <div class="calendar-day blank" aria-hidden="true"></div>
                        @endfor

                        @foreach ($monthDays as $date)
                            @php
                                $dateKey = $date->toDateString();
                                $historyLog = $calendarLogsByDate->get($dateKey);
                                $isScheduled = $habit->isScheduledFor(strtolower($date->englishDayOfWeek));
                                $calendarClass = 'is-off';
                                $valueLabel = $isScheduled ? 'No log' : 'Off day';

                                if ($isScheduled && $historyLog?->completed) {
                                    $calendarClass = 'is-complete';
                                    $valueLabel = \App\Models\Todo::formatAmount($historyLog->value).' '.$habit->unit;
                                } elseif ($isScheduled && $historyLog) {
                                    $calendarClass = 'is-partial';
                                    $valueLabel = \App\Models\Todo::formatAmount($historyLog->value).' '.$habit->unit;
                                } elseif ($isScheduled) {
                                    $calendarClass = 'is-missed';
                                }
                            @endphp

                            <div
                                class="calendar-day {{ $calendarClass }} {{ $date->isSameDay($today) ? 'is-today' : '' }} {{ $isScheduled ? 'is-editable' : '' }}"
                                data-calendar-day
                                data-habit-id="{{ $habit->id }}"
                                data-date="{{ $dateKey }}"
                                @if ($isScheduled)
                                    data-open-log-modal
                                    data-action="{{ route('today.logs.store', $habit) }}"
                                    data-habit-name="{{ $habit->name }}"
                                    data-habit-unit="{{ $habit->unit }}"
                                    data-habit-value="{{ $historyLog ? \App\Models\Todo::formatAmount($historyLog->value) : '' }}"
                                    data-logged-for="{{ $dateKey }}"
                                    data-log-date-label="{{ $date->format('D, M j') }}"
                                    data-submit-label="{{ $historyLog ? 'Save changes' : 'Save' }}"
                                    tabindex="0"
                                    role="button"
                                    aria-label="Log {{ $habit->name }} for {{ $date->format('D, M j') }}"
                                @endif
                            >
                                <span class="calendar-date">{{ $date->format('j') }}</span>
                                <span class="calendar-value">{{ $valueLabel }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="calendar-legend">
                        <span><i class="legend-complete"></i> Complete</span>
                        <span><i class="legend-partial"></i> Logged, below goal</span>
                        <span><i class="legend-missed"></i> Scheduled, no log</span>
                        <span><i class="legend-off"></i> Off day</span>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="today-modal-backdrop" data-log-modal hidden>
            <div class="today-modal" role="dialog" aria-modal="true" aria-labelledby="log-modal-title">
                <div class="today-modal-head">
                    <span class="today-modal-kicker">Daily log</span>
                    <h2 class="today-modal-title" id="log-modal-title">Log habit</h2>
                    <p class="today-modal-copy" data-log-modal-copy>How many did you do today?</p>
                </div>

                <form class="today-modal-form" data-log-modal-form>
                    <input type="hidden" name="habit_id" value="">
                    <input type="hidden" name="logged_for" value="">

                    <div class="field">
                        <label class="field-label" for="modal-habit-value">Today&apos;s total</label>
                        <input
                            id="modal-habit-value"
                            class="number-input"
                            type="number"
                            name="value"
                            min="0"
                            step="0.01"
                            inputmode="decimal"
                            placeholder="Enter today&apos;s total"
                        >
                    </div>

                    <p class="today-modal-feedback" data-log-modal-feedback aria-live="polite"></p>

                    <div class="today-modal-actions">
                        <button type="button" class="button-secondary" data-close-log-modal>Cancel</button>
                        <button type="submit" class="button" data-log-modal-submit>Save</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <section class="card today-goals-card">
        <div class="split-grid today-overview" style="margin-bottom:10px">
            <div class="stack">
                <h2>Goals</h2>
            </div>

            <div class="chip-row">
                <span class="chip">{{ $goalMilestones->count() }} this month</span>
            </div>
        </div>

        @if ($goalMilestones->isEmpty())
            <section class="empty-state" style="padding-bottom: 12px;">
                <h2>No milestones for this month.</h2>
                <p>Add milestones with an estimated completion month of {{ $monthLabel }} from the Goals page.</p>
            </section>
        @else
            <div class="today-goals-list">
                @foreach ($goalMilestones as $milestone)
                    <article
                        class="goal-milestone-row {{ $milestone->completed ? 'is-complete' : 'is-pending' }}"
                        data-goal-milestone-row
                        data-action="{{ route('today.milestones.toggle', $milestone) }}"
                        data-milestone-id="{{ $milestone->id }}"
                        tabindex="0"
                        role="button"
                        aria-label="{{ $milestone->completed ? 'Mark incomplete' : 'Mark complete' }} for {{ $milestone->name }}"
                    >
                        <div class="goal-milestone-copy">
                            <div class="habit-heading">
                                <h3 class="goal-milestone-name">{{ $milestone->name }}</h3>
                                <span class="pill {{ $milestone->completed ? 'success' : 'warning' }}" data-goal-milestone-status>
                                    {{ $milestone->completed ? 'Completed' : 'In progress' }}
                                </span>
                            </div>
                            <p class="goal-milestone-meta">
                                <strong>{{ $milestone->goal->name }}</strong> for {{ $milestone->monthLabel() }}.
                            </p>
                        </div>

                        <div class="goal-milestone-actions">
                            <p class="goal-milestone-feedback" data-goal-milestone-feedback aria-live="polite"></p>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <div class="today-modal-backdrop" data-weight-modal hidden>
        <div class="today-modal" role="dialog" aria-modal="true" aria-labelledby="weight-modal-title">
            <div class="today-modal-head">
                <span class="today-modal-kicker">Weight log</span>
                <h2 class="today-modal-title" id="weight-modal-title">Log today&apos;s weight</h2>
                <p class="today-modal-copy">What do you weigh today? The app will refresh the 7-day rolling average right away.</p>
            </div>

            <form class="today-modal-form" data-weight-modal-form action="{{ route('today.weight.store') }}">
                <div class="field">
                    <label class="field-label" for="modal-weight-value">Current weight</label>
                    <input
                        id="modal-weight-value"
                        class="number-input"
                        type="number"
                        name="weight"
                        min="0"
                        step="0.01"
                        inputmode="decimal"
                        placeholder="Enter today&apos;s weight"
                    >
                </div>

                <p class="today-modal-feedback" data-weight-modal-feedback aria-live="polite"></p>

                <div class="today-modal-actions">
                    <button type="button" class="button-secondary" data-close-weight-modal>Cancel</button>
                    <button type="submit" class="button" data-weight-modal-submit>Save</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('page_scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const modal = document.querySelector('[data-log-modal]');

            if (!modal || !window.fetch || !token) {
                return;
            }

            const form = modal.querySelector('[data-log-modal-form]');
            const title = modal.querySelector('#log-modal-title');
            const copy = modal.querySelector('[data-log-modal-copy]');
            const feedback = modal.querySelector('[data-log-modal-feedback]');
            const submitButton = modal.querySelector('[data-log-modal-submit]');
            const valueInput = form.querySelector('input[name="value"]');
            const habitIdInput = form.querySelector('input[name="habit_id"]');
            const loggedForInput = form.querySelector('input[name="logged_for"]');
            let activeRow = null;
            let activeTrigger = null;

            function closeModal() {
                modal.classList.remove('is-open');
                modal.hidden = true;
                form.reset();
                form.action = '';
                feedback.textContent = '';
                feedback.dataset.state = '';
                activeRow = null;
                activeTrigger = null;
            }

            function openModal(trigger) {
                activeTrigger = trigger;
                activeRow = trigger.closest('[data-habit-row]') || trigger;
                form.action = trigger.dataset.action;
                habitIdInput.value = trigger.dataset.habitId || '';
                loggedForInput.value = trigger.dataset.loggedFor || '';
                valueInput.value = trigger.dataset.habitValue || '';
                submitButton.textContent = trigger.dataset.submitLabel || 'Save';
                title.textContent = trigger.dataset.habitName || 'Log habit';
                copy.textContent = 'How many ' + (trigger.dataset.habitUnit || 'units') + ' did you do on ' + (trigger.dataset.logDateLabel || 'this day') + '?';
                feedback.textContent = '';
                feedback.dataset.state = '';
                modal.hidden = false;
                modal.classList.add('is-open');
                window.setTimeout(function () {
                    valueInput.focus();
                    valueInput.select();
                }, 0);
            }

            function updateRow(payload) {
                if (!activeRow || !payload || !payload.habit) {
                    return;
                }

                const habit = payload.habit;
                const statusPill = activeRow.querySelector('[data-status-pill]');
                const summary = activeRow.querySelector('[data-summary]');
                const todayValue = activeRow.querySelector('[data-today-value]');
                const remaining = activeRow.querySelector('[data-remaining]');
                const progressBar = activeRow.querySelector('[data-progress]');
                const rowFeedback = activeRow.querySelector('[data-feedback]');
                const dayStatus = activeRow.querySelector('[data-day-status][data-date="' + habit.logged_for + '"]');
                const calendarDay = document.querySelector('[data-calendar-day][data-habit-id="' + habit.id + '"][data-date="' + habit.logged_for + '"]');

                if (dayStatus) {
                    dayStatus.classList.remove('is-complete', 'is-partial', 'is-missed', 'is-off');
                    dayStatus.classList.add(habit.day_state === 'complete' ? 'is-complete' : 'is-partial');
                }

                if (calendarDay) {
                    calendarDay.classList.remove('is-complete', 'is-partial', 'is-missed', 'is-off');
                    calendarDay.classList.add(habit.day_state === 'complete' ? 'is-complete' : 'is-partial');

                    const calendarValue = calendarDay.querySelector('.calendar-value');

                    if (calendarValue) {
                        calendarValue.textContent = habit.logged_value + ' ' + habit.unit;
                    }
                }

                if (habit.is_today) {
                    if (todayValue) {
                        todayValue.textContent = habit.logged_value;
                    }

                    if (remaining) {
                        remaining.textContent = habit.remaining_label;
                    }

                    if (summary) {
                        summary.textContent = habit.summary;
                    }

                    if (progressBar) {
                        progressBar.style.width = habit.progress_percent + '%';
                    }

                    activeRow.dataset.habitValue = habit.logged_value;
                    activeRow.dataset.submitLabel = 'Save changes';
                }

                if (statusPill) {
                    if (habit.is_today && habit.status_label) {
                        statusPill.textContent = habit.status_label;
                        statusPill.classList.remove('success', 'warning', 'neutral');
                        statusPill.classList.add(habit.completed ? 'success' : 'warning');
                    }
                }

                if (activeTrigger) {
                    activeTrigger.dataset.submitLabel = 'Save changes';
                    activeTrigger.dataset.habitValue = habit.logged_value;
                }

                if (rowFeedback) {
                    rowFeedback.textContent = payload.message || 'Saved.';
                    rowFeedback.dataset.state = 'success';
                }
            }

            document.querySelectorAll('[data-open-log-modal]').forEach(function (trigger) {
                trigger.addEventListener('click', function () {
                    openModal(trigger);
                });

                trigger.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        openModal(trigger);
                    }
                });
            });

            document.querySelectorAll('[data-open-calendar-modal]').forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.stopPropagation();
                    const targetId = button.getAttribute('aria-controls');
                    const calendarModal = document.querySelector('[data-calendar-modal="' + targetId + '"]');

                    if (!calendarModal) {
                        return;
                    }

                    calendarModal.hidden = false;
                    calendarModal.classList.add('is-open');
                });

                button.addEventListener('keydown', function (event) {
                    event.stopPropagation();
                });
            });

            document.querySelectorAll('[data-calendar-modal]').forEach(function (calendarModal) {
                calendarModal.addEventListener('click', function (event) {
                    if (event.target === calendarModal || event.target.hasAttribute('data-close-calendar-modal')) {
                        calendarModal.classList.remove('is-open');
                        calendarModal.hidden = true;
                    }
                });
            });

            modal.addEventListener('click', function (event) {
                if (event.target === modal || event.target.hasAttribute('data-close-log-modal')) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                    closeModal();
                }

                if (event.key === 'Escape') {
                    document.querySelectorAll('[data-calendar-modal].is-open').forEach(function (calendarModal) {
                        calendarModal.classList.remove('is-open');
                        calendarModal.hidden = true;
                    });
                }
            });

            form.addEventListener('submit', function (event) {
                event.preventDefault();

                const formData = new FormData(form);
                const originalLabel = submitButton.textContent;
                const rowRef = activeRow;

                if (rowRef) {
                    rowRef.classList.add('is-saving');
                }

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
                        updateRow(payload);
                        closeModal();
                    })
                    .catch(function (payload) {
                        let message = 'Could not save this habit right now.';

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
                        submitButton.textContent = originalLabel;
                    })
                    .finally(function () {
                        submitButton.disabled = false;

                        if (rowRef) {
                            rowRef.classList.remove('is-saving');
                        }
                });
            });
        })();

        (function () {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const modal = document.querySelector('[data-weight-modal]');
            const section = document.querySelector('[data-weight-section]');
            const chartLibraryAvailable = typeof window.Chart !== 'undefined';

            if (!modal || !section || !window.fetch || !token) {
                return;
            }

            const form = modal.querySelector('[data-weight-modal-form]');
            const valueInput = form.querySelector('input[name="weight"]');
            const feedback = modal.querySelector('[data-weight-modal-feedback]');
            const submitButton = modal.querySelector('[data-weight-modal-submit]');
            const rowFeedback = section.querySelector('[data-weight-feedback]');
            const openButton = section.querySelector('[data-open-weight-modal]');
            const todayWeight = section.querySelector('[data-weight-today]');
            const averageWeight = section.querySelector('[data-weight-average]');
            const summary = section.querySelector('[data-weight-summary]');
            const metricDate = section.querySelector('[data-weight-metric-date]');
            const overallCurrent = section.querySelector('[data-overall-current]');
            const overallPercent = section.querySelector('[data-overall-percent]');
            const overallProgress = section.querySelector('[data-overall-progress]');
            const overallStatus = section.querySelector('[data-overall-status]');
            const monthlyCurrent = section.querySelector('[data-monthly-current]');
            const monthlyPercent = section.querySelector('[data-monthly-percent]');
            const monthlyProgress = section.querySelector('[data-monthly-progress]');
            const monthlyStatus = section.querySelector('[data-monthly-status]');
            const chartContainer = section.querySelector('[data-weight-chart]');
            const chartCanvas = section.querySelector('[data-weight-chart-canvas]');
            const chartFoot = section.querySelector('[data-weight-chart-foot]');
            const chartStatus = section.querySelector('[data-weight-chart-status]');
            const gaugeZone = section.querySelector('[data-weight-gauge-zone]');
            const gaugeNeedle = section.querySelector('[data-weight-gauge-needle]');
            const gaugeValue = section.querySelector('[data-weight-gauge-value]');
            const initialChartData = @json($weightSection['chart']);
            let weightTrendChart = null;

            function formatPercent(value) {
                const numericValue = Number(value || 0);

                return numericValue.toFixed(2) + '%';
            }

            function renderWeightChart(chartData) {
                if (!chartLibraryAvailable || !chartCanvas || !chartData) {
                    return;
                }

                if (weightTrendChart) {
                    weightTrendChart.destroy();
                }

                weightTrendChart = new window.Chart(chartCanvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: [
                            {
                                label: chartData.legendProjected,
                                data: chartData.projectedValues,
                                borderColor: 'rgba(184, 128, 31, 0.9)',
                                borderDash: [8, 7],
                                borderWidth: 3,
                                pointRadius: 0,
                                pointHoverRadius: 0,
                                tension: 0,
                            },
                            {
                                label: chartData.legendActual,
                                data: chartData.actualValues,
                                borderColor: '#165946',
                                backgroundColor: '#165946',
                                pointBackgroundColor: '#fffaf0',
                                pointBorderColor: '#165946',
                                pointBorderWidth: 3,
                                pointRadius: 4,
                                pointHoverRadius: 5,
                                spanGaps: false,
                                tension: 0.28,
                                borderWidth: 4,
                            }
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                display: false,
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        if (context.raw === null || typeof context.raw === 'undefined') {
                                            return null;
                                        }

                                        return context.dataset.label + ': ' + Number(context.raw).toFixed(2) + ' kg';
                                    },
                                },
                            },
                        },
                        scales: {
                            x: {
                                grid: {
                                    color: 'rgba(27, 37, 31, 0.08)',
                                },
                                ticks: {
                                    color: '#5e6a63',
                                    maxTicksLimit: 6,
                                },
                                title: {
                                    display: true,
                                    text: 'Day',
                                    color: '#5e6a63',
                                },
                            },
                            y: {
                                min: chartData.yMin,
                                max: chartData.yMax,
                                grid: {
                                    color: 'rgba(27, 37, 31, 0.1)',
                                },
                                ticks: {
                                    color: '#5e6a63',
                                    callback: function (value) {
                                        return Number(value).toFixed(1) + ' kg';
                                    },
                                },
                                title: {
                                    display: true,
                                    text: 'Weight',
                                    color: '#5e6a63',
                                },
                            },
                        },
                    },
                });

                if (chartFoot) {
                    chartFoot.textContent = chartData.projectedGoalWeight + ' kg projected target by month end';
                }

                if (chartStatus) {
                    chartStatus.textContent = chartData.statusLabel;
                    chartStatus.classList.remove('success', 'warning', 'neutral', 'danger');
                    chartStatus.classList.add(chartData.statusClass || 'neutral');
                    chartStatus.setAttribute('title', chartData.statusDetail || '');
                }
            }

            if (initialChartData) {
                renderWeightChart({
                    labels: initialChartData.labels,
                    actualValues: initialChartData.actual_values,
                    projectedValues: initialChartData.projected_values,
                    statusLabel: initialChartData.status_label,
                    statusClass: initialChartData.status_class,
                    statusDetail: initialChartData.status_detail,
                    yMin: initialChartData.y_min,
                    yMax: initialChartData.y_max,
                    legendActual: initialChartData.legend_actual,
                    legendProjected: initialChartData.legend_projected,
                    projectedGoalWeight: initialChartData.projected_goal_weight,
                });
            }

            function closeModal() {
                modal.classList.remove('is-open');
                modal.hidden = true;
                feedback.textContent = '';
                feedback.dataset.state = '';
            }

            function openModal() {
                valueInput.value = openButton?.dataset.weightValue || '';
                submitButton.textContent = openButton?.dataset.submitLabel || 'Save';
                feedback.textContent = '';
                feedback.dataset.state = '';
                modal.hidden = false;
                modal.classList.add('is-open');

                window.setTimeout(function () {
                    valueInput.focus();
                    valueInput.select();
                }, 0);
            }

            function updateWeightSection(payload) {
                if (!payload || !payload.weight) {
                    return;
                }

                const weight = payload.weight;

                if (todayWeight) {
                    todayWeight.textContent = weight.today_logged_weight ? weight.today_logged_weight + ' kg' : 'No log yet';
                }

                if (averageWeight) {
                    averageWeight.textContent = weight.rolling_average_weight ? weight.rolling_average_weight + ' kg' : 'Waiting';
                }

                if (summary) {
                    summary.textContent = weight.summary;
                }

                if (metricDate) {
                    metricDate.textContent = weight.metric_date_label
                        ? 'Based on the latest weight log from ' + weight.metric_date_label + '.'
                        : 'The rolling average will appear after your first weight log.';
                }

                if (openButton) {
                    openButton.dataset.weightValue = weight.today_logged_weight || '';
                    openButton.dataset.submitLabel = weight.today_button_label || 'Save';
                    openButton.textContent = weight.today_button_label || 'Save';
                }

                if (weight.overall) {
                    if (overallCurrent) {
                        overallCurrent.textContent = weight.overall.current_weight
                            ? weight.overall.current_weight + ' kg avg'
                            : 'No average yet';
                    }

                    if (overallPercent) {
                        overallPercent.textContent = formatPercent(weight.overall.percent);
                    }

                    if (overallProgress) {
                        overallProgress.style.width = weight.overall.percent + '%';
                    }

                    if (overallStatus) {
                        overallStatus.textContent = weight.overall.status;
                    }
                }

                if (weight.monthly) {
                    if (monthlyCurrent) {
                        monthlyCurrent.textContent = weight.monthly.current_weight
                            ? weight.monthly.current_weight + ' kg avg'
                            : 'No average yet';
                    }

                    if (monthlyPercent) {
                        monthlyPercent.textContent = formatPercent(weight.monthly.percent);
                    }

                    if (monthlyProgress) {
                        monthlyProgress.style.width = weight.monthly.percent + '%';
                    }

                    if (monthlyStatus) {
                        monthlyStatus.textContent = weight.monthly.status;
                    }
                }

                if (chartContainer && weight.chart) {
                    renderWeightChart({
                        labels: weight.chart.labels,
                        actualValues: weight.chart.actual_values,
                        projectedValues: weight.chart.projected_values,
                        statusLabel: weight.chart.status_label,
                        statusClass: weight.chart.status_class,
                        statusDetail: weight.chart.status_detail,
                        yMin: weight.chart.y_min,
                        yMax: weight.chart.y_max,
                        legendActual: weight.chart.legend_actual,
                        legendProjected: weight.chart.legend_projected,
                        projectedGoalWeight: weight.chart.projected_goal_weight,
                    });
                }

                if (weight.gauge) {
                    if (gaugeZone) {
                        gaugeZone.textContent = weight.gauge.zone_label;
                        gaugeZone.classList.remove('success', 'warning', 'neutral', 'danger', 'soft-warning', 'warm-warning', 'amber', 'amber-danger');
                        gaugeZone.classList.add(weight.gauge.zone_class || 'neutral');
                    }

                    if (gaugeNeedle) {
                        gaugeNeedle.style.transform = 'translateX(-50%) rotate(' + weight.gauge.angle + 'deg)';
                    }

                    if (gaugeValue) {
                        gaugeValue.textContent = weight.gauge.value_label;
                    }
                }

                if (rowFeedback) {
                    rowFeedback.textContent = payload.message || 'Saved.';
                    rowFeedback.dataset.state = 'success';
                }
            }

            if (openButton) {
                openButton.addEventListener('click', function () {
                    openModal();
                });
            }

            modal.addEventListener('click', function (event) {
                if (event.target === modal || event.target.hasAttribute('data-close-weight-modal')) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                    closeModal();
                }
            });

            form.addEventListener('submit', function (event) {
                event.preventDefault();

                const originalLabel = submitButton.textContent;
                const formData = new FormData(form);

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
                        updateWeightSection(payload);
                        closeModal();
                    })
                    .catch(function (payload) {
                        let message = 'Could not save today\'s weight right now.';

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
                        submitButton.textContent = originalLabel;
                    })
                    .finally(function () {
                        submitButton.disabled = false;
                    });
            });
        })();

        (function () {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!token || !window.fetch) {
                return;
            }

            document.querySelectorAll('[data-goal-milestone-row]').forEach(function (row) {
                const statusPill = row.querySelector('[data-goal-milestone-status]');
                const feedback = row.querySelector('[data-goal-milestone-feedback]');

                function toggleMilestone() {
                    row.classList.add('is-saving');
                    feedback.textContent = '';
                    feedback.dataset.state = '';

                    fetch(row.dataset.action, {
                        method: 'PATCH',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
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
                            if (payload.milestone && statusPill) {
                                statusPill.textContent = payload.milestone.status_label;
                                statusPill.classList.remove('success', 'warning', 'neutral');
                                statusPill.classList.add(payload.milestone.completed ? 'success' : 'warning');
                                row.classList.remove('is-complete', 'is-pending');
                                row.classList.add(payload.milestone.completed ? 'is-complete' : 'is-pending');
                                row.setAttribute(
                                    'aria-label',
                                    (payload.milestone.completed ? 'Mark incomplete' : 'Mark complete') + ' for ' + row.querySelector('.goal-milestone-name').textContent
                                );
                            }

                            feedback.textContent = payload.message || 'Saved.';
                            feedback.dataset.state = 'success';
                        })
                        .catch(function (payload) {
                            let message = 'Could not update this milestone right now.';

                            if (payload && payload.message) {
                                message = payload.message;
                            }

                            feedback.textContent = message;
                            feedback.dataset.state = 'error';
                        })
                        .finally(function () {
                            row.classList.remove('is-saving');
                        });
                }

                row.addEventListener('click', function () {
                    toggleMilestone();
                });

                row.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        toggleMilestone();
                    }
                });
            });
        })();
    </script>
@endpush
