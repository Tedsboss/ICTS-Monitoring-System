<?php

namespace App\Http\Requests;

use App\Rules\ReCaptcha;
use App\Rules\ValidOTP;
use Illuminate\Foundation\Http\FormRequest;

class TwoFactorRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return auth()->check();
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'otp_code' => ['required', 'string', 'size:6', new ValidOTP],
      'remember_device' => ['nullable', 'in:0,1'],
      'g-recaptcha-response' => ReCaptcha::rules('twofactor', 0.5)
    ];
  }

  public function attributes(): array
  {
    return [
      'otp_code' => 'OTP password',
      'remember_device' => 'remember this device',
      'g-recaptcha-response' => 'reCAPTCHA',
    ];
  }

  public function messages(): array
  {
    return [
      'g-recaptcha-response.required' => 'The reCAPTCHA verification failed. Please try again.',
    ];
  }
}
