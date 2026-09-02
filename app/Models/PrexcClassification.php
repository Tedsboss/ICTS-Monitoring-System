<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrexcClassification extends Model
{
    use HasFactory;

    protected $table = 'prexc_classifications';

    protected $fillable = [
        'classification_group',
        'program_name',
        'classification_name',
        'prexc_code',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    // Only classifications that can currently be selected
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Keep the official display order
    public function scopeOrdered($query)
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('classification_group')
            ->orderBy('program_name')
            ->orderBy('classification_name');
    }
}
