<?php

namespace App\Rules;

use App\Models\Holiday;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoSuspensionOverlap implements ValidationRule
{
  /**
   * Indicates whether the rule should be implicit.
   *
   * @var bool
   */
  public $implicit = true;
  private $start_date = null;
  private $ignore_id = null;

  public function __construct($start_date, $ignore_id = null)
  {
    $this->start_date = $start_date;
    $this->ignore_id = $ignore_id;
  }

  /**
   * Run the validation rule.
   *
   * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    if (empty($this->start_date) || empty($value)) {
    } else {
      $query = Holiday::query();
      $start   = Carbon::parse($this->start_date);
      $end = Carbon::parse($value);

      $start1990 = Carbon::parse($this->start_date)->year(1900);
      $end1990 = Carbon::parse($value)->year(1900);

      if ($this->ignore_id) {
        $query->where('id', '!=', $this->ignore_id);
      }

      $hasOverlap = $query->where(function ($query) use ($start, $end) {
        $query->where('start', '<=', $end)
          ->where('end', '>=', $start);
      })->orWhere(function ($query) use ($start1990, $end1990) {
        $query->where('repeat_every_year', 'Y')
          ->where('start', '<=', $end1990)
          ->where('end', '>=', $start1990);
      })->exists();

      if ($hasOverlap) {
        $fail('The selected period overlaps an existing suspension/holiday.');
      }
    }
  }
}
