<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialPlanSubmission extends Model
{
    protected $table = 'financial_plan_submissions';

    protected $fillable = [
        'fiscal_year',
        'office_name',
        'staff_id',
        'division_id',
        'status',
        'finalized',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'finalized_by',
        'finalized_at',
        'return_remarks',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'staff_id' => 'integer',
        'division_id' => 'integer',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'finalized_at' => 'datetime',
    ];

    // Check if the plan is locked
    public function isLocked(): bool
    {
        return $this->finalized === 'yes';
    }

    // User who submitted the plan
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'submitted_by'
        );
    }

    // User who approved the plan
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    // User who finalized the plan
    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'finalized_by'
        );
    }

    // Assigned staff or office
    public function staff(): BelongsTo
    {
        return $this->belongsTo(
            Staff::class,
            'staff_id'
        );
    }

    // Assigned division
    public function division(): BelongsTo
    {
        return $this->belongsTo(
            Division::class,
            'division_id'
        );
    }
}
