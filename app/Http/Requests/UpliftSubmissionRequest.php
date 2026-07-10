<?php

namespace App\Http\Requests;

use App\Models\UpliftMeasure;
use App\Models\UpliftSubmission;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpliftSubmissionRequest extends FormRequest
{
  public function authorize(): bool
  {
    if ($this->isFinalSubmit()) {
      $submission = $this->submission();

      return $submission != null && auth()->check() && auth()->user()->can('update', $submission);
    }

    $measure = $this->measure();

    if (!auth()->check()) {
      return false;
    }

    if ($measure == null) {
      return auth()->user()->can('create', UpliftSubmission::class);
    }

    if (!$this->userCanAccessMeasure($measure)) {
      return false;
    }

    if ($this->route()->uplift_submission instanceof UpliftSubmission) {
      return auth()->user()->can('update', $this->route()->uplift_submission);
    }

    return auth()->user()->can('create', UpliftSubmission::class);
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

    $fieldValues = $submission->fieldValues->keyBy('uplift_pillar_field_id');
    $indicatorValues = $submission->indicatorValues->keyBy('uplift_indicator_id');
    $data = [
      'fields' => [],
      'indicators' => [],
    ];

    foreach ($this->fields() as $field) {
      $this->appendValidationValue($data['fields'], $field->id, $field->value_type, $fieldValues->get($field->id), true);

      foreach ($field->indicators as $indicator) {
        $this->appendValidationValue($data['indicators'], $indicator->id, $indicator->value_type, $indicatorValues->get($indicator->id));
      }
    }

    return $data;
  }

  public function rules(): array
  {
    if ($this->routeIs('uplift-submissions.create')) {
      return [
        'measure_id' => ['required', 'integer', Rule::in([$this->measure()?->id])],
      ];
    }

    if ($this->isFinalSubmit()) {
      return $this->finalSubmitRules();
    }

    return $this->draftRules();
  }

  public function attributes(): array
  {
    $attributes = [
      'uplift_measure_id' => 'UPLIFT measure',
      'reporting_cutoff_date' => 'update as of',
    ];

    foreach ($this->fields() as $field) {
      if ($field->value_type == 'date_range') {
        $attributes['fields.' . $field->id . '.start_date'] = $field->label . ' - start date';
        $attributes['fields.' . $field->id . '.end_date'] = $field->label . ' - end date';
      } else {
        $attributes['fields.' . $field->id . '.value'] = $field->label;
      }

      if ($field->has_remarks == 1) {
        $attributes['fields.' . $field->id . '.remarks'] = $field->label . ' remarks';
      }

      foreach ($field->indicators as $indicator) {
        if ($indicator->value_type == 'date_range') {
          $attributes['indicators.' . $indicator->id . '.start_date'] = $indicator->label . ' - start date';
          $attributes['indicators.' . $indicator->id . '.end_date'] = $indicator->label . ' - end date';
        } else {
          $attributes['indicators.' . $indicator->id . '.value'] = $indicator->label;
        }
      }
    }

    return $attributes;
  }

  public function messages(): array
  {
    return [
      'fields.*.start_date.required' => ':attribute is required before final submission.',
      'fields.*.end_date.required' => ':attribute is required before final submission.',
      'indicators.*.start_date.required' => ':attribute is required before final submission.',
      'indicators.*.end_date.required' => ':attribute is required before final submission.',
    ];
  }

  protected function failedValidation(Validator $validator): void
  {
    if (!$this->isFinalSubmit()) {
      parent::failedValidation($validator);
    }

    throw new ValidationException(
      $validator,
      redirect()
        ->route('uplift-submissions.edit', $this->submission())
        ->withErrors($validator)
        ->with('error', 'Please complete all required fields before final submission.')
    );
  }

  private function draftRules(): array
  {
    $submissionId = $this->route()->uplift_submission->id ?? null;
    $measure = $this->measure();
    $agencyId = auth()->user()->agency_id;

    $rules = [
      'uplift_measure_id' => ['required', 'integer', Rule::in([$measure?->id])],
      'reporting_cutoff_date' => [
        'required',
        'date',
        Rule::unique('uplift_submissions', 'reporting_cutoff_date')
          ->where('uplift_measure_id', $measure?->id)
          ->where('agency_id', $agencyId)
          ->ignore($submissionId),
      ],
      'fields' => ['nullable', 'array'],
      'indicators' => ['nullable', 'array'],
    ];

    foreach ($this->fields() as $field) {
      $this->appendValueRules($rules, 'fields', $field->id, $field->value_type, false);

      if ($field->has_remarks == 1) {
        $rules['fields.' . $field->id . '.remarks'] = ['nullable', 'string'];
      }

      foreach ($field->indicators as $indicator) {
        $this->appendValueRules($rules, 'indicators', $indicator->id, $indicator->value_type, false);
      }
    }

    return $rules;
  }

  private function finalSubmitRules(): array
  {
    $rules = [];

    foreach ($this->fields() as $field) {
      $this->appendValueRules($rules, 'fields', $field->id, $field->value_type, $field->is_required == 1);

      foreach ($field->indicators as $indicator) {
        $this->appendValueRules($rules, 'indicators', $indicator->id, $indicator->value_type, $indicator->is_required == 1);
      }
    }

    return $rules;
  }

  private function appendValueRules(array &$rules, string $prefix, int $id, string $valueType, bool $required): void
  {
    $presence = $required ? 'required' : 'nullable';

    if ($valueType == 'date_range') {
      $rules[$prefix . '.' . $id . '.start_date'] = [$presence, 'date'];
      $rules[$prefix . '.' . $id . '.end_date'] = [$presence, 'date', 'after_or_equal:' . $prefix . '.' . $id . '.start_date'];

      return;
    }

    $rules[$prefix . '.' . $id . '.value'] = match ($valueType) {
      'decimal' => [$presence, 'numeric', 'min:0'],
      'text', 'select', 'boolean' => [$presence, 'string'],
      'user_picker' => $this->userPickerRules($presence),
      'repeating_group' => $this->repeatingGroupRules($presence, $this->fields()->firstWhere('id', $id)),
      'date' => [$presence, 'date'],
      default => [$presence, 'integer', 'min:0'],
    };
  }

  private function fields(): Collection
  {
    $measure = $this->measure();

    if ($measure == null) {
      return collect();
    }

    return $measure->fields->flatMap(function ($field) {
      return collect([$field])->concat($this->flattenChildren($field->children));
    })->values();
  }

  private function measure(): ?UpliftMeasure
  {
    $submission = $this->submission();

    if ($submission != null) {
      return $submission->measure;
    }

    $measureId = $this->input('uplift_measure_id') ?? $this->input('measure_id');

    if ($measureId == null) {
      return null;
    }

    return UpliftMeasure::with([
      'pillar',
      'supportingAgencies',
      'fields' => function ($query) {
        $query->where('status', 1)
          ->where('value_type', '!=', 'section')
          ->whereNull('parent_id')
          ->with([
            'children' => function ($childQuery) {
              $childQuery->where('status', 1)->orderBy('row_number')->orderBy('order')->orderBy('id');
            },
            'indicators' => function ($indicatorQuery) {
              $indicatorQuery->where('status', 1)->orderBy('order')->orderBy('id');
            },
          ])
          ->orderBy('row_number')
          ->orderBy('order')
          ->orderBy('id');
      },
    ])->where('status', 1)->find($measureId);
  }

  private function submission(): ?UpliftSubmission
  {
    $submission = $this->route()->uplift_submission;

    if (!$submission instanceof UpliftSubmission) {
      return null;
    }

    $submission->loadMissing([
      'measure.pillar',
      'measure.supportingAgencies',
      'measure.fields' => function ($query) {
        $query->where('status', 1)
          ->where('value_type', '!=', 'section')
          ->whereNull('parent_id')
          ->with([
            'children' => function ($childQuery) {
              $childQuery->where('status', 1)->orderBy('row_number')->orderBy('order')->orderBy('id');
            },
            'indicators' => function ($indicatorQuery) {
              $indicatorQuery->where('status', 1)->orderBy('order')->orderBy('id');
            },
          ])
          ->orderBy('row_number')
          ->orderBy('order')
          ->orderBy('id');
      },
      'fieldValues',
      'indicatorValues',
    ]);

    return $submission;
  }

  private function isFinalSubmit(): bool
  {
    return $this->routeIs('uplift-submissions.submit');
  }

  private function userCanAccessMeasure(UpliftMeasure $measure): bool
  {
    if (auth()->user()->isSuperAdmin()) {
      return true;
    }

    $agencyId = auth()->user()->agency_id;

    return $measure->lead_agency_id == $agencyId
      || $measure->supportingAgencies->contains('id', $agencyId);
  }

  private function appendValidationValue(array &$values, int $id, string $valueType, $value, bool $withRemarks = false): void
  {
    if ($valueType == 'date_range') {
      $values[$id] = [
        'start_date' => optional(optional($value)->date_start_value)->format('Y-m-d'),
        'end_date' => optional(optional($value)->date_end_value)->format('Y-m-d'),
      ];
    } else {
      $values[$id] = [
        'value' => match ($valueType) {
          'decimal' => optional($value)->decimal_value,
          'text', 'select', 'boolean', 'repeating_group', 'user_picker' => optional($value)->text_value,
          'date' => optional(optional($value)->date_value)->format('Y-m-d'),
          default => optional($value)->integer_value,
        },
      ];
    }

    if ($withRemarks) {
      $values[$id]['remarks'] = optional($value)->remarks;
    }
  }

  private function flattenChildren(Collection $children): Collection
  {
    return $children->flatMap(function ($child) {
      return collect([$child])->concat($this->flattenChildren($child->children));
    })->values();
  }


  private function userPickerRules(string $presence): array
  {
    return [
      $presence,
      'string',
      'max:2000',
      function (string $attribute, mixed $value, \Closure $fail) use ($presence) {
        if ($value == null || trim((string) $value) === '') {
          return;
        }

        $payload = json_decode((string) $value, true);

        if (!is_array($payload) || empty($payload['user_id'])) {
          $fail('The :attribute must contain a valid selected user.');
          return;
        }

        $agencyId = $this->submission()?->agency_id ?? auth()->user()->agency_id;
        $userExists = User::query()
          ->where('id', (int) $payload['user_id'])
          ->where('agency_id', $agencyId)
          ->exists();

        if (!$userExists) {
          $fail('The selected user must belong to the submission agency.');
          return;
        }
      },
    ];
  }

  private function repeatingGroupRules(string $presence, $field): array
  {
    return [
      $presence,
      'string',
      'max:1048576',
      function (string $attribute, mixed $value, \Closure $fail) use ($field, $presence) {
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

        if ($presence === 'required' && !$hasValue) {
          $fail('The :attribute requires at least one completed row.');
        }
      },
    ];
  }

  private function repeatingGroupColumnIds($field): array
  {
    $options = is_array(optional($field)->options) ? $field->options : [];
    $columns = is_array($options['columns'] ?? null) ? $options['columns'] : [];

    $columnIds = collect($columns)
      ->map(fn($column) => is_array($column) ? (string) ($column['id'] ?? '') : '')
      ->filter()
      ->values()
      ->all();

    return !empty($columnIds) ? $columnIds : ['col_1'];
  }
}
