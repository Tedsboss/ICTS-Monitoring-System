<?php

namespace App\Http\Middleware;

use App\Models\RestrictedIp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRestrictedIps
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    if (RestrictedIp::where('ipaddress', $request->ip())->where('status', 1)->exists()) {
      return redirect()->route('blocked');
    }
    return $next($request);
  }
}
