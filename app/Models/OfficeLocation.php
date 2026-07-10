<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeLocation extends Model
{
  use HasFactory;
  public $table = 'office_locations';

  public function office()
  {
    return $this->belongsTo(Office::class);
  }
}
