<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
  use HasFactory;

  public function staffs()
  {
    return $this->hasMany(Staff::class);
  }

  public function divisions()
  {
    return $this->hasMany(Division::class);
  }

  public function units()
  {
    return $this->hasMany(Unit::class);
  }

  public function locations()
  {
    return $this->hasMany(OfficeLocation::class);
  }
}
