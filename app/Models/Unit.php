<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
  use HasFactory;

  public function office()
  {
    return $this->belongsTo(Office::class);
  }

  public function division()
  {
    return $this->belongsTo(Division::class);
  }
}
