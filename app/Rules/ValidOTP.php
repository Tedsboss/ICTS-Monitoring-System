<?php

namespace App\Rules;

use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidOTP implements ValidationRule
{
  /**
   * Indicates whether the rule should be implicit.
   *
   * @var bool
   */
  public $implicit = true;

  /**
   * Run the validation rule.
   *
   * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    $user = User::where('id', auth()->id())->first();
    if ($user->twofactorcode === $value) {
      if (Carbon::parse($user->twofactorexpiredat)->addMinutes(5)->isPast()) {
        $fail('The :attribute has expired. Please click the resend OTP link');
      }
    } else {
      $fail('The :attribute is invalid.');
    }
  }
}
