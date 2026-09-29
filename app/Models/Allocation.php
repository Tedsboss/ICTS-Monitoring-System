<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Allocation extends Model
{
    use HasFactory;

    protected $table = 'allocations';

    protected $fillable = [
        'year_id',
        'level_id',
    ];

    protected $casts = [
        'year_id' => 'integer',
        'level_id' => 'integer',
    ];

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class, 'year_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(AllocationExpense::class, 'allocation_id');
    }
}