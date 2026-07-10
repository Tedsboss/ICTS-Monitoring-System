@php
  $readonly = $readonly ?? false;
  $level = $level ?? 0;
  $gridSpan = $gridSpan ?? 12;
  $gridColumn = $gridColumn ?? null;
  $gridRow = $gridRow ?? null;
  $fieldValue = $fieldValues->get($field->id);
  $activeIndicators = $field->indicators->where('status', 1);
  $activeChildren = $field->children->where('status', 1);
  $typeLabels = [
    'integer' => 'Integer',
    'decimal' => 'Decimal',
    'text' => 'Text',
    'date' => 'Date',
    'date_range' => 'Date Range',
    'select' => 'Select',
    'boolean' => 'Yes / No',
    'user_picker' => 'User Picker',
    'auto_metadata' => 'Auto Metadata',
    'repeating_group' => 'Repeating Group',
  ];
  $typeIcons = [
    'integer' => 'fa-hashtag',
    'decimal' => 'fa-calculator',
    'text' => 'fa-align-left',
    'date' => 'fa-calendar',
    'date_range' => 'fa-calendar-o',
    'select' => 'fa-list',
    'boolean' => 'fa-toggle-on',
    'user_picker' => 'fa-user-o',
    'auto_metadata' => 'fa-id-badge',
    'repeating_group' => 'fa-list-alt',
  ];
  $fieldType = $typeLabels[$field->value_type] ?? ucfirst(str_replace('_', ' ', $field->value_type));
  $fieldIcon = $typeIcons[$field->value_type] ?? 'fa-circle';
@endphp

<div class="{{ $level === 0 ? 'submission-field-cell' : '' }}" style="{{ $level === 0 ? 'grid-column: ' . ($gridColumn ?? ('span ' . $gridSpan)) . ';' . ($gridRow ? ' grid-row: ' . $gridRow . ';' : '') : '' }}">
  <div class="submission-field uplift-submission-field {{ $level > 0 ? 'is-nested' : '' }}">
    <div class="submission-field-header">
      <div class="submission-field-icon">
        <i class="fa {{ $fieldIcon }}"></i>
      </div>
      <div>
        <label class="submission-field-title">
          {{ $field->label }}
          @if($field->is_required == 1)<span class="submission-required">*</span>@endif
        </label>

        @if($field->guide)
          <p class="submission-field-subtitle">{{ $field->guide }}</p>
        @else
          <p class="submission-field-subtitle">{{ $fieldType }}</p>
        @endif
      </div>
    </div>

    <div class="submission-field-body">
      @include('uplift-submissions.partials.input', ['item' => $field, 'value' => $fieldValue, 'namePrefix' => 'fields', 'readonly' => $readonly])

      @if($field->has_remarks == 1)
        <label class="submission-remarks-label mt-3 mb-2">Remarks</label>
        <textarea name="fields[{{ $field->id }}][remarks]" class="form-control" rows="4" {{ $readonly ? 'disabled' : '' }}>{{ old('fields.' . $field->id . '.remarks', optional($fieldValue)->remarks) }}</textarea>
        @error('fields.' . $field->id . '.remarks')<p class="text-danger text-xs pt-1 mb-0">{{ $message }}</p>@enderror
      @endif

      @if($activeIndicators->count() > 0)
        <div class="uplift-indicators">
          <span class="uplift-indicator-title">Indicators</span>
          @foreach($activeIndicators as $indicator)
            @php $indicatorValue = $indicatorValues->get($indicator->id); @endphp
            <div class="uplift-indicator">
              <label class="submission-field-title mb-2">
                {{ $indicator->label }}
                @if($indicator->unit)<span class="text-xs text-muted">({{ $indicator->unit }})</span>@endif
                @if($indicator->is_required == 1)<span class="submission-required">*</span>@endif
              </label>
              @include('uplift-submissions.partials.input', ['item' => $indicator, 'value' => $indicatorValue, 'namePrefix' => 'indicators', 'readonly' => $readonly])
            </div>
          @endforeach
        </div>
      @endif

      @foreach($activeChildren as $child)
        @include('uplift-submissions.partials.field', [
          'field' => $child,
          'fieldValues' => $fieldValues,
          'indicatorValues' => $indicatorValues,
          'readonly' => $readonly,
          'level' => $level + 1,
          'gridSpan' => 12,
        ])
      @endforeach
    </div>
  </div>
</div>
