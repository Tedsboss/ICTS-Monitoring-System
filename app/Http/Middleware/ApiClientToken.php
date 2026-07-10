<?php

namespace App\Http\Middleware;

use App\Models\ApiClient;
use Closure;
use Illuminate\Http\Request;

class ApiClientToken
{
  public function handle(Request $request, Closure $next)
  {
    $token = $this->requestToken($request);

    if (empty($token)) {
      return response()->json(['message' => 'Missing API token. Send Authorization: Bearer <token> or X-API-Token.'], 401);
    }

    if ($this->matchesEnvToken($token)) {
      if (!$this->isEnvAllowedIp($request->ip())) {
        return response()->json(['message' => 'IP address is not allowed for this API token.'], 403);
      }

      $request->attributes->set('api_client', 'env');

      return $next($request);
    }

    $client = ApiClient::where('token_hash', ApiClient::hashToken($token))->first();

    if ($client == null || !$client->isActive()) {
      return response()->json(['message' => 'Invalid API token.'], 401);
    }

    if (!$client->isAllowedIp($request->ip())) {
      return response()->json(['message' => 'IP address is not allowed for this API token.'], 403);
    }

    $client->forceFill(['last_used_at' => now()])->save();
    $request->attributes->set('api_client', $client);

    return $next($request);
  }

  private function requestToken(Request $request): ?string
  {
    return $request->bearerToken() ?: $request->header('X-API-Token');
  }

  private function matchesEnvToken(string $token): bool
  {
    $envToken = (string) config('services.approved_submissions.token');

    return $envToken !== '' && hash_equals($envToken, $token);
  }

  private function isEnvAllowedIp(?string $ip): bool
  {
    $allowedIps = collect(explode(',', (string) config('services.approved_submissions.allowed_ips')))
      ->map(fn($allowedIp) => trim($allowedIp))
      ->filter()
      ->values()
      ->all();

    return $ip != null && in_array($ip, $allowedIps, true);
  }
}
