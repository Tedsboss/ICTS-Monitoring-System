<?php
// app/Models/FinancialPlanAllocation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialPlanAllocation extends Model
{
    protected $table = 'financial_plan_allocations';

    protected $fillable = [
        'fiscal_year',
        'office_name',
        'mooe_allocation',
        'capital_outlay_allocation',
        'ninp_allocation',
    ];

    protected $casts = [
        'fiscal_year'                => 'integer',
        'mooe_allocation'            => 'decimal:2',
        'capital_outlay_allocation'  => 'decimal:2',
    ];
}