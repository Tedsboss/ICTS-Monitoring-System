<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffRequest extends FormRequest
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
      'name' => ['required', 'string', 'max:50', 'min:2', Rule::unique('staffs')->ignore($this->route()->staff->id ?? null)],
      'abbreviation' => ['required', 'string', 'max:50', 'min:2'],
      'head_name' => ['required', 'string', 'max:50', 'min:2'],
      'head_position' => ['required', 'string', 'max:50', 'min:2'],
      'head_email' => ['required', 'email:rfc,dns', 'not_regex:/\s/'],
    ];
  }
}
