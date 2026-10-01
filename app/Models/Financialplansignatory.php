<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialPlanSignatory extends Model
{
    protected $table = 'financial_plan_signatories';

    protected $fillable = [
        'fiscal_year',
        'office_name',
        'staff_id',
        'division_id',
        'prepared_by',
        'prepared_by_position',
        'reviewed_by',
        'reviewed_by_position',
        'recommended_by',
        'recommended_by_position',
        'approved_by',
        'approved_by_position',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'staff_id' => 'integer',
        'division_id' => 'integer',
    ];

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
}
