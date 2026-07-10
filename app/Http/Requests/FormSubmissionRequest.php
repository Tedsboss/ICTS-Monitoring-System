<?php

namespace App\Http\Requests;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Rules\PurifyHtml;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FormSubmissionRequest extends FormRequest
{
  protected function prepareForValidation(): void
  {
    if ($this->isFinalSubmit()) {
      return;
    }

    $values = $this->input('values', []);

    foreach ($values as $fieldId => $value) {
      if (isset($value['remarks']) && $this->isEmptyRichText($value['remarks'])) {
        $values[$fieldId]['remarks'] = null;
      }
    }

    $this->merge([
      'values' => $values,
    ]);
  }

  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    if ($this->isFinalSubmit()) {
      $submission = $this->submission();

      return $submission != null && auth()->check() && auth()->user()->can('update', $submission);
    }

    $form = $this->form();

    if ($form == null) {
      return false;
    }

    if ($this->route()->form_submission instanceof FormSubmission && $this->route()->form_submission->isSubmitted()) {
      return false;
    }

    if (!auth()->check() || auth()->user()->agency_id != $form->agency_id) {
      return false;
    }

    if ($this->route()->form_submission instanceof FormSubmission) {
      return auth()->user()->can('update', $this->route()->form_submission);
    }

    return auth()->user()->can('create', FormSubmission::class);
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    if ($this->isFinalSubmit()) {
      return $this->finalSubmitRules();
    }

