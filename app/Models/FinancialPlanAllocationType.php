<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function staff(): BelongsTo
    {
        return $this->belongsTo(
            Staff::class,
            'staff_id'
        );
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForStaff($query, int $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    public function scopeOrdered($query)
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('name');
    }
}
