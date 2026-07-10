<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Inquiry extends Model
{
  use HasFactory, Notifiable;

  public function editor()
  {
    return $this->belongsTo(User::class, 'updated_by');
  }

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id');
  }
}
