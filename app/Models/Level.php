<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    use HasFactory;

    protected $table = 'levels';

    protected $fillable = [
        'level_code',
        'level_description',
    ];

    public function allocations(): HasMany
    {
        return $this->hasMany(Allocation::class, 'level_id');
    }
}