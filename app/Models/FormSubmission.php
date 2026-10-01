<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class FormSubmission extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'reporting_cutoff_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'returned_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(
            Form::class,
            'form_id'
        );
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(
            Agency::class,
            'agency_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    public function values(): HasMany
    {
        return $this->hasMany(
            FormSubmissionValue::class,
            'form_submission_id'
        );
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    public function approvalHistories(): MorphMany
    {
        return $this->morphMany(
            SubmissionApprovalHistory::class,
            'submission'
        )->latest();
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isEditableStatus(): bool
    {
        return in_array(
            $this->status,
            ['draft', 'returned'],
            true
        );
    }
}
