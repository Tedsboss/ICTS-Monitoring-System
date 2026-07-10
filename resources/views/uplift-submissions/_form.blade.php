@php
  $readonly = $readonly ?? false;
  $previewMode = $previewMode ?? false;
  $topLevelFields = $measure->fields
    ->whereNull('parent_id')
    ->where('status', 1)
    ->where('value_type', '!=', 'section')
    ->sortBy([
      ['row_number', 'asc'],
      ['order', 'asc'],
      ['id', 'asc'],
    ]);
  $sectionGroups = $topLevelFields->groupBy(fn($field) => $field->section ?: 'General Information');
@endphp

@include('submissions.form_css')

<style>
  .uplift-submission-field.is-nested {
    margin-top: 14px;
    border-color: #dbe9f7;
    background: #fbfdff;
    box-shadow: none;
  }

  .uplift-indicators {
    display: grid;
    gap: 10px;
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid #e5eef8;
  }

  .uplift-indicator-title {
    display: block;
    color: #356b9c;
    font-size: .74rem;
    font-weight: 800;
    margin-bottom: 0;
    text-transform: uppercase;
  }

  .uplift-indicator {
    border: 1px solid #dbe9f7;
    border-radius: 8px;
    padding: 12px;
    background: #fbfdff;
  }

  .uplift-user-picker {
    position: relative;
    z-index: 5;
  }

  .uplift-user-picker .ts-wrapper {
    width: 100%;
  }

  body > .ts-dropdown,
  body > .ts-dropdown.plugin-dropdown_input {
    z-index: 9999;
  }
</style>

@if(!$readonly && !$previewMode)
  @csrf
  <input type="hidden" name="uplift_measure_id" value="{{ old('uplift_measure_id', $measure->id) }}">
@endif

