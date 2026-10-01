<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const SUPER_ADMIN_ROLE_ID = 1;
    public const ADMIN_ROLE_IDS = [1, 29];

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

    public function setPasswordAttribute($value): void
    {
        $value = (string) $value;

        $this->attributes['password'] = Hash::needsRehash($value)
            ? Hash::make($value)
            : $value;
    }

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

    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function isSuperAdmin(): bool
    {
        return (int) $this->role_id === self::SUPER_ADMIN_ROLE_ID;
    }

    public function isAdministrator(): bool
    {
        return in_array(
            (int) $this->role_id,
            self::ADMIN_ROLE_IDS,
            true
        );
    }

    public function isDepDevStaff(): bool
    {
        return Agency::isDepDevId($this->agency_id)
            && ! empty($this->staff_id);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function position_name(): ?string
    {
        return optional($this->position)->name;
    }

    public function staff_name(): ?string
    {
        return optional($this->staff)->name;
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    public function histories(): MorphMany
    {
        return $this->morphMany(History::class, 'model');
    }

    public function trusted_devices(): HasMany
    {
        return $this->hasMany(TrustedDevice::class);
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function formSubmissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }
}
