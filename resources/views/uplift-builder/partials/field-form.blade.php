@php
  $prefix = $field ? 'field_' . $field->id : 'new';
  $sectionOptions = isset($sections) ? $sections : $fields->pluck('section')->filter()->unique()->sort()->values();
  $currentSection = old('section', $field?->section);
  $blockedParentIds = $field ? collect([$field->id])->merge($field->descendantIds()) : collect();
  $fieldOptions = $field?->options ?? [];
  $optionsText = old('options_text', collect(is_array($fieldOptions) && array_is_list($fieldOptions) ? $fieldOptions : [])->implode("\n"));
  $repeatingColumnsText = old('repeating_columns_text', collect(is_array($fieldOptions) ? ($fieldOptions['columns'] ?? []) : [])
    ->map(fn($column, $index) => is_array($column) ? ($column['label'] ?? ('Column ' . ($index + 1))) : (string) $column)
    ->implode("\n"));
@endphp

<div class="mb-3">
  <label>Parent Question</label>
  <select name="parent_id" id="{{ $prefix }}_parent_id">
    <option value="">None - top level question</option>
    @foreach($fields as $option)
      @if(!$blockedParentIds->contains($option->id))
        <option value="{{ $option->id }}" {{ old('parent_id', $field?->parent_id) == $option->id ? 'selected' : '' }}>
          {{ $option->label }}
        </option>
      @endif
    @endforeach
  </select>
  <span class="uplift-guide">Choose a parent to create a question under a question.</span>
</div>

<div class="mb-3">
  <label>Section</label>
  <input type="hidden" name="section" id="{{ $prefix }}_section_value" value="{{ $currentSection }}">
  <select id="{{ $prefix }}_section_select" data-section-prefix="{{ $prefix }}">
    <option value="__none" {{ !$currentSection ? 'selected' : '' }}>No section</option>
    @foreach($sectionOptions as $section)
      <option value="{{ $section }}" {{ $currentSection == $section ? 'selected' : '' }}>{{ $section }}</option>
    @endforeach
    @if($currentSection && !$sectionOptions->contains($currentSection))
      <option value="{{ $currentSection }}" selected>{{ $currentSection }}</option>
    @endif
  </select>
  <span class="uplift-guide">Search an existing section, or type a new section name and press Enter to create another blue header.</span>
</div>

<div class="mb-3">
  <label>Question / Field <span class="text-danger">*</span></label>
  <input name="label" id="{{ $prefix }}_label" class="form-control" type="text" value="{{ old('label', $field?->label) }}" placeholder="Field label">
</div>

<div class="mb-3">
  <label>Guide</label>
  <textarea name="guide" class="form-control" rows="2" placeholder="Short guide from the workbook">{{ old('guide', $field?->guide) }}</textarea>
</div>

<div class="row">
  <div class="col-lg-6 mb-3">
    <label>Line Number <span class="text-danger">*</span></label>
    <input name="row_number" id="{{ $prefix }}_row_number" class="form-control" type="number" min="1" value="{{ old('row_number', $field?->row_number ?? 1) }}">
  </div>
  <div class="col-lg-6 mb-3">
    <label>Position <span class="text-danger">*</span></label>
    <input name="order" id="{{ $prefix }}_order" class="form-control" type="number" min="1" value="{{ old('order', $field?->order ?? 1) }}">
  </div>
</div>

<div class="mb-3">
  <label>Data Type <span class="text-danger">*</span></label>
  <select name="value_type" id="{{ $prefix }}_value_type" class="hide-search">
    @foreach(['text' => 'Text', 'integer' => 'Integer', 'decimal' => 'Decimal', 'date' => 'Date', 'date_range' => 'Date Range', 'select' => 'Select', 'boolean' => 'Yes/No', 'repeating_group' => 'Repeating Group'] as $value => $label)
      <option value="{{ $value }}" {{ old('value_type', $field?->value_type ?? 'text') == $value ? 'selected' : '' }}>{{ $label }}</option>
    @endforeach
  </select>
</div>

<div class="mb-3 uplift-select-options" id="{{ $prefix }}_select_options">
  <label>Select Options</label>
  <textarea name="options_text" class="form-control" rows="4" placeholder="One option per line">{{ $optionsText }}</textarea>
  <span class="uplift-guide">Used only when Data Type is Select. Put one option per line.</span>
</div>

<div class="mb-3 uplift-repeating-options" id="{{ $prefix }}_repeating_options">
  <label>Repeating Group Columns</label>
  <textarea name="repeating_columns_text" class="form-control" rows="4" placeholder="One column per line">{{ $repeatingColumnsText }}</textarea>
  <span class="uplift-guide">Used only when Data Type is Repeating Group. Put one table column per line.</span>
</div>

<div class="row">
  <div class="col-lg-4 mb-3">
    <label class="d-block">Required</label>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="radio" name="is_required" id="{{ $prefix }}_is_required_yes" value="1" {{ old('is_required', $field?->is_required ?? 0) == 1 ? 'checked' : '' }}>
      <label class="form-check-label" for="{{ $prefix }}_is_required_yes">Yes</label>
    </div>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="radio" name="is_required" id="{{ $prefix }}_is_required_no" value="0" {{ old('is_required', $field?->is_required ?? 0) == 0 ? 'checked' : '' }}>
      <label class="form-check-label" for="{{ $prefix }}_is_required_no">No</label>
    </div>
  </div>
  <div class="col-lg-4 mb-3">
    <label class="d-block">Remarks</label>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="radio" name="has_remarks" id="{{ $prefix }}_has_remarks_yes" value="1" {{ old('has_remarks', $field?->has_remarks ?? 0) == 1 ? 'checked' : '' }}>
      <label class="form-check-label" for="{{ $prefix }}_has_remarks_yes">Yes</label>
    </div>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="radio" name="has_remarks" id="{{ $prefix }}_has_remarks_no" value="0" {{ old('has_remarks', $field?->has_remarks ?? 0) == 0 ? 'checked' : '' }}>
      <label class="form-check-label" for="{{ $prefix }}_has_remarks_no">No</label>
    </div>
  </div>
  <div class="col-lg-4 mb-3">
    <label class="d-block">Status</label>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="radio" name="status" id="{{ $prefix }}_status_active" value="1" {{ old('status', $field?->status ?? 1) == 1 ? 'checked' : '' }}>
      <label class="form-check-label" for="{{ $prefix }}_status_active">Active</label>
    </div>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="radio" name="status" id="{{ $prefix }}_status_inactive" value="0" {{ old('status', $field?->status ?? 1) == 0 ? 'checked' : '' }}>
      <label class="form-check-label" for="{{ $prefix }}_status_inactive">Inactive</label>
    </div>
  </div>
</div>