    return $this->draftRules();
  }

  public function withValidator(Validator $validator): void
  {
    if (!$this->isFinalSubmit()) {
      return;
    }

    $validator->after(function (Validator $validator) {
      $submission = $this->submission();

      if ($submission == null || $this->hasSubmittedThisWeek($submission)) {
        $validator->errors()->add('submission', $this->weeklySubmissionLockMessage());
      }
    });
  }

  public function validationData(): array
  {
    if (!$this->isFinalSubmit()) {
      return parent::validationData();
    }

    $submission = $this->submission();

    if ($submission == null) {
      return [];
    }

    $values = $submission->values->keyBy('form_field_id');
    $data = [
      'values' => [],
    ];

    foreach ($this->fields() as $field) {
      $value = $values->get($field->id);

      if ($field->value_type == 'date_range') {
        $data['values'][$field->id] = [
          'start_date' => optional(optional($value)->date_start_value)->format('Y-m-d'),
          'end_date' => optional(optional($value)->date_end_value)->format('Y-m-d'),
        ];
      } else {
        $data['values'][$field->id] = [
          'value' => match ($field->value_type) {
            'decimal' => optional($value)->decimal_value,
            'text' => optional($value)->text_value,
            'repeating_group' => optional($value)->text_value,
            'date' => optional(optional($value)->date_value)->format('Y-m-d'),
            default => optional($value)->integer_value,
          },
        ];
      }
    }

    return $data;
  }

  private function draftRules(): array
  {
    $submissionId = $this->route()->form_submission->id ?? null;
    $form = $this->form();
    $formId = $form?->id;
    $agencyId = auth()->user()->agency_id;

    $rules = [
      'form_id' => ['required', 'integer', 'exists:forms,id'],
      'reporting_cutoff_date' => [
        'required',
        'date',
        Rule::unique('form_submissions', 'reporting_cutoff_date')
          ->where('form_id', $formId)
          ->where('agency_id', $agencyId)
          ->ignore($submissionId),
      ],
      'values' => ['required', 'array'],
    ];

    if ($form != null) {
      foreach ($this->fields() as $field) {
        if ($field->value_type == 'date_range') {
          $rules['values.' . $field->id . '.start_date'] = ['nullable', 'date'];
          $rules['values.' . $field->id . '.end_date'] = ['nullable', 'date', 'after_or_equal:values.' . $field->id . '.start_date'];
        } else {
          $rules['values.' . $field->id . '.value'] = match ($field->value_type) {
            'decimal' => ['nullable', 'numeric', 'min:0'],
            'text' => ['nullable', 'string'],
            'repeating_group' => $this->repeatingGroupRules('nullable', $field),
            'date' => ['nullable', 'date'],
            default => ['nullable', 'integer', 'min:0'],
          };
        }
        if ($field->has_remarks == 1) {
          $rules['values.' . $field->id . '.remarks'] = [
            'nullable',
            'string',
            'max:1048576',
            new PurifyHtml(true),
          ];
        }
      }
    }

    return $rules;
  }

  private function finalSubmitRules(): array
  {
    $rules = [];

    foreach ($this->fields() as $field) {
      $required = $field->is_required == 1 ? 'required' : 'nullable';

      if ($field->value_type == 'date_range') {
        $rules['values.' . $field->id . '.start_date'] = [$required, 'date'];
        $rules['values.' . $field->id . '.end_date'] = [$required, 'date', 'after_or_equal:values.' . $field->id . '.start_date'];
      } else {
        $rules['values.' . $field->id . '.value'] = match ($field->value_type) {
          'decimal' => [$required, 'numeric', 'min:0'],
          'text' => [$required, 'string'],
          'repeating_group' => $this->repeatingGroupRules($required, $field),
          'date' => [$required, 'date'],
          default => [$required, 'integer', 'min:0'],
        };
      }
    }

    return $rules;
  }

  public function attributes(): array
  {
    $attributes = [
      'form_id' => 'form',
      'reporting_cutoff_date' => 'week ending date',
    ];

    foreach ($this->fields() as $field) {
      if ($field->value_type == 'date_range') {
        $attributes['values.' . $field->id . '.start_date'] = $field->label . ' - start date';
        $attributes['values.' . $field->id . '.end_date'] = $field->label . ' - end date';
      } else {
        $attributes['values.' . $field->id . '.value'] = $field->label;
      }
      if (!$this->isFinalSubmit() && $field->has_remarks == 1) {
        $attributes['values.' . $field->id . '.remarks'] = $field->label . ' remarks';
      }
    }

    return $attributes;
  }

  public function messages(): array
  {
    return [
      'values.*.start_date.required' => ':attribute is required before final submission.',
      'values.*.end_date.required' => ':attribute is required before final submission.',
      'values.*.remarks.max' => 'The :attribute is too long or contains large images.',
    ];
  }

  protected function failedValidation(Validator $validator): void
  {
    if (!$this->isFinalSubmit()) {
      parent::failedValidation($validator);
    }

    $message = $validator->errors()->first('submission') ?: 'Please complete all required fields before final submission.';

    throw new ValidationException(
      $validator,
      redirect()
        ->route('submissions.edit', $this->submission())
        ->withErrors($validator)
        ->with('error', $message)
    );
  }

  private function form(): ?Form
  {
    $formId = $this->input('form_id') ?? $this->route()->form_submission->form_id ?? null;

    if ($formId == null) {
      return null;
    }

    return Form::with(['fields' => function ($query) {
      $query->where('status', 1)->orderBy('row_number')->orderBy('order');
    }])->where('status', 1)->find($formId);
  }

  private function fields()
  {
    if ($this->isFinalSubmit()) {
      return (optional(optional($this->submission())->form)->fields ?? collect())
        ->where('value_type', '!=', 'section');
    }

    return (optional($this->form())->fields ?? collect())
      ->where('value_type', '!=', 'section');
  }

  private function submission(): ?FormSubmission
  {
    $submission = $this->route()->form_submission;

    if (!$submission instanceof FormSubmission) {
      return null;
    }

    if (!$submission->relationLoaded('form') || !$submission->relationLoaded('values')) {
      $submission->load(['form.fields' => function ($query) {
        $query->where('status', 1)->orderBy('row_number')->orderBy('order');
      }, 'values']);
    }

    return $submission;
  }

  private function isFinalSubmit(): bool
  {
    return $this->routeIs('submissions.submit');
  }

  private function hasSubmittedThisWeek(FormSubmission $submission): bool
  {
    $weekStart = now()->startOfWeek();
    $weekEnd = now()->endOfWeek();

    return FormSubmission::where('agency_id', $submission->agency_id)
      ->where('form_id', $submission->form_id)
      ->whereIn('status', ['submitted', 'approved'])
      ->whereBetween('submitted_at', [$weekStart, $weekEnd])
      ->where('id', '!=', $submission->id)
      ->exists();
  }

  private function weeklySubmissionLockMessage(): string
  {
    return 'A report has already been submitted this week. Submissions will reopen next Monday.';
  }

  private function repeatingGroupRules(string $required, $field): array
  {
    return [
      $required,
      'string',
      'max:1048576',
      function (string $attribute, mixed $value, \Closure $fail) use ($field, $required) {
        if ($value == null || trim((string) $value) === '') {
          return;
        }

        $rows = json_decode((string) $value, true);

        if (!is_array($rows)) {
          $fail('The :attribute must contain valid repeating group rows.');
          return;
        }

        $columnIds = $this->repeatingGroupColumnIds($field);
        $hasValue = false;

        foreach ($rows as $row) {
          if (!is_array($row)) {
            $fail('The :attribute must contain valid repeating group rows.');
            return;
          }

          foreach ($row as $columnId => $cellValue) {
            if (!in_array((string) $columnId, $columnIds, true)) {
              $fail('The :attribute contains an unknown repeating group column.');
              return;
            }

            if (trim((string) $cellValue) !== '') {
              $hasValue = true;
            }
          }
        }

        if ($required === 'required' && !$hasValue) {
          $fail('The :attribute requires at least one completed row.');
        }
      },
    ];
  }

  private function repeatingGroupColumnIds($field): array
  {
    $options = is_array($field->options) ? $field->options : [];
    $columns = is_array($options['columns'] ?? null) ? $options['columns'] : [];

    $columnIds = collect($columns)
      ->map(fn ($column) => is_array($column) ? (string) ($column['id'] ?? '') : '')
      ->filter()
      ->values()
      ->all();

    return !empty($columnIds) ? $columnIds : ['col_1'];
  }

  private function isEmptyRichText(?string $value): bool
  {
    if ($value == null) {
      return true;
    }

    if (preg_match('/<(img|iframe)\b/i', $value)) {
      return false;
    }

    return trim(strip_tags(str_replace('&nbsp;', ' ', $value))) == '';
  }
}
