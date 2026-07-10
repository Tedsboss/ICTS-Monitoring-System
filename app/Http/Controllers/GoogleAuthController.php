<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;

class GoogleAuthController extends Controller
{
  /**
   * Redirect the user to Google’s OAuth page.
   */
  public function redirect()
  {
    return Socialite::driver('google')->redirect();
  }

  /**
   * Handle the callback from Google.
   */
  public function callback()
  {
    try {
      // Get the user information from Google
      $user = Socialite::driver('google')->user();
    } catch (Throwable $e) {
      return redirect('/')->with('error', 'Google authentication failed.');
    }

    // Check if the user already exists in the database
    $existingUser = User::where('email', $user->email)->first();

    if ($existingUser) {
      session(['provider_name' => 'Google']);
      Auth::login($existingUser, true);

      session(['user_settings' => [
        'class_theme' => auth()->user()->enabledark == 'Y' ? 'dark' : '',
        'hide_dashboard_charts' => auth()->user()->autohidecharts ?? 'N',
      ]]);
    } else {
      return redirect()->route('login.unknownuser');
    }

    return redirect('/');
  }
}
