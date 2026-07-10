<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CompareDefault implements ValidationRule
{
  /**
   * Indicates whether the rule should be implicit.
   *
   * @var bool
   */
  public $implicit = true;
  private $string = null;
  private $can_update_focal = null;

  public function __construct($string, $can_update_focal)
  {
    $this->string = $string;
    $this->can_update_focal = $can_update_focal;
  }

  /**
   * Run the validation rule.
   *
   * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    if ($this->can_update_focal == false && $value != $this->string) {
      $fail('The :attribute is invalid');
    }
  }
}
