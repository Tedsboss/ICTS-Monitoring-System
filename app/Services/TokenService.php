<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TokenService
{
  public function generatePipolAccessToken()
  {
    $tokenResponse = Http::asForm()->post(config('app.pipol.url') . '/oauth/token', [
      'grant_type' => 'client_credentials',
      'client_id' => config('app.pipol.client_id'),
      'client_secret' => config('app.pipol.client_secret'),
      'scope' => '',
    ]);

    if ($tokenResponse->successful()) {
      $accessToken = $tokenResponse->json()['access_token'];
      $expiresAt = now()->addSeconds($tokenResponse->json()['expires_in'])->addHours(24);
      Cache::put('pipol_access_token', [
        'access_token' => $accessToken,
        'expires_at' => $expiresAt
      ], $expiresAt);
      return $accessToken;
    } else {
      abort(500, 'Failed to obtain access token.');
    }
  }
}
