<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(
            Agency::class,
            'agency_id'
        );
    }

    public function assignedSector(): BelongsTo
    {
        return $this->belongsTo(
            Staff::class,
            'assigned_sector_id'
        );
    }

    public function templateSource(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'template_source_form_id'
        );
    }

    public function copies(): HasMany
    {
        return $this->hasMany(
            self::class,
            'template_source_form_id'
        );
    }

    public function fields(): HasMany
    {
        return $this->hasMany(
            FormField::class,
            'form_id'
        )
            ->orderBy('row_number')
            ->orderBy('order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(
            FormSubmission::class,
            'form_id'
        );
    }
}
