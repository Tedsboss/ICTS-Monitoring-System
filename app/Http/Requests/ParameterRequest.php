<?php

namespace App\Http\Requests;

use App\Rules\PurifyHtml;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;
use Svg\Tag\Rect;

class ParameterRequest extends FormRequest
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
      'name' => ['required', 'string', 'max:255', 'min:2'],
      'type' => ['required', 'in:html,string,time,integer,boolean'],
      'title' => ['required', 'string', 'max:255', 'min:2'],
      'description' => ['nullable', 'string', 'max:10000', 'min:2'],
      'value' => [
        'required',
        'max:1048576',
        Rule::when($this->input('type') == 'html', new PurifyHtml),
        Rule::when($this->input('type') == 'string', 'string'),
        Rule::when($this->input('type') == 'time', 'date_format:H:i'),
        Rule::when($this->input('type') == 'integer', 'integer'),
        Rule::when($this->input('type') == 'boolean', 'in:yes,no'),
      ],
      'start_date' => [Rule::requiredIf(function () {
        return $this->route()->parameter->with_duration == 'Y';
      }), 'nullable', 'date'],
      'end_date' => [Rule::requiredIf(function () {
        return $this->route()->parameter->with_duration == 'Y';
      }), 'nullable', 'date', 'after:start_date'],
    ];
  }
}
