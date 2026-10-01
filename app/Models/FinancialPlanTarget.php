<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialPlanTarget extends Model
{
    use HasFactory;

    protected $table = 'financial_plan_targets';

    protected $fillable = [
        'financial_plan_id',
        'month',
        'amount',
    ];

    protected $casts = [
        'financial_plan_id' => 'integer',
        'month' => 'integer',
        'amount' => 'decimal:2',
    ];

    public function financialPlan(): BelongsTo
    {
        return $this->belongsTo(
            FinancialPlan::class,
            'financial_plan_id'
        );
    }

    public function isValidMonth(): bool
    {
        return $this->month >= 1 && $this->month <= 12;
    }

    public function getMonthNameAttribute(): string
    {
        return match ($this->month) {
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
            default => '',
        };
    }

    public function getMonthShortNameAttribute(): string
    {
        return match ($this->month) {
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Aug',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dec',
            default => '',
        };
    }
}
