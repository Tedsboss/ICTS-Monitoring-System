@php
  $namePrefix = $namePrefix ?? 'fields';
  $readonly = $readonly ?? false;
  $value = $value ?? null;
  $valueType = $item->value_type;
  $currentValue = optional($value)->integer_value;
  if ($valueType == 'decimal') {
    $currentValue = optional($value)->decimal_value;
  } elseif (in_array($valueType, ['text', 'select', 'boolean', 'repeating_group', 'user_picker'])) {
    $currentValue = optional($value)->text_value;
  } elseif ($valueType == 'date') {
    $currentValue = optional(optional($value)->date_value)->format('Y-m-d');
  }
  $currentStartDate = optional(optional($value)->date_start_value)->format('Y-m-d');
  $currentEndDate = optional(optional($value)->date_end_value)->format('Y-m-d');
  $selectInputId = $namePrefix . '_' . $item->id . '_value_select';
@endphp

@if($valueType == 'text')
  <textarea name="{{ $namePrefix }}[{{ $item->id }}][value]" class="form-control" rows="4" {{ $readonly ? 'disabled' : '' }}>{{ old($namePrefix . '.' . $item->id . '.value', $currentValue) }}</textarea>
  @error($namePrefix . '.' . $item->id . '.value')<p class="text-danger text-xs pt-1 mb-0">{{ $message }}</p>@enderror
@elseif($valueType == 'repeating_group')
  @php
    $fieldOptions = is_array($item->options) ? $item->options : [];
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
      ->filter(fn($column) => trim($column['label']) !== '')
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
        ->map(fn($user) => trim((string) $user->full_name))
        ->filter()
        ->unique()
        ->values()
        ->all(),
      'designation' => \App\Models\Position::query()
        ->orderBy('name')
        ->pluck('name')
        ->map(fn($name) => trim((string) $name))
        ->filter()
        ->unique()
        ->values()
        ->all(),
      'status' => ['Draft', 'Submitted', 'Approved', 'Returned', 'Rejected'],
    ];

    $rawRepeatingValue = old($namePrefix . '.' . $item->id . '.value', $currentValue);
    $decodedRepeatingRows = json_decode((string) $rawRepeatingValue, true);
    $repeatingRows = [];

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
            ->mapWithKeys(fn($column) => [$column['id'] => (string) ($row[$column['id']] ?? '')])
            ->all();
        })
        ->filter(fn($row) => collect($row)->contains(fn($cellValue) => trim((string) $cellValue) !== ''))
        ->values()
        ->all();
    } elseif (trim((string) $rawRepeatingValue) !== '') {
      $repeatingRows = collect(preg_split('/\r\n|\r|\n/', (string) $rawRepeatingValue))
        ->map(fn($lineValue) => [$repeatingColumns[0]['id'] => (string) $lineValue])
        ->filter(fn($row) => collect($row)->contains(fn($cellValue) => trim((string) $cellValue) !== ''))
        ->values()
        ->all();
    }

    if (empty($repeatingRows)) {
      $repeatingRows = [
        collect($repeatingColumns)->mapWithKeys(fn($column) => [$column['id'] => ''])->all(),
      ];
    }
  @endphp

  <div class="submission-repeating-group" data-repeating-group data-readonly="{{ $readonly ? '1' : '0' }}">
    <input type="hidden" name="{{ $namePrefix }}[{{ $item->id }}][value]" class="repeating-group-value" value="{{ old($namePrefix . '.' . $item->id . '.value', $currentValue) }}">

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
  @error($namePrefix . '.' . $item->id . '.value')<p class="text-danger text-xs pt-1 mb-0">{{ $message }}</p>@enderror
@elseif($valueType == 'date_range')
  <div class="row">
    <div class="col-md-6">
      <input name="{{ $namePrefix }}[{{ $item->id }}][start_date]" class="form-control" type="date" value="{{ old($namePrefix . '.' . $item->id . '.start_date', $currentStartDate) }}" {{ $readonly ? 'disabled' : '' }}>
      @error($namePrefix . '.' . $item->id . '.start_date')<p class="text-danger text-xs pt-1 mb-0">{{ $message }}</p>@enderror
    </div>
    <div class="col-md-6 mt-2 mt-md-0">
      <input name="{{ $namePrefix }}[{{ $item->id }}][end_date]" class="form-control" type="date" value="{{ old($namePrefix . '.' . $item->id . '.end_date', $currentEndDate) }}" {{ $readonly ? 'disabled' : '' }}>
      @error($namePrefix . '.' . $item->id . '.end_date')<p class="text-danger text-xs pt-1 mb-0">{{ $message }}</p>@enderror
    </div>
  </div>
