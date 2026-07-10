<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfEmptyHtml implements ValidationRule
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
    if (strip_tags($value) == '') {
      $fail('The :attribute cannot be empty.');
    }
  }
}
