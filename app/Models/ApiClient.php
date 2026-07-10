<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiClient extends Model
{
  use HasFactory;

  protected $guarded = ['id'];

  protected $casts = [
    'allowed_ips' => 'array',
    'last_used_at' => 'datetime',
    'revoked_at' => 'datetime',
  ];

  public static function issueToken(): string
  {
    return 'uplift_' . Str::random(64);
  }

  public static function hashToken(string $token): string
  {
    return hash('sha256', $token);
  }

  public function isAllowedIp(?string $ip): bool
  {
    $allowedIps = array_filter($this->allowed_ips ?? []);

    return $ip != null && in_array($ip, $allowedIps, true);
  }

  public function isActive(): bool
  {
    return $this->revoked_at == null;
  }
}
