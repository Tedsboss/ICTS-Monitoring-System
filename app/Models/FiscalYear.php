<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiscalYear extends Model
{
    use HasFactory;

    protected $table = 'fiscal_years';

    protected $fillable = [
        'year',
        'staff_id',
    ];

    protected $casts = [
        'year' => 'integer',
        'staff_id' => 'integer',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(
            Staff::class,
            'staff_id'
        );
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(
            Allocation::class,
            'year_id'
        );
    }
}
