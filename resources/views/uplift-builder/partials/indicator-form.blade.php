@php
  $prefix = $indicator?->id ?? 'new_' . $field->id;
@endphp

<div class="row">
  <div class="col-lg-5 mb-3">
    <label>Indicator <span class="text-danger">*</span></label>
    <input name="label" class="form-control" type="text" value="{{ old('label', $indicator?->label) }}" placeholder="Indicator">
  </div>
  <div class="col-lg-2 mb-3">
    <label>Unit</label>
    <input name="unit" class="form-control" type="text" value="{{ old('unit', $indicator?->unit) }}" placeholder="Number, PHP">
  </div>
  <div class="col-lg-2 mb-3">
    <label>Order</label>
    <input name="order" class="form-control" type="number" min="1" value="{{ old('order', $indicator?->order ?? 1) }}">
  </div>
  <div class="col-lg-3 mb-3">
    <label>Data Type</label>
    <select name="value_type" id="indicator_value_type_{{ $prefix }}" class="hide-search">
      @foreach(['decimal' => 'Decimal', 'integer' => 'Integer', 'text' => 'Text', 'date' => 'Date', 'date_range' => 'Date Range', 'boolean' => 'Yes/No'] as $value => $label)
        <option value="{{ $value }}" {{ old('value_type', $indicator?->value_type ?? 'decimal') == $value ? 'selected' : '' }}>{{ $label }}</option>
      @endforeach
    </select>
  </div>
</div>
<div class="row align-items-end">
  <div class="col-lg-4 mb-3">
    <label class="d-block">Required</label>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="radio" name="is_required" id="indicator_{{ $prefix }}_required_yes" value="1" {{ old('is_required', $indicator?->is_required ?? 0) == 1 ? 'checked' : '' }}>
      <label class="form-check-label" for="indicator_{{ $prefix }}_required_yes">Yes</label>
    </div>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="radio" name="is_required" id="indicator_{{ $prefix }}_required_no" value="0" {{ old('is_required', $indicator?->is_required ?? 0) == 0 ? 'checked' : '' }}>
      <label class="form-check-label" for="indicator_{{ $prefix }}_required_no">No</label>
    </div>
  </div>
  <div class="col-lg-4 mb-3">
    <label class="d-block">Status</label>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="radio" name="status" id="indicator_{{ $prefix }}_active" value="1" {{ old('status', $indicator?->status ?? 1) == 1 ? 'checked' : '' }}>
      <label class="form-check-label" for="indicator_{{ $prefix }}_active">Active</label>
    </div>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="radio" name="status" id="indicator_{{ $prefix }}_inactive" value="0" {{ old('status', $indicator?->status ?? 1) == 0 ? 'checked' : '' }}>
      <label class="form-check-label" for="indicator_{{ $prefix }}_inactive">Inactive</label>
    </div>
  </div>
</div>
