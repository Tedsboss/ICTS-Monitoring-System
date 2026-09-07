<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialPlanAllocationItem extends Model
{
    use HasFactory;

    protected $table = 'financial_plan_allocation_items';

    protected $fillable = [
        'financial_plan_allocation_id',
        'allocation_type_id',
        'expense_category',
        'amount',
    ];

    protected $casts = [
        'financial_plan_allocation_id' => 'integer',
        'allocation_type_id' => 'integer',
        'amount' => 'decimal:2',
    ];

    // Allocation header
    public function allocation(): BelongsTo
    {
        return $this->belongsTo(
            FinancialPlanAllocation::class,
            'financial_plan_allocation_id'
        );
    }

    // Allocation type
    public function allocationType(): BelongsTo
    {
        return $this->belongsTo(
            FinancialPlanAllocationType::class,
            'allocation_type_id'
        );
    }
}
