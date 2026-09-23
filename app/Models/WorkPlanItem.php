<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkPlanItem extends Model
{
    protected $fillable = [
        'work_plan_id',
        'parent_id',
        'row_type',
        'title',
        'classification_id',
        'specific_activity',
        'sort_order',
    ];

    protected $casts = [
        'work_plan_id' => 'integer',
        'parent_id' => 'integer',
        'classification_id' => 'integer',
        'sort_order' => 'integer',
    ];

    public function workPlan(): BelongsTo
    {
        return $this->belongsTo(
            WorkPlan::class,
            'work_plan_id'
        );
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            WorkPlanItem::class,
            'parent_id'
        );
    }

    public function children(): HasMany
    {
        return $this->hasMany(
            WorkPlanItem::class,
            'parent_id'
        )
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function classification(): BelongsTo
    {
        return $this->belongsTo(
            WorkPlanClassification::class,
            'classification_id'
        );
    }

    public function targets(): HasMany
    {
        return $this->hasMany(
            WorkPlanTarget::class,
            'work_plan_item_id'
        )
            ->orderBy('month')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function targetsForMonth(int $month)
    {
        return $this->targets
            ->where('month', $month)
            ->sortBy('sort_order')
            ->values();
    }

    public function isHeader(): bool
    {
        return $this->row_type === 'header';
    }

    public function isSubheader(): bool
    {
        return $this->row_type === 'subheader';
    }

    public function isItem(): bool
    {
        return $this->row_type === 'item';
    }
}