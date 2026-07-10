<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpliftSubmission extends Model
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

  public function measure()
  {
    return $this->belongsTo(UpliftMeasure::class, 'uplift_measure_id');
  }

  public function agency()
  {
    return $this->belongsTo(Agency::class, 'agency_id');
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function fieldValues()
  {
    return $this->hasMany(UpliftSubmissionFieldValue::class);
  }

  public function indicatorValues()
  {
    return $this->hasMany(UpliftSubmissionIndicatorValue::class);
  }

  public function isSubmitted()
  {
    return $this->status == 'submitted';
  }

  public function isEditableStatus(): bool
  {
    return in_array($this->status, ['draft', 'returned'], true);
  }

  public function approver()
  {
    return $this->belongsTo(User::class, 'approved_by');
  }

  public function approvalHistories()
  {
    return $this->morphMany(SubmissionApprovalHistory::class, 'submission')->latest();
  }
}
