<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EmailValidParameter implements ValidationRule
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
    $allowedPatterns = ['[@firstname]', '[@lastname]', '[@designation]'];
    preg_match_all('/\[@[a-zA-Z0-9_]+\]/', $value, $matches);
    $foundPatterns = $matches[0];
    if (!empty($foundPatterns)) {
      $invalidPatterns = array_diff($foundPatterns, $allowedPatterns);
      if (!empty($invalidPatterns)) {
        $fail('Only the following parameters are allowed: [@firstname], [@lastname], and [@designation]');
      }
    }
  }
}
