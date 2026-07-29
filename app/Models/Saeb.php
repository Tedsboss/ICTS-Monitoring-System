<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Saeb extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'saeb';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'as_of_date',
        'funding_source',
        'allotment_class',
        'expense_class',
        'allotment',
        'obligated',
        'aa',
        'balances',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'as_of_date' => 'date',
        'allotment'  => 'decimal:2',
        'obligated'  => 'decimal:2',
        'aa'         => 'decimal:2',
        'balances'   => 'decimal:2',
    ];

    /**
     * Percent obligated (obligated / allotment), rounded to 2 decimals.
     */
    public function getPercentObligatedAttribute(): float
    {
        if ((float) $this->allotment === 0.0) {
            return 0.0;
        }

        return round(((float) $this->obligated / (float) $this->allotment) * 100, 2);
    }

    // Saeb.php / Procurement.php
    public function financialPlanItem()
    {
        return $this->belongsTo(FinancialPlanItem::class);
    }

}
