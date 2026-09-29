<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiscalYear extends Model
{
    use HasFactory;

    protected $table = 'fiscal_years';

    protected $fillable = [
        'year',
    ];

    protected $casts = [
        'year' => 'integer',
    ];

    public function allocations(): HasMany
    {
        return $this->hasMany(Allocation::class, 'year_id');
    }
}