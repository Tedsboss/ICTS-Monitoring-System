<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WorkPlan extends Model
{
    protected $fillable = [
        'fiscal_year',
        'staff_id',
        'division_id',
        'status',
        'finalized',
        'created_by',
        'updated_by',
        'submitted_at',
        'submitted_by',
        'approved_at',
        'approved_by',
        'finalized_at',
        'finalized_by',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'staff_id' => 'integer',
        'division_id' => 'integer',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'finalized_at' => 'datetime',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(WorkPlanItem::class, 'work_plan_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function signatory(): HasOne
    {
        return $this->hasOne(
            WorkPlanSignatory::class,
            'work_plan_id'
        );
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(WorkPlanSubmission::class, 'work_plan_id')
            ->orderByDesc('acted_at')
            ->orderByDesc('id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

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

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'returned'], true)
            && $this->finalized !== 'yes';
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(
            Division::class,
            'division_id'
        );
    }
}