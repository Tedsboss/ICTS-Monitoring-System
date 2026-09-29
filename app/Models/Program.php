<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasFactory;

    protected $table = 'programs';

    protected $fillable = [
        'sub_header_id',
        'program',
    ];

    protected $casts = [
        'sub_header_id' => 'integer',
    ];

    public function subHeader(): BelongsTo
    {
        return $this->belongsTo(ProgramSubHeader::class, 'sub_header_id');
    }

    public function expenditures(): HasMany
    {
        return $this->hasMany(Expenditure::class, 'program_id');
    }
}