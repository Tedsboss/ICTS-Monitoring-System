<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StartDateTime implements ValidationRule
{
  /**
   * Indicates whether the rule should be implicit.
   *
   * @var bool
   */
  public $implicit = true;
  private $type = null;
  private $whole_day = null;

  public function __construct($type, $whole_day)
  {
    $this->type = $type;
    $this->whole_day = $whole_day;
  }

  /**
   * Run the validation rule.
   *
   * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    if ($this->whole_day == 'Y' || $this->type == 'Holiday') {
      $timePart = explode('T', $value);
      if ($timePart[1] == '00:00:00' || $timePart[1] == '00:00') {
      } else {
        $fail('The :attribute time must be at 12:00:00 AM');
      }
    }
  }
}
