<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseType extends Model
{
    use HasFactory;

    protected $table = 'expense_types';

    protected $fillable = [
        'type',
        'expense_description',
    ];

    public function allocationExpenses(): HasMany
    {
        return $this->hasMany(AllocationExpense::class, 'expense_id');
    }
}