<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkPlanTarget extends Model
{
    protected $fillable = [
        'work_plan_item_id',
        'month',
        'target_output',
        'sort_order',
    ];

    protected $casts = [
        'work_plan_item_id' => 'integer',
        'month' => 'integer',
        'sort_order' => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(
            WorkPlanItem::class,
            'work_plan_item_id'
        );
    }

    public function months(): HasMany
    {
        return $this->hasMany(
            WorkPlanTargetMonth::class,
            'work_plan_target_id'
        )->orderBy('month');
    }

    public function applicableMonthNumbers(): array
    {
        $this->loadMissing('months');

        if ($this->months->isNotEmpty()) {
            return $this->months
                ->pluck('month')
                ->map(fn ($month) => (int) $month)
                ->filter(fn ($month) => $month >= 1 && $month <= 12)
                ->unique()
                ->sort()
                ->values()
                ->all();
        }

        $month = (int) $this->month;

        return $month >= 1 && $month <= 12
            ? [$month]
            : [];
    }

    public function appliesToMonth(int $month): bool
    {
        if ($month < 1 || $month > 12) {
            return false;
        }

        return in_array(
            $month,
            $this->applicableMonthNumbers(),
            true
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
            1 => 'J',
            2 => 'F',
            3 => 'M',
            4 => 'A',
            5 => 'M',
            6 => 'J',
            7 => 'J',
            8 => 'A',
            9 => 'S',
            10 => 'O',
            11 => 'N',
            12 => 'D',
            default => '',
        };
    }
}
