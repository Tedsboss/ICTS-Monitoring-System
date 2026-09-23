<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkPlanSignatory extends Model
{
    protected $fillable = [
        'work_plan_id',
        'prepared_by',
        'prepared_by_position',
        'reviewed_by',
        'reviewed_by_position',
        'recommended_by',
        'recommended_by_position',
        'approved_by',
        'approved_by_position',
    ];

    protected $casts = [
        'work_plan_id' => 'integer',
    ];

    public function workPlan(): BelongsTo
    {
        return $this->belongsTo(
            WorkPlan::class,
            'work_plan_id'
        );
    }
}