<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpliftMeasure extends Model
{
  use HasFactory;

  protected $guarded = ['id'];

  public function pillar()
  {
    return $this->belongsTo(UpliftPillar::class, 'uplift_pillar_id');
  }

  public function leadAgency()
  {
    return $this->belongsTo(Agency::class, 'lead_agency_id');
  }

  public function assignedSector()
  {
    return $this->belongsTo(Staff::class, 'assigned_sector_id');
  }

  public function supportingAgencies()
  {
    return $this->belongsToMany(Agency::class, 'uplift_measure_supporting_agencies', 'uplift_measure_id', 'agency_id')
      ->select('agencies.*')
      ->withTimestamps();
  }

  public function fields()
  {
    return $this->hasMany(UpliftPillarField::class)->orderBy('row_number')->orderBy('order')->orderBy('id');
  }
}
