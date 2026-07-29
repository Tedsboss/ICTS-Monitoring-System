<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;
use App\Rules\ReCaptcha;
use App\Traits\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class LoginController extends Controller
{
  use ThrottlesLogins;

  protected $maxAttempts = 3; // Default is 5
  protected $decayMinutes = 5; // Default is 1
  /**
   * Display login page.
   *
   * @return Renderable
   */
  public function show()
  {
    return view('auth.login');
  }

  public function login(Request $request)
  {
    $credentials = $request->validate([
      'email' => ['required', 'email'],
      'password' => ['required'],
      'g-recaptcha-response' => ReCaptcha::rules('login', 0.5)
    ]);

    if (method_exists($this, 'hasTooManyLoginAttempts') && $this->hasTooManyLoginAttempts($request)) {
      $this->fireLockoutEvent($request);
      return $this->sendLockoutResponse($request);
    }

    if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
      session(['provider_name' => null]);

      $this->clearLoginAttempts($request);
      $request->session()->regenerate();

      session(['user_settings' => [
        'class_theme' => auth()->user()->enabledark == 'Y' ? 'dark' : '',
      ]]);

      return redirect()->intended(route('home'));
    } else {
      $this->incrementLoginAttempts($request);
    }

    return back()->withErrors([
      'email' => 'The provided credentials do not match our records.',
    ]);
  }

  public function logout(Request $request)
  {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
  }

  public function unknownuser()
  {
    return view('errors.unknownuser');
  }

  public function showmyneda()
  {
    return view('auth.myneda');
  }

  public function loginmyneda(Request $request)
  {
    $request->validate([
      'username_ncsois' => ['required'],
      'password_ncsois' => ['required'],
    ]);
    $options = [];
    if (env('APP_ENV') == 'local') {
      $options = ['verify' => false];
    }
    $response = Http::accept('application/json')->withOptions($options)->post(env('NCSOIS_API_LINK') . '/auth/login', ['username' => $request->username_ncsois, 'password' => $request->password_ncsois]);
    if ($response->status() == 200) {
      $tmp_user = json_decode($response->getBody());
      if ($tmp_user != null && $tmp_user->user != null && $tmp_user->user->email != null) {
        $user = User::where('email', $tmp_user->user->email)->first();
        if ($user == null) {
          return redirect()->route('login.unknownuser');
        } else {
          Auth::login($user, true);
          return redirect()->route('home');
          // if (Auth::attempt(['email' => $user->email, 'password' => $user->password])) {
          //   $request->session()->regenerate();
          //   return redirect()->intended('/');
          // }
        }
      } else {
        return back()->withErrors([
          'message' => 'Server error',
        ])->withInput($request->all());
      }
    } else {
      $error = json_decode($response->getBody());
      // if ($error?->error?->message != null) {
      if ($error != null && $error->error != null && $error->error->message != null) {
        return back()->withErrors([
          'message' => $error->error->message,
        ])->withInput($request->all());
      } else {
        return back()->withErrors([
          'message' => 'Server error',
        ])->withInput($request->all());
      }
    }
  }
}
