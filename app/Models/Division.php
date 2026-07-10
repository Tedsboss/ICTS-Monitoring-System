<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
  use HasFactory;

  public function office()
  {
    return $this->belongsTo(Office::class);
  }

  public function staff()
  {
    return $this->belongsTo(Staff::class);
  }

  public function units()
  {
    return $this->hasMany(Unit::class);
  }
}
