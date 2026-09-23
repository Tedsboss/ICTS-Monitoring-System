<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkPlanClassification extends Model
{
    protected $fillable = [
        'fiscal_year',
        'parent_id',
        'code',
        'name',
        'level',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'parent_id' => 'integer',
        'level' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            WorkPlanClassification::class,
            'parent_id'
        );
    }

    public function children(): HasMany
    {
        return $this->hasMany(
            WorkPlanClassification::class,
            'parent_id'
        )
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            WorkPlanItem::class,
            'classification_id'
        )
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function scopeForFiscalYear($query, int $fiscalYear)
    {
        return $query->where('fiscal_year', $fiscalYear);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
