<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
  use HasFactory;

  public $timestamps = ["created_at"];
  const UPDATED_AT = null;

  protected $fillable = [
    'systemlog_id',
    'user_id',
    'reference_table',
    'model_type',
    'model_id',
    'body'
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function model()
  {
    return $this->morphTo();
  }
}
