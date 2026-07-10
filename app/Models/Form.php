<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
  use HasFactory;

  protected $guarded = ['id'];

  public function agency()
  {
    return $this->belongsTo(Agency::class, 'agency_id');
  }

  public function assignedSector()
  {
    return $this->belongsTo(Staff::class, 'assigned_sector_id');
  }

  public function templateSource()
  {
    return $this->belongsTo(self::class, 'template_source_form_id');
  }

  public function copies()
  {
    return $this->hasMany(self::class, 'template_source_form_id');
  }

  public function fields()
  {
    return $this->hasMany(FormField::class)->orderBy('row_number')->orderBy('order');
  }

  public function submissions()
  {
    return $this->hasMany(FormSubmission::class);
  }
}
