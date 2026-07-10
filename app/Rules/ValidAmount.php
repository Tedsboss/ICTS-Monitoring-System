<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidAmount implements ValidationRule
{
  /**
   * Indicates whether the rule should be implicit.
   *
   * @var bool
   */
  public $implicit = true;
  private $min_value = 0;
  private $max_value = 999999999999999.99;

  public function __construct(float $min_value = 0, float $max_value = 999999999999999.99)
  {
    $this->min_value = $min_value;
    $this->max_value = $max_value;
  }

  /**
   * Run the validation rule.
   *
   * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    $value = str_replace(',', '', $value);
    if ($value == null || $value == '') {
    } else {
      if (is_numeric($value) && preg_match('/^\d+(\.\d{1,2})?$/', $value)) {
        if ($value < $this->min_value) {
          $fail('The :attribute must be at least ' . number_format($this->min_value, 2, '.', ',') . '.');
        } elseif ($value > $this->max_value) {
          $fail('The :attribute may not be greater than ' . number_format($this->max_value, 2, '.', ',') . '.');
        }
      } else {
        $fail('The :attribute must be a valid amount with up to two decimal places.');
      }
    }
  }
}
