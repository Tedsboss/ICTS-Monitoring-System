<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpliftSubmissionFieldValue extends Model
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

  public function field()
  {
    return $this->belongsTo(UpliftPillarField::class, 'uplift_pillar_field_id');
  }
}
