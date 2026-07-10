<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
  use HasFactory;
  public $table = 'staffs';

  public function office()
  {
    return $this->belongsTo(Office::class);
  }

  public function group()
  {
    return $this->belongsTo(Group::class);
  }

  public function divisions()
  {
    return $this->hasMany(Division::class);
  }

  // public function centers()
  // {
  //   return $this->hasMany(Center::class);
  // }
}
