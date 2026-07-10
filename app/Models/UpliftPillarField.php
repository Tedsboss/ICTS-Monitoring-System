<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UpliftPillarField extends Model
{
  use HasFactory, SoftDeletes;

  protected $guarded = ['id'];

  protected $casts = [
    'options' => 'array',
  ];

  public function measure()
  {
    return $this->belongsTo(UpliftMeasure::class, 'uplift_measure_id');
  }

  public function parent()
  {
    return $this->belongsTo(self::class, 'parent_id');
  }

  public function children()
  {
    return $this->hasMany(self::class, 'parent_id')->orderBy('row_number')->orderBy('order')->orderBy('id');
  }

  public function indicators()
  {
    return $this->hasMany(UpliftIndicator::class)->orderBy('order')->orderBy('id');
  }

  public function descendantIds()
  {
    $ids = collect();

    $this->children()->with('children')->get()->each(function ($child) use (&$ids) {
      $ids->push($child->id);
      $ids = $ids->merge($child->descendantIds());
    });

    return $ids;
  }
}
