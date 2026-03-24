<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Todo extends Model
{
    public const WEEKDAYS = [
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Friday',
        'saturday' => 'Saturday',
        'sunday' => 'Sunday',
    ];

    protected $fillable = [
        'name',
        'days_of_week',
        'daily_goal',
        'unit',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'days_of_week' => 'array',
            'daily_goal' => 'decimal:2',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TodoLog::class);
    }

    public function latestLog(): HasOne
    {
        return $this->hasOne(TodoLog::class)->latestOfMany('logged_for');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeScheduledFor(Builder $query, string $weekday): Builder
    {
        return $query->whereJsonContains('days_of_week', $weekday);
    }

    public function isScheduledFor(string $weekday): bool
    {
        return in_array($weekday, $this->days_of_week ?? [], true);
    }

    public function goalReached(float|int|string $value): bool
    {
        return (float) $value >= (float) $this->daily_goal;
    }

    public function scheduleLabel(): string
    {
        $labels = collect($this->days_of_week ?? [])
            ->map(fn (string $day) => self::WEEKDAYS[$day] ?? ucfirst($day))
            ->all();

        return implode(', ', $labels);
    }

    public function goalLabel(): string
    {
        return self::formatAmount($this->daily_goal).' '.$this->unit;
    }

    public static function formatAmount(float|int|string $value): string
    {
        $formatted = number_format((float) $value, 2, '.', '');

        return rtrim(rtrim($formatted, '0'), '.');
    }
}
