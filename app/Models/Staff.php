<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staffs';

    protected $fillable = [
        'name',
        'abbreviation',
        'head_name',
        'head_position',
        'head_email',
        'withdivision',
        'can_check',
        'region_code',
        'is_DCO',
        'office_id',
        'group_id',
    ];

    protected $casts = [
        'can_check' => 'integer',
        'office_id' => 'integer',
        'group_id' => 'integer',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(
            Office::class,
            'office_id'
        );
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(
            Group::class,
            'group_id'
        );
    }

    public function users(): HasMany
    {
        return $this->hasMany(
            User::class,
            'staff_id'
        );
    }

    public function divisions(): HasMany
    {
        return $this->hasMany(
            Division::class,
            'staff_id'
        );
    }

    public function fiscalYears(): HasMany
    {
        return $this->hasMany(
            FiscalYear::class,
            'staff_id'
        );
    }

    public function levels(): HasMany
    {
        return $this->hasMany(
            Level::class,
            'staff_id'
        );
    }

    public function expenseTypes(): HasMany
    {
        return $this->hasMany(
            ExpenseType::class,
            'staff_id'
        );
    }

    public function expenseItems(): HasMany
    {
        return $this->hasMany(
            ExpenseItem::class,
            'staff_id'
        );
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(
            Allocation::class,
            'staff_id'
        );
    }

    public function financialPlans(): HasMany
    {
        return $this->hasMany(
            FinancialPlan::class,
            'staff_id'
        );
    }

    public function workPlans(): HasMany
    {
        return $this->hasMany(
            WorkPlan::class,
            'staff_id'
        );
    }

    public function hasDivisions(): bool
    {
        return $this->withdivision === 'Y';
    }

    public function isDco(): bool
    {
        return $this->is_DCO === 'Y';
    }
}
