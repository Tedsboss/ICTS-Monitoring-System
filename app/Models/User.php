<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
  use HasApiTokens, HasFactory, Notifiable;
  protected $appends = ['avatar_url', 'full_name'];
  public $timestamps = ['twofactorexpiredat'];

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
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
    'position_id',
    'division_id',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
  ];

  /**
   * Always encrypt the password when it is updated.
   *
   * @param $value
   * @return string
   */
  public function setPasswordAttribute($value)
  {
    $this->attributes['password'] = bcrypt($value);
  }

  public function avatarUrl()
  {
    if ($this->avatar == null || $this->avatar == '' || !Storage::disk('avatars')->exists($this->avatar)) {
      return '/assets/img/default-avatar.jpg';
    }

    return '/avatars/' . ltrim($this->avatar, '/');
  }

  public function getAvatarUrlAttribute()
  {
    return $this->avatarUrl();
  }

  public function role()
  {
    return $this->belongsTo(Role::class);
  }

  public function isSuperAdmin()
  {
    return $this->role_id === 1;
  }

  public function isDepDevStaff(): bool
  {
    return (int) $this->agency_id === Agency::DEPDEV_ID && !empty($this->staff_id);
  }

  // public function isAdmin()
  // {
  //   return $this->role_id === 2;
  // }

  // public function isValidator()
  // {
  //   return $this->role_id === 3;
  // }

  // public function isMember()
  // {
  //   return $this->role_id === 4;
  // }

  public function staff()
  {
    return $this->belongsTo(Staff::class);
  }

  public function division()
  {
    return $this->belongsTo(Division::class);
  }

  public function position()
  {
    return $this->belongsTo(Position::class);
  }

  public function position_name()
  {
    if ($this->position()->first() != null) {
      return $this->position()->first()->name;
    }
    return null;
  }

  public function staff_name()
  {
    if ($this->staff()->first() != null) {
      return $this->staff()->first()->name;
    }
    return null;
  }

  public function histories()
  {
    return $this->morphMany(History::class, 'model');
  }
  
  public function getFullNameAttribute()
  {
    // return $this->firstname . " " . $this->lastname;
    return $this->firstname . ' ' . ($this->middlename ? $this->middlename[0] . '. ' : '') . $this->lastname;
  }

  public function trusted_devices()
  {
    return $this->hasMany(TrustedDevice::class);
  }

  public function inquiries()
  {
    return $this->hasMany(Inquiry::class);
  }

  public function agency()
  {
    return $this->belongsTo(Agency::class, 'agency_id');
  }

  public function formSubmissions()
  {
    return $this->hasMany(FormSubmission::class);
  }
}
