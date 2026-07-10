<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
  use HasFactory;

  protected $guarded = [];

  public function creator()
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  public function users()
  {
    return $this->hasMany(User::class);
  }

  public function permissions()
  {
    return $this->belongsToMany(Permission::class);
  }
}
