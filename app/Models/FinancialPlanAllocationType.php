<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialPlanAllocationType extends Model
{
    use HasFactory;

    protected $table = 'financial_plan_allocation_types';

    protected $fillable = [
        'staff_id',
        'code',
        'name',
        'allows_mooe',
        'allows_capital_outlay',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'staff_id' => 'integer',
        'allows_mooe' => 'boolean',
        'allows_capital_outlay' => 'boolean',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    // Assigned staff or office
    public function staff(): BelongsTo
    {
        return $this->belongsTo(
            Staff::class,
            'staff_id'
        );
    }

    // Allocation amounts using this type
    public function allocationItems(): HasMany
    {
        return $this->hasMany(
            FinancialPlanAllocationItem::class,
            'allocation_type_id'
        );
    }

    // Active allocation types
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Allocation types for one staff or office
    public function scopeForStaff($query, int $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    // Default display order
    public function scopeOrdered($query)
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('name');
    }
}
