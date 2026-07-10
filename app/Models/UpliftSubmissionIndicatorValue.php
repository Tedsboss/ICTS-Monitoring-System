<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpliftSubmissionIndicatorValue extends Model
{
  use HasFactory;

  protected $guarded = ['id'];

  protected $casts = [
    'date_value' => 'date',
    'date_start_value' => 'date',
    'date_end_value' => 'date',
  ];

  public function submission()
  {
    return $this->belongsTo(UpliftSubmission::class, 'uplift_submission_id');
  }

  public function indicator()
  {
    return $this->belongsTo(UpliftIndicator::class, 'uplift_indicator_id');
  }
}
