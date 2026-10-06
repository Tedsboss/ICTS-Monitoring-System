<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialPlanWorkflowHistory extends Model
{
    protected $table = 'financial_plan_workflow_histories';

    protected $fillable = [
        'fiscal_year',
        'staff_id',
        'office_name',
        'action',
        'from_status',
        'to_status',
        'remarks',
        'acted_by',
        'acted_at',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'staff_id' => 'integer',
        'acted_by' => 'integer',
        'acted_at' => 'datetime',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}
