<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramHeader extends Model
{
    use HasFactory;

    protected $table = 'program_headers';

    protected $fillable = [
        'header',
    ];

    public function subHeaders(): HasMany
    {
        return $this->hasMany(ProgramSubHeader::class, 'header_id');
    }
}