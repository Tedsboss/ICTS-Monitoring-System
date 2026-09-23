<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkPlanTargetMonth extends Model
{
    protected $fillable = [
        'work_plan_target_id',
        'month',
    ];

    protected $casts = [
        'work_plan_target_id' => 'integer',
        'month' => 'integer',
    ];

    public function target(): BelongsTo
    {
        return $this->belongsTo(
            WorkPlanTarget::class,
            'work_plan_target_id'
        );
    }
}