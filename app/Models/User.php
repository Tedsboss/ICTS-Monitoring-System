<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $appends = [
        'avatar_url',
        'full_name',
    ];

    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'gender',
        'birthday',
        'email',
        'agency_id',
        'position_id',
        'division_id',
        'staff_id',
        'location',
        'phone',
        'password',
        'role_id',
        'avatar',
        'twofactorcode',
        'twofactorexpiredat',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'birthday' => 'date',
        'email_verified_at' => 'datetime',
        'twofactorexpiredat' => 'datetime',
        'agency_id' => 'integer',
        'position_id' => 'integer',
        'division_id' => 'integer',
        'staff_id' => 'integer',
        'role_id' => 'integer',
    ];

    // Always hash the password when it is updated.
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = bcrypt($value);
    }

    // Avatar URL.
    public function avatarUrl(): string
    {
        if (
            empty($this->avatar)
            || ! Storage::disk('avatars')->exists($this->avatar)
        ) {
            return '/assets/img/default-avatar.jpg';
        }

        return '/avatars/' . ltrim($this->avatar, '/');
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatarUrl();
    }

    // Full display name.
    public function getFullNameAttribute(): string
    {
        $parts = [
            trim((string) $this->firstname),

            $this->middlename
                ? mb_substr(trim((string) $this->middlename), 0, 1) . '.'
                : null,

            trim((string) $this->lastname),
        ];

        return collect($parts)
            ->filter(fn ($value) => filled($value))
            ->implode(' ');
    }

    // DIREK access role.
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isSuperAdmin(): bool
    {
        return (int) $this->role_id === 1;
    }

    // User belongs to a DepDev Staff/Office.
    public function isDepDevStaff(): bool
    {
        return Agency::isDepDevId($this->agency_id)
            && ! empty($this->staff_id);
    }

    // DIREK Staff/Office access scope.
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    // Organizational metadata.
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    // Organizational position/designation.
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function position_name()
    {
        return optional($this->position)->name;
    }

    public function staff_name()
    {
        return optional($this->staff)->name;
    }

    // User agency.
    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    // Existing legacy/application relationships.
    public function histories()
    {
        return $this->morphMany(History::class, 'model');
    }

    public function trusted_devices()
    {
        return $this->hasMany(TrustedDevice::class);
    }

    public function inquiries()
    {
        return $this->hasMany(Inquiry::class);
    }

    public function formSubmissions()
    {
        return $this->hasMany(FormSubmission::class);
    }
}
