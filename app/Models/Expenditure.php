<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expenditure extends Model
{
    use HasFactory;

    protected $table = 'expenditures';

    protected $fillable = [
        'program_id',
        'expenditure',
        'prexc',
        'is_ops',
    ];

    protected $casts = [
        'program_id' => 'integer',
        'is_ops' => 'boolean',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }
}