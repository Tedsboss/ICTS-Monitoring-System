<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialPlan extends Model
{
    use HasFactory;

    protected $table = 'financial_plans';

    protected $fillable = [
        'parent_id',
        'fiscal_year',
        'office_name',
        'staff_id',
        'division_id',
        'row_type',
        'program_classification',
        'prexc_code',
        'allocation_type',
        'staff_unit_project',
        'specific_activity',
        'procurement_status',
        'expense_item',
        'assigned_personnel',
        'mooe',
        'capital_outlay',
        'contract_amount',
        'sort_order',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'fiscal_year' => 'integer',
        'staff_id' => 'integer',
        'division_id' => 'integer',
        'allocation_type' => 'string',
        'mooe' => 'decimal:2',
        'capital_outlay' => 'decimal:2',
        'contract_amount' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    // Parent row
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    // Child rows
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('sort_order');
    }

    // Monthly financial targets
    public function targets(): HasMany
    {
        return $this->hasMany(
            FinancialPlanTarget::class,
            'financial_plan_id'
        );
    }

    // Total original MOOE + Capital Outlay
    public function getTotalBudgetAttribute(): float
    {
        return (float) $this->mooe
            + (float) $this->capital_outlay;
    }

    // Total of all monthly targets
    public function getTotalTargetAttribute(): float
    {
        return (float) $this->targets->sum('amount');
    }

    // Monthly amounts keyed from 1 to 12
    public function getMonthlyAmountsAttribute(): array
    {
        $months = array_fill(1, 12, 0.00);

        foreach ($this->targets as $target) {
            $months[(int) $target->month] = (float) $target->amount;
        }

        return $months;
    }

    // Related SAEB entries
    public function saebEntries(): HasMany
    {
        return $this->hasMany(
            Saeb::class,
            'financial_plan_item_id'
        );
    }

    // Related procurement entries
    public function procurements(): HasMany
    {
        return $this->hasMany(
            Procurement::class,
            'financial_plan_item_id'
        );
    }

    // Total SAEB balance
    public function getSaebBalanceAttribute(): float
    {
        return (float) $this->saebEntries->sum('balances');
    }

    // Check if at least one procurement record is OK
    public function getIsProcuredAttribute(): bool
    {
        return $this->procurements
            ->contains('procurement_status', 'OK');
    }

    // Assigned staff / office
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
