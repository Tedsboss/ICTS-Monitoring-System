<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FormFieldRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    if (!auth()->check() || $this->route()->form == null) {
      return false;
    }

    if ($this->isMethod('delete')) {
      return auth()->user()->can('update', $this->route()->form);
    }

    if ($this->route()->form_field != null) {
      return auth()->user()->can('update', $this->route()->form);
    }

    return auth()->user()->can('update', $this->route()->form);
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'label' => ['required', 'string', 'max:255'],
      'subtitle' => ['nullable', 'string', 'max:1000'],
      'value_type' => ['required', 'in:section,integer,decimal,text,date,date_range,repeating_group'],
      'options' => ['nullable', 'string', 'max:1048576'],
      'column_size' => ['nullable', 'integer', 'in:4,12'],
      'row_number' => ['required', 'integer', 'min:1'],
      'order' => ['required', 'integer', 'min:0'],
      'is_required' => ['required', 'in:0,1'],
      'has_remarks' => ['required', 'in:0,1'],
      'status' => ['required', 'in:0,1'],
    ];
  }
}
