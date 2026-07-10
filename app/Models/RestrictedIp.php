<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestrictedIp extends Model
{
  use HasFactory;

  public function editor()
  {
    return $this->belongsTo(User::class, 'updated_by');
  }
}
