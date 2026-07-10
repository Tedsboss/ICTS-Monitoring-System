<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrustedDevice extends Model
{
  use HasFactory;
  protected $appends = ['status'];
  public $timestamps = ['last_seen_at', 'expires_at'];

  public function getStatusAttribute()
  {
    if ($this->revoked_at != null) {
      return 'Revoked';
    } else if (Carbon::parse($this->expires_at)->isPast()) {
      return 'Expired';
    } else {
      return 'Active';
    }
  }
}
