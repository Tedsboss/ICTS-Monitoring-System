<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialPlanSubmission extends Model
{
    protected $fillable = [
        'fiscal_year', 'office_name', 'status',
        'submitted_by', 'submitted_at',
        'approved_by', 'approved_at', 'return_remarks',
        'finalized',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at'  => 'datetime',
    ];

    public function isLocked(): bool
    {
        return $this->finalized === 'yes';
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}