<div class="submission-shell uplift-submission-shell">
  <div class="submission-summary mb-4">
    <div class="row align-items-center g-3">
      <div class="col-lg-8">
        <div class="d-flex align-items-start">
          <div class="submission-summary-icon me-3">
            <i class="fa fa-calendar-o"></i>
          </div>
          <div class="w-100">
            <p class="submission-eyebrow mb-1">Reporting Period</p>
            <label class="submission-label mb-2" for="reporting_cutoff_date">
              Update as of <span class="text-danger">*</span>
            </label>
            <div class="input-group submission-date-picker date-picker-field {{ $readonly ? '' : 'cursor-pointer' }}" data-date-picker-target="reporting_cutoff_date">
              <span class="input-group-text pe-2"><i class="fa fa-calendar-o"></i></span>
              <input id="reporting_cutoff_date" name="reporting_cutoff_date" class="form-control" type="date" value="{{ old('reporting_cutoff_date', $submission->reporting_cutoff_date?->format('Y-m-d')) }}" {{ $readonly ? 'disabled' : '' }}>
            </div>
            @error('reporting_cutoff_date')<p class="text-danger text-xs mb-0 mt-1">{{ $message }}</p>@enderror
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="submission-status-panel">
          <p class="submission-eyebrow mb-1">{{ $previewMode ? 'Preview Status' : 'Submission Status' }}</p>
          <div class="d-flex align-items-center justify-content-lg-end">
            <span class="submission-status-badge {{ $submission->status == 'submitted' ? 'is-submitted' : 'is-draft' }}">
              {{ $previewMode ? 'Preview Only' : ($submission->status ? ucfirst($submission->status) : 'Draft') }}
            </span>
          </div>
          @if($previewMode)
            <p class="text-xs text-muted mb-0 mt-2">Editable test view only. This page does not save or submit entries.</p>
          @elseif($submission->submitted_at)
            <p class="text-xs text-muted mb-0 mt-2">Submitted {{ $submission->submitted_at->format('Y-m-d H:i:s') }}</p>
          @else
            <p class="text-xs text-muted mb-0 mt-2">Drafts can be incomplete. Final submission checks required fields.</p>
          @endif
        </div>
      </div>
    </div>
  </div>

  <div class="submission-grid mt-4">
    @forelse($sectionGroups as $sectionName => $sectionFields)
      <div class="submission-section mb-4">
        <div class="submission-section-header">
          <div class="submission-section-icon">
            <i class="fa fa-folder-open"></i>
          </div>
          <div>
            <h6 class="submission-section-title">{{ $sectionName }}</h6>
            @if($sectionName === 'General Information' && $measure->brief_description)
              <p class="submission-section-subtitle">{{ $measure->brief_description }}</p>
            @endif
          </div>
        </div>

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
              $rowHasFullField = $rowFields->contains(fn($item) => (int) $item->column_size === 12 || $item->value_type === 'repeating_group');
              $normalRowFields = $rowFields->filter(fn($item) => (int) $item->column_size !== 12 && $item->value_type !== 'repeating_group')->values();

              if ((int) $field->column_size === 12 || $field->value_type === 'repeating_group') {
                $fieldGridColumn = '1 / -1';
              } elseif (!$rowHasFullField && $normalRowFields->count() === 1) {
                $fieldGridColumn = '1 / -1';
              } elseif (!$rowHasFullField && $normalRowFields->count() === 2) {
                $visualIndex = $normalRowFields->search(fn($item) => (string) $item->id === (string) $field->id);
                $fieldGridColumn = $visualIndex === 0 ? '1 / 7' : '7 / 13';
              } else {
                $fieldGridStart = (($fieldColumn - 1) * 4) + 1;
                $fieldGridColumn = $fieldGridStart . ' / ' . ($fieldGridStart + 4);
              }
            @endphp
            @include('uplift-submissions.partials.field', [
              'field' => $field,
              'fieldValues' => $fieldValues,
              'indicatorValues' => $indicatorValues,
              'readonly' => $readonly,
              'level' => 0,
              'gridColumn' => $fieldGridColumn,
              'gridRow' => $fieldRow,
            ])
          @endforeach
        </div>
      </div>
    @empty
      <div class="submission-section-empty">
        No active fields have been configured for this UPLIFT measure.
      </div>
    @endforelse
  </div>

  @if(!$readonly && !$previewMode)
    <div class="submission-actions">
      <button class="submission-action-btn submission-action-save" type="submit">
        <i class="fa fa-save"></i>
        <span>Save Draft</span>
      </button>
      @if($submission->exists && !$submission->isSubmitted())
        <button class="submission-action-btn submission-action-submit" type="submit" form="uplift-submit-form" onclick="return confirm('Submit this UPLIFT report?')">
          <i class="fa fa-paper-plane"></i>
          <span>Submit</span>
        </button>
      @endif
    </div>
  @endif
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof initTomSelect !== 'function') {
      return;
    }

    document.querySelectorAll('.uplift-answer-select[id]').forEach(function (select) {
      if (window.tomSelects && window.tomSelects[select.id]) {
        return;
      }

      initTomSelect(select.id, true, false, true);
    });

    document.querySelectorAll('[data-user-picker]').forEach(function (picker) {
      const select = picker.querySelector('.uplift-user-picker-select');
      const hidden = picker.querySelector('.uplift-user-picker-value');
      const designationInput = picker.querySelector('.uplift-user-picker-designation');

      if (!select || !hidden || !designationInput) {
        return;
      }

      const syncUserPicker = function () {
        const option = select.options[select.selectedIndex];

        if (!option || !option.value) {
          hidden.value = '';
          designationInput.value = '';
          return;
        }

        const payload = {
          user_id: Number(option.value),
          name: option.dataset.name || option.textContent.trim(),
          designation: option.dataset.designation || ''
        };

        hidden.value = JSON.stringify(payload);
        designationInput.value = payload.designation;
      };

      select.addEventListener('change', syncUserPicker);
      syncUserPicker();
    });
  });
</script>
