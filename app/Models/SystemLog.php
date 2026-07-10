<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
  use HasFactory;

  public $timestamps = ["created_at"];
  const UPDATED_AT = null;

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function history()
  {
    return $this->hasOne(History::class, 'systemlog_id');
  }
}
