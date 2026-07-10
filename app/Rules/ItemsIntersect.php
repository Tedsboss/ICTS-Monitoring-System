<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ItemsIntersect implements ValidationRule
{
  /**
   * Indicates whether the rule should be implicit.
   *
   * @var bool
   */
  public $implicit = true;
  private $array1 = [];

  public function __construct(array $array1)
  {
    $this->array1 = $array1;
  }

  /**
   * Run the validation rule.
   *
   * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    $array2 = $value ?? [];
    $overlap = array_intersect($this->array1, $array2);
    if (!empty($overlap)) {
      $fail('Some :attribute have overlapping items.');
    }
  }
}
