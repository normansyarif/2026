<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TodoLog extends Model
{
    protected $fillable = [
        'todo_id',
        'logged_for',
        'value',
        'completed',
    ];

    protected function casts(): array
    {
        return [
            'logged_for' => 'date',
            'value' => 'decimal:2',
            'completed' => 'boolean',
        ];
    }

    public function todo(): BelongsTo
    {
        return $this->belongsTo(Todo::class);
    }
}
