<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Allocation extends Model
{
    use HasFactory;

    protected $table = 'allocations';

    protected $fillable = [
        'year_id',
        'level_id',
        'program_id',
        'mooe_budget',
        'co_budget',
        'staff_id',
    ];

    protected $casts = [
        'year_id' => 'integer',
        'level_id' => 'integer',
        'program_id' => 'integer',
        'mooe_budget' => 'decimal:2',
        'co_budget' => 'decimal:2',
        'staff_id' => 'integer',
    ];

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(
            FiscalYear::class,
            'year_id'
        );
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(
            Level::class,
            'level_id'
        );
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(
            Program::class,
            'program_id'
        );
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(
            Staff::class,
            'staff_id'
        );
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(
            AllocationExpense::class,
            'allocation_id'
        );
    }

    public function financialPlans(): HasMany
    {
        return $this->hasMany(
            FinancialPlan::class,
            'allocation_id'
        );
    }

    public function getTotalBudgetAttribute(): float
    {
        return (float) $this->mooe_budget
            + (float) $this->co_budget;
    }
}
