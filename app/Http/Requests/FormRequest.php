<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest as BaseFormRequest;
use Illuminate\Validation\Rule;

class FormRequest extends BaseFormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    if (!auth()->check()) {
      return false;
    }

    if ($this->route()->form != null) {
      return auth()->user()->can('update', $this->route()->form);
    }

    return auth()->user()->can('create', \App\Models\Form::class);
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    if ($this->route()->form != null) {
      $agencyRule = auth()->user()->isSuperAdmin()
        ? ['required', 'integer', 'exists:agencies,id']
        : ['required', 'integer', 'exists:agencies,id', Rule::in([auth()->user()->agency_id])];

      return [
        'agency_id' => $agencyRule,
        'title' => [
          'required',
          'string',
          'max:255',
          Rule::unique('forms')->where('agency_id', $this->input('agency_id'))->ignore($this->route()->form->id ?? null),
        ],
        'status' => ['required', 'in:0,1'],
        'assigned_sector_id' => ['nullable', 'integer', 'exists:staffs,id'],
      ];
    }

    $agencyRules = auth()->user()->isSuperAdmin()
      ? ['required', 'array', 'min:1']
      : ['required', 'array', 'size:1'];

    $agencyItemRules = auth()->user()->isSuperAdmin()
      ? ['integer', 'exists:agencies,id']
      : ['integer', 'exists:agencies,id', Rule::in([auth()->user()->agency_id])];

    return [
      'agency_ids' => $agencyRules,
      'agency_ids.*' => $agencyItemRules,
      'title' => ['required', 'string', 'max:255'],
      'status' => ['required', 'in:0,1'],
      'assigned_sector_id' => ['nullable', 'integer', 'exists:staffs,id'],
    ];
  }

  protected function prepareForValidation(): void
  {
    if ($this->route()->form != null) {
      return;
    }

    if (!$this->has('agency_ids') && $this->has('agency_id')) {
      $this->merge([
        'agency_ids' => array_filter((array) $this->input('agency_id')),
      ]);
    }
  }

  public function withValidator($validator): void
  {
    $validator->after(function ($validator) {
      if ($this->route()->form != null || $validator->errors()->isNotEmpty()) {
        return;
      }

      $agencyIds = collect((array) $this->input('agency_ids'))
        ->filter()
        ->unique()
        ->values();

      $existingAgencyIds = \App\Models\Form::where('title', $this->input('title'))
        ->whereIn('agency_id', $agencyIds)
        ->pluck('agency_id')
        ->all();

      if (!empty($existingAgencyIds)) {
        $validator->errors()->add('title', 'The form title already exists for one or more selected agencies.');
      }
    });
  }

  public function attributes(): array
  {
    return [
      'agency_id' => 'agency',
      'agency_ids' => 'agencies',
      'agency_ids.*' => 'agency',
    ];
  }
}
