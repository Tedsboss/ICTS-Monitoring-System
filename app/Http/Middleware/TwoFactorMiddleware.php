<?php

namespace App\Http\Middleware;

use App\Models\TrustedDevice;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    $user = auth()->user();
    if (auth()->check()) {
      if ($user->twofactor == 'Y') {
        if (session('two_factor_passed') === true) {
          $logout_message = 'Unknown error. Please log in again to continue';
          if (session()->has('two_factor_current')) {
            $trusted_device = TrustedDevice::where('id', session('two_factor_current'))->first();
            if ($trusted_device != null) {
              if ($trusted_device->revoked_at == null) {
                $trusted_device->last_seen_at = now();
                $trusted_device->save();
                return $next($request);
              } else {
                $logout_message = 'You\'ve been logged out because this session was revoked by an administrator or from another device. Please log in again to continue.';
              }
            } else {
              $logout_message = 'You\'ve been logged out due to update in the system. Please log in again to continue';
            }
          } else {
            $logout_message = 'You\'ve been logged out due to recent account changes. Please log in again to continue';
          }
          Auth::logout();
          $request->session()->invalidate();
          $request->session()->regenerateToken();
          return redirect('/login')->with('error', $logout_message);
        } else {
          if ($cookie = $request->cookie('trusted_device')) {
            $decoded = base64_decode($cookie, true);
            if ($decoded && str_contains($decoded, ':')) {
              [$selector, $validator] = explode(':', $decoded, 2);

              $trusted_device = TrustedDevice::where('user_id', $user->id)
                ->where('selector', $selector)
                ->whereNull('revoked_at')
                ->where('expires_at', '>', now())
                ->first();

              if ($trusted_device && Hash::check($validator, $trusted_device->validator_hash)) {
                // renew cookie to prevent reuse
                $newValidator = bin2hex(random_bytes(32));

                $trusted_device->validator_hash = Hash::make($newValidator);
                $trusted_device->last_seen_at = now();
                $trusted_device->ip = $request->ip();
                $trusted_device->save();


                Cookie::queue(cookie(
                  'trusted_device',
                  base64_encode($selector . ':' . $newValidator),
                  60 * 24 * config('auth.two_factor.remember_days', 30),
                  '/',
                  null,
                  $request->isSecure(),
                  true,
                  false,
                  'Lax'
                ));

                session(['two_factor_current' => $trusted_device->id]);
                session(['two_factor_passed' => true]);
                return $next($request);
              }
            }
          }
        }
      } else {
        session(['two_factor_passed' => true]);
        return $next($request);
      }
    } else {
      return redirect()->route('login');
    }
    return redirect()->route('2fa.challenge.show');
  }
}
