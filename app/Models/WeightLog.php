<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeightLog extends Model
{
    protected $fillable = [
        'logged_for',
        'weight',
        'rolling_average_weight',
    ];

    protected function casts(): array
    {
        return [
            'logged_for' => 'date',
            'weight' => 'decimal:2',
            'rolling_average_weight' => 'decimal:2',
        ];
    }
}
