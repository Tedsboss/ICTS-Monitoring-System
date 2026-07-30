<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialPlanSignatory extends Model
{
    protected $fillable = [
        'fiscal_year',
        'office_name',
        'prepared_by',
        'prepared_by_position',
        'reviewed_by',
        'reviewed_by_position',
        'recommended_by',
        'recommended_by_position',
        'approved_by',
        'approved_by_position',
    ];
}
