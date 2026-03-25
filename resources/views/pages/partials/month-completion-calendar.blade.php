<div class="month-calendar-shell" data-month-key="{{ $monthKey }}">
    <div class="split-grid month-calendar-head">
        <div class="stack">
            <h2>{{ $monthLabel }}</h2>
            <p>{{ $monthSummary }}</p>
        </div>

        <div class="button-row">
            <button type="button" class="button-secondary" data-calendar-month-button data-month="{{ $previousMonthKey }}">
                Previous month
            </button>
            <button type="button" class="button-secondary" data-calendar-month-button data-month="{{ $nextMonthKey }}">
                Next month
            </button>
        </div>
    </div>

    <div class="month-calendar-legend">
        <span><i class="legend-complete"></i> Finished</span>
        <span><i class="legend-missed"></i> Incomplete</span>
        <span><i class="legend-future"></i> Future</span>
    </div>

    <div class="month-calendar-grid" aria-label="{{ $monthLabel }} completion calendar">
        @foreach ($weekdays as $weekday)
            <div class="month-calendar-weekday">{{ $weekday }}</div>
        @endforeach

        @for ($blank = 0; $blank < $leadingBlankDays; $blank++)
            <div class="month-calendar-day blank" aria-hidden="true"></div>
        @endfor

        @foreach ($days as $day)
            <button
                type="button"
                class="month-calendar-day {{ $day['state_class'] }} {{ $day['is_today'] ? 'is-today' : '' }}"
                data-calendar-day-button
                data-date="{{ $day['date'] }}"
                data-state="{{ $day['state'] }}"
                title="{{ $day['tooltip'] }}"
            >
                <span class="month-calendar-date">{{ $day['day_number'] }}</span>
            </button>
        @endforeach
    </div>

    <script type="application/json" data-calendar-day-details>
        @json(collect($days)->mapWithKeys(fn ($day) => [$day['date'] => $day['details']])->all())
    </script>
</div>
