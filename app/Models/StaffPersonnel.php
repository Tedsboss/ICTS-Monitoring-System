<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffPersonnel extends Model
{
    use HasFactory;

    protected $table = 'staff_personnel';

    protected $fillable = [
        'staff_id',
        'name',
        'position',
        'is_active',
    ];

    protected $casts = [
        'staff_id' => 'integer',
        'is_active' => 'boolean',
    ];

    // Staff/office this personnel belongs to
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    // Only personnel available for current selection
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
