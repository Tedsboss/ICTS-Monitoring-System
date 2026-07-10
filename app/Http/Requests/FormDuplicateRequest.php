<?php

namespace App\Http\Requests;

use App\Models\Form;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FormDuplicateRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    if (!auth()->check() || $this->route('form') == null) {
      return false;
    }

    return auth()->user()->can('create', Form::class)
      && auth()->user()->can('view', $this->route('form'));
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    $targetAgencyId = auth()->user()->agency_id ?? $this->input('agency_id', $this->route('form')->agency_id);

    $rules = [
      'title' => [
        'required',
        'string',
        'max:255',
        Rule::unique('forms')
          ->where('agency_id', $targetAgencyId),
      ],
    ];

    if (auth()->user()->isSuperAdmin()) {
      $rules['agency_id'] = ['required', 'integer', 'exists:agencies,id'];
    }

    return $rules;
  }

  public function attributes(): array
  {
    return [
      'agency_id' => 'agency',
    ];
  }
}
