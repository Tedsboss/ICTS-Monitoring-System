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
        'submitted_by' => 'integer',
        'approved_by' => 'integer',
        'finalized_by' => 'integer',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'finalized_at' => 'datetime',
    ];

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isReturned(): bool
    {
        return $this->status === 'returned';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isFinalized(): bool
    {
        return $this->status === 'finalized'
            || $this->finalized === 'yes';
    }

    public function isLocked(): bool
    {
        return $this->isFinalized();
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'returned'], true)
            && ! $this->isFinalized();
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'submitted_by'
        );
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'finalized_by'
        );
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(
            Staff::class,
            'staff_id'
        );
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(
            Division::class,
            'division_id'
        );
    }
}
