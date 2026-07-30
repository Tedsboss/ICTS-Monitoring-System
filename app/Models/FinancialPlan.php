<?php
// app/Models/FinancialPlan.php

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

        'fiscal_year',
        'office_name',

        'row_type',

        'program_classification',
        'prexc_code',
        'staff_unit_project',
        'specific_activity',
        'procurement_status',
        'expense_item',
        'assigned_personnel',

        'mooe',
        'capital_outlay',

        'sort_order',
    ];

    protected $casts = [
        'mooe'           => 'decimal:2',
        'capital_outlay' => 'decimal:2',
        'fiscal_year'    => 'integer',
        'sort_order'     => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function targets()
    {
        return $this->hasMany(FinancialPlanTarget::class);
    }

    /**
     * Total across MOOE + Capital Outlay (should equal sum of monthly targets).
     */
    public function getTotalBudgetAttribute(): float
    {
        return (float) $this->mooe + (float) $this->capital_outlay;
    }

    /**
     * Sum of the 12 monthly target amounts.
     */
    public function getTotalTargetAttribute(): float
    {
        return (float) $this->targets->sum('amount');
    }

    /**
     * Amounts keyed by month number (1-12), defaulting to 0.
     */
    public function getMonthlyAmountsAttribute()
    {
        return $this->targets
            ->pluck('amount', 'month')
            ->toArray();
    }

    public function saebEntries(): HasMany
    {
        return $this->hasMany(Saeb::class, 'financial_plan_item_id');
    }

    public function procurements(): HasMany
    {
        return $this->hasMany(Procurement::class, 'financial_plan_item_id');
    }

    public function getSaebBalanceAttribute(): float
    {
        return (float) $this->saebEntries->sum('balances');
    }

    public function getIsProcuredAttribute(): bool
    {
        return $this->procurements->contains('procurement_status', 'OK');
    }
}
