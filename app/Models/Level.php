<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    use HasFactory;

    protected $table = 'levels';

    protected $fillable = [
        'level_code',
        'level_description',
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

    public function allocations(): HasMany
    {
        return $this->hasMany(
            Allocation::class,
            'level_id'
        );
    }
}
