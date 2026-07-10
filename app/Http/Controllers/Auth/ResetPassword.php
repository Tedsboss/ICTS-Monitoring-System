<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Notifications\Notifiable;
use App\Notifications\RecoverPassword;

use App\Models\User;
use App\Rules\ReCaptcha;
use Illuminate\Support\Facades\Notification;

class ResetPassword extends Controller
{
  use Notifiable;

  public function show()
  {
    return view('auth.reset-password');
  }

  public function routeNotificationForMail()
  {
    return request()->email;
  }

  public function send(Request $request)
  {
    $email = $request->validate([
      'email' => ['required'],
      'g-recaptcha-response' => ReCaptcha::rules('reset', 0.5)
    ]);
    $user = User::where('email', $email)->first();
    if ($user) {
      // $this->notify(new RecoverPassword($user->id));
      // Notification::route('mail', config('mail.email_prefix') . $user->email)->notify(new RecoverPassword($user->id));
      Notification::route('mail', $user->email)->notify(new RecoverPassword($user->id));
      return back()->with('succes', 'If the provided email exists in our system, a reset link will be sent shortly.');
    } else {
      return back()->with('succes', 'If the provided email exists in our system, a reset link will be sent shortly.');
    }
  }
}
