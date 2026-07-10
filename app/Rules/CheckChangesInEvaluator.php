<?php

namespace App\Rules;

use App\Models\Submission;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckChangesInEvaluator implements ValidationRule
{
  /**
   * Indicates whether the rule should be implicit.
   *
   * @var bool
   */
  public $implicit = true;
  private $submission;
  private $sub_eval_dco = [];
  private $sub_eval_dro = [];

  public function __construct(Submission $submission, array $sub_eval_dco, array $sub_eval_dro)
  {
    $this->submission = $submission;
    $this->sub_eval_dco = $sub_eval_dco;
    $this->sub_eval_dro = $sub_eval_dro;
  }

  /**
   * Run the validation rule.
   *
   * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    $existing_main_evaluators = $this->submission->main_evaluator_divisions->pluck('id')->toArray();
    $existing_sub_evaluators_dco = $this->submission->sub_evaluator_dco_divisions->pluck('id')->toArray();
    $existing_sub_evaluators_dro = $this->submission->sub_evaluator_dro_divisions->pluck('id')->toArray();

    $proposed_main_evaluators = array_map('intval', $value);
    $proposed_sub_evaluators_dco = array_map('intval', $this->sub_eval_dco);
    $proposed_sub_evaluators_dro = array_map('intval', $this->sub_eval_dro);
    sort($proposed_main_evaluators);
    sort($proposed_sub_evaluators_dco);
    sort($proposed_sub_evaluators_dro);

    if ($proposed_main_evaluators === $existing_main_evaluators) {
      if ($proposed_sub_evaluators_dco === $existing_sub_evaluators_dco) {
        if ($proposed_sub_evaluators_dro === $existing_sub_evaluators_dro) {
          $fail('No changes detected in evaluator assignments.');
        }
      }
    }
  }
}
