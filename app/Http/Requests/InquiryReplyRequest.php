<?php

namespace App\Http\Requests;

use App\Rules\PurifyHtml;
use Illuminate\Foundation\Http\FormRequest;

class InquiryReplyRequest extends FormRequest
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
      'html_reply' => [
        'required',
        'string',
        'max:1048576', //1MB (1 * 1024 * 1024)
        new PurifyHtml,
      ],
    ];
  }

  public function messages()
  {
    return [
      'html_reply.required' => 'The :attribute field is required.',
      'html_reply.max' => 'The :attribute message is too long or contains large images.',
    ];
  }

  public function attributes(): array
  {
    return [
      'html_reply' => 'reply/remarks',
    ];
  }
}
