<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobRun extends Model
{
  use HasFactory;
  protected $fillable = [
    'job_id',
    'job_name',
    'queue',
    'connection',
    'status',
    'attempts',
    'payload',
    'started_at',
    'finished_at',
    'duration_ms',
    'exception',
  ];

  protected $casts = [
    'payload'     => 'array',
    'started_at'  => 'datetime',
    'finished_at' => 'datetime',
  ];
}