@elseif($valueType == 'date')
  <input name="{{ $namePrefix }}[{{ $item->id }}][value]" class="form-control" type="date" value="{{ old($namePrefix . '.' . $item->id . '.value', $currentValue) }}" {{ $readonly ? 'disabled' : '' }}>
  @error($namePrefix . '.' . $item->id . '.value')<p class="text-danger text-xs pt-1 mb-0">{{ $message }}</p>@enderror
@elseif($valueType == 'boolean')
  <select id="{{ $selectInputId }}" name="{{ $namePrefix }}[{{ $item->id }}][value]" class="form-control uplift-answer-select" {{ $readonly ? 'disabled' : '' }}>
    <option value="">Select</option>
    <option value="Yes" {{ old($namePrefix . '.' . $item->id . '.value', $currentValue) == 'Yes' ? 'selected' : '' }}>Yes</option>
    <option value="No" {{ old($namePrefix . '.' . $item->id . '.value', $currentValue) == 'No' ? 'selected' : '' }}>No</option>
  </select>
  @error($namePrefix . '.' . $item->id . '.value')<p class="text-danger text-xs pt-1 mb-0">{{ $message }}</p>@enderror
@elseif($valueType == 'user_picker')
  @php
    $pickerAgencyId = $submission->agency_id ?? auth()->user()->agency_id;
    $userPickerPayload = json_decode((string) old($namePrefix . '.' . $item->id . '.value', $currentValue), true);
    $selectedUserId = is_array($userPickerPayload) ? ($userPickerPayload['user_id'] ?? null) : null;
    $selectedDesignation = is_array($userPickerPayload) ? ($userPickerPayload['designation'] ?? '') : '';
    $agencyUsers = \App\Models\User::query()
      ->with('position')
      ->where('agency_id', $pickerAgencyId)
      ->orderBy('firstname')
      ->orderBy('lastname')
      ->get();
  @endphp

  <div class="uplift-user-picker" data-user-picker>
    <input
      type="hidden"
      name="{{ $namePrefix }}[{{ $item->id }}][value]"
      class="uplift-user-picker-value"
      value="{{ old($namePrefix . '.' . $item->id . '.value', $currentValue) }}"
    >

    <select
      id="{{ $selectInputId }}"
      class="form-control uplift-answer-select uplift-user-picker-select"
      data-placeholder="Select user"
      {{ $readonly ? 'disabled' : '' }}
    >
      <option value="">Select user</option>
      @foreach($agencyUsers as $agencyUser)
        @php $designation = optional($agencyUser->position)->name; @endphp
        <option
          value="{{ $agencyUser->id }}"
          data-name="{{ trim((string) $agencyUser->full_name) }}"
          data-designation="{{ $designation }}"
          {{ (string) $selectedUserId === (string) $agencyUser->id ? 'selected' : '' }}
        >
          {{ trim((string) $agencyUser->full_name) }}
        </option>
      @endforeach
    </select>

    <input
      class="form-control mt-2 uplift-user-picker-designation"
      type="text"
      value="{{ $selectedDesignation }}"
      placeholder="Designation auto metadata"
      readonly
    >
  </div>
  @error($namePrefix . '.' . $item->id . '.value')<p class="text-danger text-xs pt-1 mb-0">{{ $message }}</p>@enderror
@elseif($valueType == 'select')
  <select id="{{ $selectInputId }}" name="{{ $namePrefix }}[{{ $item->id }}][value]" class="form-control uplift-answer-select" {{ $readonly ? 'disabled' : '' }}>
    <option value="">Select</option>
    @foreach(($item->options ?? []) as $option)
      <option value="{{ $option }}" {{ old($namePrefix . '.' . $item->id . '.value', $currentValue) == $option ? 'selected' : '' }}>{{ $option }}</option>
    @endforeach
  </select>
  @error($namePrefix . '.' . $item->id . '.value')<p class="text-danger text-xs pt-1 mb-0">{{ $message }}</p>@enderror
@else
  <input name="{{ $namePrefix }}[{{ $item->id }}][value]" class="form-control" type="number" step="{{ $valueType == 'decimal' ? '0.01' : '1' }}" value="{{ old($namePrefix . '.' . $item->id . '.value', $currentValue) }}" {{ $readonly ? 'disabled' : '' }}>
  @error($namePrefix . '.' . $item->id . '.value')<p class="text-danger text-xs pt-1 mb-0">{{ $message }}</p>@enderror
@endif
