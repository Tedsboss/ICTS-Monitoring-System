<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionNotification extends Model
{
  use HasFactory;

  protected $guarded = ['id'];

  protected $casts = [
    'read_at' => 'datetime',
  ];

  public function submission()
  {
    return $this->belongsTo(FormSubmission::class, 'form_submission_id');
  }

  public function upliftSubmission()
  {
    return $this->belongsTo(UpliftSubmission::class, 'uplift_submission_id');
  }

  public function form()
  {
    return $this->belongsTo(Form::class);
  }

  public function upliftMeasure()
  {
    return $this->belongsTo(UpliftMeasure::class, 'uplift_measure_id');
  }

  public function agency()
  {
    return $this->belongsTo(Agency::class, 'agency_id');
  }

  public function recipient()
  {
    return $this->belongsTo(User::class, 'recipient_user_id');
  }
}
