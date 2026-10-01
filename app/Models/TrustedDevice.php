<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrustedDevice extends Model
{
    use HasFactory;

    protected $appends = ['status'];

    public function getStatusAttribute(): string
    {
        if ($this->revoked_at !== null) {
            return 'Revoked';
        }

        if ($this->expires_at !== null && Carbon::parse($this->expires_at)->isPast()) {
            return 'Expired';
        }

        return 'Active';
    }
}
