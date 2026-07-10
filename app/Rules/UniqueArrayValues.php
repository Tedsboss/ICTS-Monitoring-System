<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueArrayValues implements ValidationRule
{
  /**
   * Indicates whether the rule should be implicit.
   *
   * @var bool
   */
  public $implicit = true;
  private $array_values = [];
  private $field_name = '';

  public function __construct($array_values, $field_name)
  {
    $this->array_values = $array_values;
    $this->field_name = $field_name;
  }

  /**
   * Run the validation rule.
   *
   * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    $ids = collect($this->array_values)->pluck($this->field_name)->toArray();
    if (count(array_filter($ids, fn($item) => $item === $value)) > 1) {
      $fail('The :attribute field must be unique.');
    }
  }
}
