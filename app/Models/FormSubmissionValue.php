<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmissionValue extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'date_value' => 'date',
        'date_start_value' => 'date',
        'date_end_value' => 'date',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(
            FormSubmission::class,
            'form_submission_id'
        );
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(
            FormField::class,
            'form_field_id'
        );
    }
}
