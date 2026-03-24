<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Goal extends Model
{
    protected $fillable = [
        'name',
    ];

    public function milestones(): HasMany
    {
        return $this->hasMany(GoalMilestone::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function nextMilestone(): HasOne
    {
        return $this->hasOne(GoalMilestone::class)->ofMany('sort_order', 'min');
    }
}
