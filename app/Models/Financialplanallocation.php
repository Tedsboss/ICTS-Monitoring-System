<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialPlanAllocation extends Model
{
    protected $table = 'financial_plan_allocations';

    protected $fillable = [
        'fiscal_year',
        'office_name',
        'division_id',
        'mooe_allocation',
        'capital_outlay_allocation',
        'ninp_allocation',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'division_id' => 'integer',
        'mooe_allocation' => 'decimal:2',
        'capital_outlay_allocation' => 'decimal:2',
        'ninp_allocation' => 'decimal:2',
    ];

    // Assigned division
    public function division(): BelongsTo
    {
        return $this->belongsTo(
            Division::class,
            'division_id'
        );
    }
}
