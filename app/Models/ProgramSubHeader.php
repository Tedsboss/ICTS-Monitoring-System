<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramSubHeader extends Model
{
    use HasFactory;

    protected $table = 'program_sub_headers';

    protected $fillable = [
        'header_id',
        'sub_header',
        'sub_code',
    ];

    protected $casts = [
        'header_id' => 'integer',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(
            ProgramHeader::class,
            'header_id'
        );
    }

    public function programs(): HasMany
    {
        return $this->hasMany(
            Program::class,
            'sub_header_id'
        );
    }
}
