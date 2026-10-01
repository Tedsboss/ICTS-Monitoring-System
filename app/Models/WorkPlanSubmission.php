<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkPlanSubmission extends Model
{
    protected $fillable = [
        'work_plan_id',
        'staff_id',
        'action',
        'from_status',
        'to_status',
        'remarks',
        'acted_by',
        'acted_at',
    ];

    protected $casts = [
        'work_plan_id' => 'integer',
        'staff_id' => 'integer',
        'acted_by' => 'integer',
        'acted_at' => 'datetime',
    ];

    public function workPlan(): BelongsTo
    {
        return $this->belongsTo(
            WorkPlan::class,
            'work_plan_id'
        );
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(
            Staff::class,
            'staff_id'
        );
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'acted_by'
        );
    }

    public function isSubmit(): bool
    {
        return $this->action === 'submit';
    }

    public function isReturn(): bool
    {
        return $this->action === 'return';
    }

    public function isApprove(): bool
    {
        return $this->action === 'approve';
    }

    public function isFinalize(): bool
    {
        return $this->action === 'finalize';
    }

    public function isReopen(): bool
    {
        return $this->action === 'reopen';
    }
}
