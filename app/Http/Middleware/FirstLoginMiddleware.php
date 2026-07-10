<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FirstLoginMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    if (auth()->check()) {
      if (auth()->user()->first_login == 'N') {
        return $next($request);
      }
    } else {
      return redirect()->route('login');
    }
    return redirect()->route('user-profile')->with('info', 'As a first-time user, you must update your profile, including your password, before you can use the system');
  }
}
