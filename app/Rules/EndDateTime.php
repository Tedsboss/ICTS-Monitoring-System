<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EndDateTime implements ValidationRule
{
  /**
   * Indicates whether the rule should be implicit.
   *
   * @var bool
   */
  public $implicit = true;
  private $type = null;
  private $whole_day = null;
  private $start_date_time = null;

  public function __construct($type, $whole_day, $start_date_time)
  {
    $this->type = $type;
    $this->whole_day = $whole_day;
    $this->start_date_time = $start_date_time;
  }

  /**
   * Run the validation rule.
   *
   * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    $endPart = explode('T', $value);
    $startPart = explode('T', $this->start_date_time);
    if ($this->whole_day == 'Y' || $this->type == 'Holiday') {
      if ($endPart[1] == '23:59:59') {
      } else {
        $fail('The :attribute time must be at 11:59:59 PM');
      }
    } else {
      $startDateTime = new \DateTime($this->start_date_time);
      $endDateTime = new \DateTime($value);
      if ($endDateTime <= $startDateTime) {
        $fail('The :attribute must be greater than the start date');
      } else {
        if ($endPart[0] != $startPart[0]) {
          $fail('The :attribute date must be the same as the start date');
        }
      }
    }
  }
}
