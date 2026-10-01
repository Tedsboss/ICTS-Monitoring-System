<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Office extends Model
{
    use HasFactory;

    protected $table = 'offices';

    protected $fillable = [
        'name',
        'abbreviation',
    ];

    public function staffs(): HasMany
    {
        return $this->hasMany(
            Staff::class,
            'office_id'
        );
    }

    public function divisions(): HasMany
    {
        return $this->hasMany(
            Division::class,
            'office_id'
        );
    }

    public function units(): HasMany
    {
        return $this->hasMany(
            Unit::class,
            'office_id'
        );
    }

    public function locations(): HasMany
    {
        return $this->hasMany(
            OfficeLocation::class,
            'office_id'
        );
    }
}
