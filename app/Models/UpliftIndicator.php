<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UpliftIndicator extends Model
{
  use HasFactory, SoftDeletes;

  protected $guarded = ['id'];

  public function field()
  {
    return $this->belongsTo(UpliftPillarField::class, 'uplift_pillar_field_id');
  }
}
