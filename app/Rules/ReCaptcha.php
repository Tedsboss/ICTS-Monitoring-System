<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Throwable;
use Carbon\Carbon;

class ReCaptcha implements ValidationRule
{
  /**
   * Indicates whether the rule should be implicit.
   *
   * @var bool
   */
  public $implicit = true;
  private $expectedAction = null;
  private $minScore = null;
  private $maxAgeSeconds = null;

  public function __construct($expectedAction = 'submit', $minScore = 0.5, $maxAgeSeconds = 3600)
  {
    $this->expectedAction = $expectedAction;
    $this->minScore = $minScore;
    $this->maxAgeSeconds = $maxAgeSeconds;
  }

  public static function rules($expectedAction = 'submit', $minScore = 0.5, $maxAgeSeconds = 3600): array
  {
    if (!app()->environment('production')) {
      return ['nullable'];
    }

    return ['required', new self($expectedAction, $minScore, $maxAgeSeconds)];
  }

  /**
   * Run the validation rule.
   *
   * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    if (!app()->environment('production')) {
      return;
    }

    $genericError = 'The reCAPTCHA verification failed. Please try again.';
    if (empty($value) || !is_string($value)) {
      $fail('reCAPTCHA token is missing.');
      // $fail($genericError);
    }
    try {
      $response = Http::asForm()
        ->timeout(5)
        ->retry(2, 200) // quick retry
        ->post(config('recaptchav3.origin') . '/api/siteverify', [
          'secret'   => config('recaptchav3.secret'),
          'response' => $value,
          'remoteip' => request()->ip(),
        ]);

      if (!$response->ok()) {
        $fail('reCAPTCHA verification failed (network). Please try again.');
        // $fail($genericError);
      }

      $data = $response->json();

      if (empty($data['success'])) {
        $fail('Unknow reCAPTCHA error');
        // $fail($genericError);
      }

      if (!empty($data['action']) && $this->expectedAction !== '' && $data['action'] !== $this->expectedAction) {
        $fail('reCAPTCHA action mismatch.');
        // $fail($genericError);
      }


      if (isset($data['score']) && $data['score'] < $this->minScore) {
        $fail('reCAPTCHA score too low. Please try again.');
        // $fail($genericError);
      }

      if (!empty($data['challenge_ts'])) {
        $ts = strtotime($data['challenge_ts']);
        // dd((time() - $ts), Carbon::parse($ts), Carbon::now());
        if ($ts !== false && (time() - $ts) > $this->maxAgeSeconds) {
          $fail('reCAPTCHA token expired. Please submit again.');
          // $fail($genericError);
        }
      }
    } catch (Throwable $e) {
      $fail($e->getMessage());
      // $fail($genericError);
    }
  }
}
