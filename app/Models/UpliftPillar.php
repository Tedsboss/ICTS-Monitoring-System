<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpliftPillar extends Model
{
  use HasFactory;

  protected $guarded = ['id'];

  public function measures()
  {
    return $this->hasMany(UpliftMeasure::class)->orderBy('title');
  }
}
