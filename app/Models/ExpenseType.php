<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseType extends Model
{
    use HasFactory;

    protected $table = 'expense_types';

    protected $fillable = [
        'type',
        'expense_description',
        'staff_id',
    ];

    protected $casts = [
        'staff_id' => 'integer',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(
            Staff::class,
            'staff_id'
        );
    }

    public function allocationExpenses(): HasMany
    {
        return $this->hasMany(
            AllocationExpense::class,
            'expense_id'
        );
    }
}
