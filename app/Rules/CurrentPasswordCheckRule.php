<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;

class CurrentPasswordCheckRule implements ValidationRule
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
    if (Hash::check($value, auth()->user()->password)) {
      $fail('The current password field does not match your password');
    }
  }
}
