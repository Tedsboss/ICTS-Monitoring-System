<?php

namespace App\Http\Middleware;

use App\Models\RestrictedIp;
use App\Traits\GenerateLogs;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class LoginIpThrottle
{
  use GenerateLogs;
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    $key = 'icc_login_attempts_' . $request->ip();
    $maxAttempts = 10;
    $decayMinutes = 60;

    if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
      $restricted_ip = new RestrictedIp();
      $restricted_ip->ipaddress = $request->ip();
      $restricted_ip->route = 'login';
      $restricted_ip->content = json_encode($request->all());
      $restricted_ip->status = 1;
      $restricted_ip->updated_by = null;
      $restricted_ip->save();

      $this->addSystemLogs("IP blocked due to multiple failed login attempts: " . $restricted_ip->ipaddress, null, null, request()->getClientIp(true), 'restricted_ips', $restricted_ip->id);
      RateLimiter::clear($key);

      return redirect()->route('blocked');
    }

    $response = $next($request);

    if (auth()->check()) {
      RateLimiter::clear($key);
    } else {
      RateLimiter::hit($key, $decayMinutes * 60);
    }

    return $response;
  }
}
