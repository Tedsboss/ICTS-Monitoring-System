<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasFactory;

    protected $table = 'divisions';

    protected $fillable = [
        'name',
        'abbreviation',
        'head_name',
        'head_position',
        'head_email',
        'can_review',
        'withunit',
        'office_id',
        'staff_id',
        'parent_id',
    ];

    protected $casts = [
        'office_id' => 'integer',
        'staff_id' => 'integer',
        'parent_id' => 'integer',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(
            Office::class,
            'office_id'
        );
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(
            Staff::class,
            'staff_id'
        );
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'parent_id'
        );
    }

    public function children(): HasMany
    {
        return $this->hasMany(
            self::class,
            'parent_id'
        );
    }

    public function units(): HasMany
    {
        return $this->hasMany(
            Unit::class,
            'division_id'
        );
    }

    public function users(): HasMany
    {
        return $this->hasMany(
            User::class,
            'division_id'
        );
    }

    public function financialPlans(): HasMany
    {
        return $this->hasMany(
            FinancialPlan::class,
            'division_id'
        );
    }

    public function financialPlanSubmissions(): HasMany
    {
        return $this->hasMany(
            FinancialPlanSubmission::class,
            'division_id'
        );
    }

    public function financialPlanSignatories(): HasMany
    {
        return $this->hasMany(
            FinancialPlanSignatory::class,
            'division_id'
        );
    }

    public function workPlans(): HasMany
    {
        return $this->hasMany(
            WorkPlan::class,
            'division_id'
        );
    }

    public function canReview(): bool
    {
        return $this->can_review === 'Y';
    }

    public function hasUnits(): bool
    {
        return $this->withunit === 'Y';
    }
}
