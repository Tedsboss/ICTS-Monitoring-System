<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class History extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'systemlog_id',
        'user_id',
        'reference_table',
        'model_type',
        'model_id',
        'body',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
