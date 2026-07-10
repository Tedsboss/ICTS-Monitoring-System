@php
  $readonly = $readonly ?? false;
@endphp

@csrf
<input type="hidden" name="form_id" value="{{ old('form_id', $form->id) }}">

<div class="submission-shell">
  <div class="submission-summary mb-4">
    <div class="row align-items-center g-3">
      <div class="col-lg-8">
        <div class="d-flex align-items-start">
          <div class="submission-summary-icon me-3">
            <i class="fa fa-calendar-o"></i>
          </div>
          <div class="w-100">
            <p class="submission-eyebrow mb-1">Reporting Period</p>
            <label class="submission-label mb-2">Week Ending Date <span class="text-danger">*</span></label>
            <div class="input-group submission-date-picker date-picker-field {{ $readonly ? '' : 'cursor-pointer' }}" data-date-picker-target="reporting_cutoff_date">
              <span class="input-group-text pe-2"><i class="fa fa-calendar-o"></i></span>
              <input id="reporting_cutoff_date" name="reporting_cutoff_date" class="form-control uplift-flatpickr" type="text" placeholder="YYYY-MM-DD" value="{{ old('reporting_cutoff_date', $submission->reporting_cutoff_date?->format('Y-m-d')) }}" {{ $readonly ? 'disabled' : '' }}>
            </div>
            @error('reporting_cutoff_date') <p class='text-danger text-xs mb-0 mt-1'> {{ $message }} </p> @enderror
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="submission-status-panel">
          <p class="submission-eyebrow mb-1">Submission Status</p>
          <div class="d-flex align-items-center justify-content-lg-end">
            <span class="submission-status-badge {{ $submission->status == 'submitted' ? 'is-submitted' : 'is-draft' }}">
              {{ $submission->status ? ucfirst($submission->status) : 'Draft' }}
            </span>
          </div>
          @if($submission->submitted_at)
            <p class="text-xs text-muted mb-0 mt-2">Submitted {{ $submission->submitted_at->format('Y-m-d H:i:s') }}</p>
          @else
            <p class="text-xs text-muted mb-0 mt-2">Drafts can be incomplete. Final submission checks required fields.</p>
          @endif
        </div>
      </div>
    </div>
  </div>

@include('submissions.form_css')

@php
  $activeFields = $form->fields
    ->where('status', 1)
    ->sortBy([
      ['row_number', 'asc'],
      ['order', 'asc'],
      ['id', 'asc'],
    ])
    ->values();

  $sections = $activeFields->where('value_type', 'section')->values();
  $regularFields = $activeFields->where('value_type', '!=', 'section')->values();
  $fieldsBySection = $regularFields->groupBy(fn ($field) => (int) $field->row_number);

  $sectionGroups = $sections->map(function ($section) use ($fieldsBySection) {
    return [
      'section' => $section,
      'fields' => ($fieldsBySection[(int) $section->row_number] ?? collect())->values(),
    ];
  });

  if ($sectionGroups->isEmpty() && $regularFields->isNotEmpty()) {
    $sectionGroups = collect([
      [
        'section' => null,
        'fields' => $regularFields,
      ],
    ]);
  }
@endphp

