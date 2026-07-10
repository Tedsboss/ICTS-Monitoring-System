<?php

namespace App\Http\Middleware;

use App\Models\RestrictedIp;
use App\Traits\GenerateLogs;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class MessageIpThrottle
{
  use GenerateLogs;
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    $key = 'icc_send_inquiries_' . $request->ip();
    $maxAttempts = 10;
    $decayMinutes = 60;

    if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
      $restricted_ip = new RestrictedIp();
      $restricted_ip->ipaddress = $request->ip();
      $restricted_ip->route = 'inquiry';
      $restricted_ip->content = json_encode($request->all());
      $restricted_ip->status = 1;
      $restricted_ip->updated_by = null;
      $restricted_ip->save();

      $this->addSystemLogs("IP blocked due to suspected spam inquiries: " . $restricted_ip->ipaddress, null, null, request()->getClientIp(true), 'restricted_ips', $restricted_ip->id);
      RateLimiter::clear($key);

      return redirect()->route('blocked');
    }

    $response = $next($request);

    if ($response->status() >= 200 && $response->status() < 300) {
      RateLimiter::hit($key, $decayMinutes * 60);
    } else {
      RateLimiter::clear($key);
    }

    return $response;
  }
}
