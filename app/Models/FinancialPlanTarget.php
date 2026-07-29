<?php
// app/Models/FinancialPlanTarget.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialPlanTarget extends Model
{
    use HasFactory;

    protected $table = 'financial_plan_targets';

    protected $fillable = ['financial_plan_id', 'month', 'amount'];

    protected $casts = [
        'month'  => 'integer',
        'amount' => 'decimal:2',
    ];

    public function financialPlan(): BelongsTo
    {
        return $this->belongsTo(FinancialPlan::class);
    }
}
