<?php

namespace App\Http\Requests;

use App\Rules\PurifyHtml;
use App\Rules\ReCaptcha;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InquiryRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'firstname' => [Rule::requiredIf(auth()->guest()), 'string', 'min:1', 'max:255'],
      'lastname' => [Rule::requiredIf(auth()->guest()), 'string', 'min:1', 'max:255'],
      'email' => [Rule::requiredIf(auth()->guest()), 'email', 'max:255'],
      'agency' => [Rule::requiredIf(auth()->guest()), 'string', 'min:1', 'max:255'],
      'html_message' => [
        'required',
        'string',
        'max:1048576', //1MB (1 * 1024 * 1024)
        new PurifyHtml,
      ],
      'g-recaptcha-response' => ReCaptcha::rules('inquiry', 0.5)
    ];
  }

  public function messages()
  {
    return [
      'html_message.max' => 'The :attribute is too long or contains large images.',
      'g-recaptcha-response.required' => 'The reCAPTCHA verification failed. Please try again.',
    ];
  }

  public function attributes(): array
  {
    return [
      'html_message' => 'inquiry details',
    ];
  }
}
