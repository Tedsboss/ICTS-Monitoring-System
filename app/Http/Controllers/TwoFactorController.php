<?php

namespace App\Http\Controllers;

use App\Http\Requests\TwoFactorRequest;
use App\Models\TrustedDevice;
use App\Models\User;
use App\Notifications\TwoFactorNotification;
use App\Traits\GenerateLogs;
use App\Traits\TracksHistoryTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Stevebauman\Location\Facades\Location;

class TwoFactorController extends Controller
{
  use GenerateLogs;
  use TracksHistoryTrait;
  /**
   * Display a listing of the resource.
   */
  public function show()
  {
    $remaining = 0;
    $user = User::where('id', auth()->id())->first();
    if ($user->twofactorexpiredat == null && session()->has('error') == false) {
      $otp_code = strtoupper(Str::random(6));
      $user->updateQuietly([
        'twofactorcode' => $otp_code,
        'twofactorexpiredat' => now()->addMinutes(config('auth.two_factor.expiry_minutes', 5)),
      ]);
      // Log::info('Two Factor Auth Sent', ['user_id' => auth()->id(), 'user_email' => $user->email]);
      // Notification::route('mail', config('mail.email_prefix') . $user->email)->notify(new TwoFactorNotification($user, [guessDeviceName(request()->userAgent()), request()->ip()]));
      Notification::route('mail', $user->email)->notify(new TwoFactorNotification($user, [guessDeviceName(request()->userAgent()), request()->ip()]));
      $remaining = config('auth.two_factor.expiry_minutes', 5) * 60; // 5 minutes
    } else {
      if (Carbon::parse($user->twofactorexpiredat)->isPast()) {
        $remaining = 0;
      } else {
        $remaining = Carbon::parse($user->twofactorexpiredat)->diffInSeconds(Carbon::now());
      }
    }
    return view('auth.twofactor', compact('remaining'));
  }

  public function verify(TwoFactorRequest $request, User $user)
  {
    $user = User::where('id', auth()->id())->first();
    $user->updateQuietly([
      'twofactorcode' => null,
      'twofactorexpiredat' => null,
    ]);

    if ($request->remember_device == 1) {
      $selector  = Str::random(24);
      $validator = bin2hex(random_bytes(32));  // keep this only in cookie

      $location = null;
      try {
        $location = Location::get();
      } catch (Exception $e) {
      }

      $trusted_device = new TrustedDevice();
      $trusted_device->user_id = auth()->id();
      $trusted_device->selector = $selector;
      $trusted_device->validator_hash = Hash::make($validator);
      $trusted_device->device_name = guessDeviceName($request->userAgent());
      $trusted_device->user_agent = (string) $request->userAgent();
      $trusted_device->ip = $request->ip();

      $trusted_device->location_country = optional($location)->countryCode;
      $trusted_device->location_region = optional($location)->regionName;
      $trusted_device->location_city = optional($location)->cityName;

      $trusted_device->last_seen_at = now();
      $trusted_device->expires_at = now()->addDays(config('auth.two_factor.remember_days', 30));
      $trusted_device->save();

      $cookieValue = base64_encode($selector . ':' . $validator);
      $minutes = 60 * 24 * config('auth.two_factor.remember_days', 30);
      Cookie::queue(cookie(
        'trusted_device',               // cookie name
        $cookieValue,
        $minutes,
        '/',
        null,
        $request->isSecure(),
        true,
        false,
        'Lax' // HttpOnly, SameSite=Lax
      ));

      session(['two_factor_current' => $trusted_device->id]);
    }

    session(['two_factor_passed' => true]);
    return redirect()->intended(route('home'));
  }

  public function resend()
  {
    $user = User::where('id', auth()->id())->first();
    $user->updateQuietly([
      'twofactorcode' => null,
      'twofactorexpiredat' => null,
    ]);
    return redirect()->route('2fa.challenge.show')->with('succes', 'OTP sent successfully');
  }



  public function revoke(TrustedDevice $trusted_device)
  {
    if ($trusted_device->user_id == auth()->id()) {
      $trusted_device->revoked_at = now();
      $trusted_device->save();

      $this->addSystemLogs("Revoked trusted device: " . $trusted_device->device_name . ' - ' . $trusted_device->ip, auth()->id(), auth()->user()->email, request()->getClientIp(true), 'trusted_devices', $trusted_device->id);
    }
    return back()->with('succes', 'Device succesfully revoked');
  }
}