<div class="submission-grid mt-4">
  @forelse($sectionGroups as $sectionGroup)
    @php
      $section = $sectionGroup['section'];
      $sectionFields = $sectionGroup['fields'];
    @endphp

    <div class="submission-section mb-4">
      @if($section)
        <div class="submission-section-header">
          <div class="submission-section-icon">
            <i class="fa fa-folder-open"></i>
          </div>
          <div>
            <h6 class="submission-section-title">{{ $section->label }}</h6>
            @if($section->subtitle)
              <p class="submission-section-subtitle">{{ $section->subtitle }}</p>
            @endif
          </div>
        </div>
      @endif

      @if($sectionFields->isNotEmpty())
        <div class="submission-section-grid">
          @foreach($sectionFields as $field)
        @php
          $fieldOrder = max(1, (int) ($field->order ?: 1));
          $fieldColumn = (($fieldOrder - 1) % 3) + 1;
          $fieldRow = (int) floor(($fieldOrder - 1) / 3) + 1;
          $rowFields = $sectionFields->filter(function ($item) use ($fieldRow) {
            $itemOrder = max(1, (int) ($item->order ?: 1));
            return ((int) floor(($itemOrder - 1) / 3) + 1) === $fieldRow;
          })->values();
          $rowHasFullField = $rowFields->contains(fn ($item) => (int) $item->column_size === 12 || $item->value_type === 'repeating_group');
          $normalRowFields = $rowFields->filter(fn ($item) => (int) $item->column_size !== 12 && $item->value_type !== 'repeating_group')->values();

          if ((int) $field->column_size === 12 || $field->value_type === 'repeating_group') {
            $fieldGridColumn = '1 / -1';
          } elseif (!$rowHasFullField && $normalRowFields->count() === 2) {
            $visualIndex = $normalRowFields->search(fn ($item) => (string) $item->id === (string) $field->id);
            $fieldGridColumn = $visualIndex === 0 ? '1 / 7' : '7 / 13';
          } else {
            $fieldGridStart = (($fieldColumn - 1) * 4) + 1;
            $fieldGridColumn = $fieldGridStart . ' / ' . ($fieldGridStart + 4);
          }
          $fieldValue = $values->get($field->id);
          $currentValue = optional($fieldValue)->integer_value;
          if ($field->value_type == 'decimal') {
            $currentValue = optional($fieldValue)->decimal_value;
          } elseif (in_array($field->value_type, ['text', 'repeating_group'])) {
            $currentValue = optional($fieldValue)->text_value;
          } elseif ($field->value_type == 'date') {
            $currentValue = optional(optional($fieldValue)->date_value)->format('Y-m-d');
          }
          $repeatingRows = [];
          $repeatingColumns = [];
          if ($field->value_type == 'repeating_group') {
            $fieldOptions = is_array($field->options) ? $field->options : [];
            $optionColumns = $fieldOptions['columns'] ?? [];
            $repeatingColumns = collect(is_array($optionColumns) ? $optionColumns : [])
              ->map(function ($column, $index) {
                $type = in_array(($column['type'] ?? null), ['select', 'date'], true) ? $column['type'] : 'text';

                return [
                  'id' => (string) ($column['id'] ?? ('col_' . ($index + 1))),
                  'label' => (string) ($column['label'] ?? ('Column ' . ($index + 1))),
                  'type' => $type,
                  'source' => $type === 'select' ? ($column['source'] ?? null) : null,
                ];
              })
              ->filter(fn ($column) => trim($column['label']) !== '')
              ->values()
              ->all();

            if (empty($repeatingColumns)) {
              $repeatingColumns = [
                [
                  'id' => 'col_1',
                  'label' => 'Column 1',
                  'type' => 'text',
                  'source' => null,
                ],
              ];
            }

            $selectSources = [
              'user_name' => \App\Models\User::query()
                ->orderBy('firstname')
                ->orderBy('lastname')
                ->get()
                ->map(fn ($user) => trim((string) $user->full_name))
                ->filter()
                ->unique()
                ->values()
                ->all(),
              'designation' => \App\Models\Position::query()
                ->orderBy('name')
                ->pluck('name')
                ->map(fn ($name) => trim((string) $name))
                ->filter()
                ->unique()
                ->values()
                ->all(),
              'status' => ['Draft', 'Submitted', 'Approved', 'Returned', 'Rejected'],
            ];

            $rawRepeatingValue = old('values.' . $field->id . '.value', $currentValue);
            $decodedRepeatingRows = json_decode((string) $rawRepeatingValue, true);

            if (is_array($decodedRepeatingRows)) {
              $repeatingRows = collect($decodedRepeatingRows)
                ->map(function ($row) use ($repeatingColumns) {
                  if (!is_array($row)) {
                    return [
                      $repeatingColumns[0]['id'] => (string) $row,
                    ];
                  }

                  if (array_key_exists('value', $row) && count($row) === 1) {
                    return [
                      $repeatingColumns[0]['id'] => (string) $row['value'],
                    ];
                  }

                  return collect($repeatingColumns)
                    ->mapWithKeys(fn ($column) => [$column['id'] => (string) ($row[$column['id']] ?? '')])
                    ->all();
                })
                ->filter(fn ($row) => collect($row)->contains(fn ($value) => trim((string) $value) !== ''))
                ->values()
                ->all();
            } elseif (trim((string) $rawRepeatingValue) !== '') {
              $repeatingRows = collect(preg_split('/\r\n|\r|\n/', (string) $rawRepeatingValue))
                ->map(fn ($value) => [$repeatingColumns[0]['id'] => (string) $value])
                ->filter(fn ($row) => collect($row)->contains(fn ($value) => trim((string) $value) !== ''))
                ->values()
                ->all();
            }

            if (empty($repeatingRows)) {
              $repeatingRows = [
                collect($repeatingColumns)->mapWithKeys(fn ($column) => [$column['id'] => ''])->all(),
              ];
            }
          }
          $currentStartDate = optional(optional($fieldValue)->date_start_value)->format('Y-m-d');
          $currentEndDate = optional(optional($fieldValue)->date_end_value)->format('Y-m-d');
          $currentDays = null;
          if ($field->value_type == 'date_range' && optional($fieldValue)->date_start_value && optional($fieldValue)->date_end_value) {
            $currentDays = optional($fieldValue)->date_start_value->diffInDays(optional($fieldValue)->date_end_value) + 1;
          }
          $fieldIcon = match ($field->value_type) {
            'text' => 'fa-align-left',
            'repeating_group' => 'fa-list-alt',
            'date' => 'fa-calendar',
            'date_range' => 'fa-calendar-check-o',
            'decimal' => 'fa-line-chart',
            default => 'fa-hashtag',
          };
        @endphp
        <div class="submission-field-cell" style="grid-column: {{ $fieldGridColumn }}; grid-row: {{ $fieldRow }};">
          <div class="submission-field">
            <div class="submission-field-header">
              <div class="submission-field-icon">
                <i class="fa {{ $fieldIcon }}"></i>
              </div>
              <div>
                <label class="submission-field-title">
                  {{ $field->label }}
                  @if($field->is_required == 1)<span class="submission-required">*</span>@endif
                </label>
                @if($field->subtitle)
                  <p class="submission-field-subtitle">{{ $field->subtitle }}</p>
                @endif
              </div>
            </div>
            <div class="submission-field-body">
              @if($field->value_type == 'text')
                <textarea name="values[{{ $field->id }}][value]" class="form-control" rows="4" placeholder="{{ $field->label }}" {{ $readonly ? 'disabled' : '' }}>{{ old('values.' . $field->id . '.value', $currentValue) }}</textarea>
              @elseif($field->value_type == 'repeating_group')
                <div class="submission-repeating-group" data-repeating-group data-readonly="{{ $readonly ? '1' : '0' }}">
                  <input type="hidden" name="values[{{ $field->id }}][value]" class="repeating-group-value" value="{{ old('values.' . $field->id . '.value', $currentValue) }}">

                  <div class="repeating-group-header" style="--repeating-column-count: {{ max(1, count($repeatingColumns)) }};">
                    @foreach($repeatingColumns as $column)
                      <span>{{ $column['label'] }}</span>
                    @endforeach
                  </div>

                  <div class="repeating-group-rows">
                    @foreach($repeatingRows as $rowValues)
                      <div class="repeating-group-row" style="--repeating-column-count: {{ max(1, count($repeatingColumns)) }};">
                        <div class="repeating-group-fields">
                          @foreach($repeatingColumns as $column)
                            <div class="repeating-group-cell">
                              @if(($column['type'] ?? 'text') === 'select')
                                @php
                                  $cellValue = $rowValues[$column['id']] ?? '';
                                  $sourceOptions = $selectSources[$column['source'] ?? ''] ?? [];
                                @endphp
                                <select
                                  class="form-control repeating-group-input repeating-group-column-input"
                                  data-column-id="{{ $column['id'] }}"
                                  aria-label="{{ $column['label'] }}"
                                  {{ $readonly ? 'disabled' : '' }}
                                >
                                  <option value="">Select</option>
                                  @foreach($sourceOptions as $sourceOption)
                                    <option value="{{ $sourceOption }}" {{ (string) $cellValue === (string) $sourceOption ? 'selected' : '' }}>{{ $sourceOption }}</option>
                                  @endforeach
                                </select>
                              @elseif(($column['type'] ?? 'text') === 'date')
                                <input
                                  class="form-control repeating-group-input repeating-group-column-input"
                                  type="date"
                                  data-column-id="{{ $column['id'] }}"
                                  value="{{ $rowValues[$column['id']] ?? '' }}"
                                  aria-label="{{ $column['label'] }}"
                                  {{ $readonly ? 'disabled' : '' }}
                                >
                              @else
                                <input
                                  class="form-control repeating-group-input repeating-group-column-input"
                                  type="text"
                                  data-column-id="{{ $column['id'] }}"
                                  value="{{ $rowValues[$column['id']] ?? '' }}"
                                  aria-label="{{ $column['label'] }}"
                                  {{ $readonly ? 'disabled' : '' }}
                                >
                              @endif
                            </div>
                          @endforeach
                        </div>
                        @if(!$readonly)
                          <button type="button" class="repeating-group-remove" title="Remove row">
                            <i class="fa fa-trash"></i>
                          </button>
                        @endif
                      </div>
                    @endforeach
                  </div>

                  @if(!$readonly)
                    <template class="repeating-group-row-template">
                      <div class="repeating-group-row" style="--repeating-column-count: {{ max(1, count($repeatingColumns)) }};">
                        <div class="repeating-group-fields">
                          @foreach($repeatingColumns as $column)
                            <div class="repeating-group-cell">
                              @if(($column['type'] ?? 'text') === 'select')
                                @php $sourceOptions = $selectSources[$column['source'] ?? ''] ?? []; @endphp
                                <select
                                  class="form-control repeating-group-input repeating-group-column-input"
                                  data-column-id="{{ $column['id'] }}"
                                  aria-label="{{ $column['label'] }}"
                                >
                                  <option value="">Select</option>
                                  @foreach($sourceOptions as $sourceOption)
                                    <option value="{{ $sourceOption }}">{{ $sourceOption }}</option>
                                  @endforeach
                                </select>
                              @elseif(($column['type'] ?? 'text') === 'date')
                                <input
                                  class="form-control repeating-group-input repeating-group-column-input"
                                  type="date"
                                  data-column-id="{{ $column['id'] }}"
                                  value=""
                                  aria-label="{{ $column['label'] }}"
                                >
                              @else
                                <input
                                  class="form-control repeating-group-input repeating-group-column-input"
                                  type="text"
                                  data-column-id="{{ $column['id'] }}"
                                  value=""
                                  aria-label="{{ $column['label'] }}"
                                >
                              @endif
                            </div>
                          @endforeach
                        </div>

                        <button type="button" class="repeating-group-remove" title="Remove row">
                          <i class="fa fa-trash"></i>
                        </button>
                      </div>
                    </template>

                    <button type="button" class="repeating-group-add">
                      <i class="fa fa-plus"></i>
                      <span>Add Row</span>
                    </button>
                  @endif
                </div>
              @elseif($field->value_type == 'date_range')
                <div class="row align-items-end">
                  <div class="col-12">
                    <div class="input-group submission-date-picker date-picker-field {{ $readonly ? '' : 'cursor-pointer' }}" data-date-picker-target="date_start_{{ $field->id }}">
                      <span class="input-group-text pe-2"><i class="fa fa-calendar-o"></i></span>
                      <input name="values[{{ $field->id }}][start_date]" id="date_start_{{ $field->id }}" class="form-control date-range-input uplift-flatpickr" type="text" placeholder="YYYY-MM-DD" data-days-target="date_days_{{ $field->id }}" data-pair-id="date_end_{{ $field->id }}" value="{{ old('values.' . $field->id . '.start_date', $currentStartDate) }}" {{ $readonly ? 'disabled' : '' }}>
                    </div>
                    @error('values.' . $field->id . '.start_date') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                  </div>
                  <div class="col-12 mt-3">
                    <div class="date-range-total d-flex align-items-center justify-content-center px-2">
                      <span class="days-value" id="date_days_{{ $field->id }}">{{ $currentDays ?? '-' }}</span>
                      <span class="text-xs text-muted ms-1">days</span>
                    </div>
                  </div>
                  <div class="col-12 mt-3">
                    <div class="input-group submission-date-picker date-picker-field {{ $readonly ? '' : 'cursor-pointer' }}" data-date-picker-target="date_end_{{ $field->id }}">
                      <span class="input-group-text pe-2"><i class="fa fa-calendar-o"></i></span>
                      <input name="values[{{ $field->id }}][end_date]" id="date_end_{{ $field->id }}" class="form-control date-range-input uplift-flatpickr" type="text" placeholder="YYYY-MM-DD" data-days-target="date_days_{{ $field->id }}" data-pair-id="date_start_{{ $field->id }}" value="{{ old('values.' . $field->id . '.end_date', $currentEndDate) }}" {{ $readonly ? 'disabled' : '' }}>
                    </div>
                    @error('values.' . $field->id . '.end_date') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                  </div>
                </div>
              @else
                @if($field->value_type == 'date')
                  <div class="input-group submission-date-picker date-picker-field {{ $readonly ? '' : 'cursor-pointer' }}" data-date-picker-target="field_value_{{ $field->id }}">
                    <span class="input-group-text pe-2"><i class="fa fa-calendar-o"></i></span>
                    <input id="field_value_{{ $field->id }}" name="values[{{ $field->id }}][value]" class="form-control uplift-flatpickr" type="text" placeholder="YYYY-MM-DD" value="{{ old('values.' . $field->id . '.value', $currentValue) }}" {{ $readonly ? 'disabled' : '' }}>
                  </div>
                @else
                  <div class="input-group">
                    <input name="values[{{ $field->id }}][value]" class="form-control" type="number" min="{{ in_array($field->value_type, ['integer', 'decimal']) ? '0' : '' }}" step="{{ $field->value_type == 'decimal' ? '0.01' : '1' }}" value="{{ old('values.' . $field->id . '.value', $currentValue) }}" {{ $readonly ? 'disabled' : '' }}>
                  </div>
                @endif
              @endif
              @if($field->value_type != 'date_range')
                @error('values.' . $field->id . '.value') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
              @endif

              @if($field->has_remarks == 1)
                <label class="submission-remarks-label mt-3 mb-2">Remarks</label>
                <div id="quill_remarks_{{ $field->id }}" class="submission-remarks-editor quill-box" data-html-input="remarks_{{ $field->id }}" data-readonly="{{ $readonly ? '1' : '0' }}"></div>
                <textarea id="remarks_{{ $field->id }}" name="values[{{ $field->id }}][remarks]" hidden>{{ old('values.' . $field->id . '.remarks', optional($fieldValue)->remarks) }}</textarea>
                @error('values.' . $field->id . '.remarks') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
              @endif
            </div>
          </div>
        </div>
          @endforeach
        </div>
      @else
        <div class="submission-section-empty">No active fields inside this section.</div>
      @endif
    </div>
  @empty
    <div class="submission-section-empty">No active fields available.</div>
  @endforelse
</div>

@if(!$readonly)
  <div class="submission-actions">
    <button class="submission-action-btn submission-action-save" type="submit">
      <i class="fa fa-save"></i>
      <span>Save Draft</span>
    </button>
    @if($submission->exists && !$submission->isSubmitted())
      <button class="submission-action-btn submission-action-submit" type="submit" form="submission-submit-form" onclick="return confirm('Are you sure you want to submit this report?')">
        <i class="fa fa-paper-plane"></i>
        <span>Submit</span>
      </button>
    @endif
  </div>
@endif
</div>
