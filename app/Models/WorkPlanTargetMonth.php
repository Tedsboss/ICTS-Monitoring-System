<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkPlanTargetMonth extends Model
{
    protected $fillable = [
        'work_plan_target_id',
        'month',
    ];

    protected $casts = [
        'work_plan_target_id' => 'integer',
        'month' => 'integer',
    ];

    public function target(): BelongsTo
    {
        return $this->belongsTo(
            WorkPlanTarget::class,
            'work_plan_target_id'
        );
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

    public function isValidMonth(): bool
    {
        return $this->month >= 1 && $this->month <= 12;
    }
}
