<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllocationExpense extends Model
{
    use HasFactory;

    protected $table = 'allocation_expenses';

    protected $fillable = [
        'allocation_id',
        'expense_id',
        'cost',
    ];

    protected $casts = [
        'allocation_id' => 'integer',
        'expense_id' => 'integer',
        'cost' => 'decimal:2',
    ];

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(Allocation::class, 'allocation_id');
    }

    public function expenseType(): BelongsTo
    {
        return $this->belongsTo(ExpenseType::class, 'expense_id');
    }
}