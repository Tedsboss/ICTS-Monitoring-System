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
        'allocation_id',
        'office_name',
        'staff_id',
        'division_id',
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
        'contract_amount',
        'sort_order',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'fiscal_year' => 'integer',
        'allocation_id' => 'integer',
        'staff_id' => 'integer',
        'division_id' => 'integer',
        'mooe' => 'decimal:2',
        'capital_outlay' => 'decimal:2',
        'contract_amount' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(
            Allocation::class,
            'allocation_id'
        );
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'parent_id'
        );
    }

    public function children(): HasMany
    {
        return $this->hasMany(
            self::class,
            'parent_id'
        )
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(
            FinancialPlanTarget::class,
            'financial_plan_id'
        )->orderBy('month');
    }

    public function saebEntries(): HasMany
    {
        return $this->hasMany(
            Saeb::class,
            'financial_plan_item_id'
        );
    }

    public function procurements(): HasMany
    {
        return $this->hasMany(
            Procurement::class,
            'financial_plan_item_id'
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

    public function getTotalBudgetAttribute(): float
    {
        return (float) $this->mooe
            + (float) $this->capital_outlay;
    }

    public function getTotalTargetAttribute(): float
    {
        return (float) $this->targets->sum('amount');
    }

    public function getMonthlyAmountsAttribute(): array
    {
        $months = array_fill(1, 12, 0.0);

        foreach ($this->targets as $target) {
            $month = (int) $target->month;

            if ($month >= 1 && $month <= 12) {
                $months[$month] = (float) $target->amount;
            }
        }

        return $months;
    }

    public function getSaebBalanceAttribute(): float
    {
        return (float) $this->saebEntries->sum('balances');
    }

    public function getIsProcuredAttribute(): bool
    {
        return $this->procurements
            ->contains('procurement_status', 'OK');
    }

    public function isHeader(): bool
    {
        return $this->row_type === 'header';
    }

    public function isSubheader(): bool
    {
        return $this->row_type === 'subheader';
    }

    public function isItem(): bool
    {
        return $this->row_type === 'item';
    }
}
