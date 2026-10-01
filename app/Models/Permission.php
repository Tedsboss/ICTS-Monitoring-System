<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'module_id',
        'order',
    ];

    protected $casts = [
        'module_id' => 'integer',
        'order' => 'integer',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(
            Module::class,
            'module_id'
        );
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'permission_role',
            'permission_id',
            'role_id'
        );
    }
}
