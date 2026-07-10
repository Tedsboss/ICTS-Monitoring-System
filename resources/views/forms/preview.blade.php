@php
  $class_theme = session('user_settings.class_theme', '');
@endphp

@extends('layouts.app')

@section('content')
  <style>
    .form-preview-page {
      --uplift-blue: #08428f;
      --uplift-blue-2: #145fbd;
      --uplift-blue-3: #2d7bd9;
      --uplift-navy: #05306f;
      --uplift-surface: #f4f9ff;
      --uplift-line: #c9dcf2;
      --uplift-gold: #f8b817;
      color: #16345c;
    }

    .form-preview-card {
      border: 1px solid var(--uplift-line);
      border-radius: 8px;
      box-shadow: 0 10px 26px rgba(8, 66, 143, .08);
      overflow: hidden;
    }

    .form-preview-card > .card-header {
      border-bottom: 1px solid #dbe9f7;
      background: linear-gradient(135deg, #ffffff 0%, #eef7ff 100%);
    }

    .preview-eyebrow {
      color: #3b78aa;
      font-size: .68rem;
      font-weight: 800;
      letter-spacing: .04em;
      text-transform: uppercase;
    }

    .preview-page-title {
      color: var(--uplift-navy);
      font-weight: 800;
      line-height: 1.25;
    }

    .preview-page-subtitle {
      color: #49739e;
    }

    .preview-status-badge {
      display: inline-flex;
      align-items: center;
      min-height: 30px;
      padding: 7px 14px;
      border: 1px solid #f1cb64;
      border-radius: 999px;
      background: #fff7df;
      color: #8a6100;
      font-size: .74rem;
      font-weight: 800;
      text-transform: uppercase;
    }

    .preview-back-btn {
      min-height: 38px;
      border-radius: 8px;
      font-weight: 700;
    }

    .preview-summary {
      border: 1px solid var(--uplift-line);
      border-radius: 8px;
      padding: 18px;
      background: linear-gradient(135deg, #f7fbff 0%, #e6f3ff 100%);
      box-shadow: 0 8px 22px rgba(8, 66, 143, .08);
    }

    .preview-summary-icon,
    .preview-field-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 42px;
      height: 42px;
      border-radius: 8px;
      background: linear-gradient(135deg, var(--uplift-blue) 0%, var(--uplift-blue-3) 100%);
      color: #fff;
      box-shadow: inset 0 -3px 0 rgba(0, 0, 0, .08);
      flex: 0 0 auto;
    }

    .preview-summary-label {
      color: var(--uplift-navy);
      font-size: .82rem;
      font-weight: 800;
    }

    .preview-summary-value {
      color: #16345c;
      font-size: .95rem;
      font-weight: 700;
      line-height: 1.35;
    }

    .preview-section {
      border: 1px solid var(--uplift-line);
      border-radius: 8px;
      background: #f7fbff;
      overflow: hidden;
    }

    .preview-section-header {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      padding: 14px;
      border-bottom: 1px solid #dbe9f7;
      background: linear-gradient(135deg, #ffffff 0%, #eef7ff 100%);
    }

    .preview-section-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      min-width: 36px;
      border-radius: 7px;
      background: #e6f3ff;
      color: var(--uplift-blue);
    }

    .preview-section-title {
      margin: 0;
      color: var(--uplift-navy);
      font-size: .92rem;
      font-weight: 850;
      line-height: 1.3;
    }

    .preview-section-subtitle {
      margin: 3px 0 0;
      color: #55769a;
      font-size: .74rem;
      line-height: 1.4;
    }

    .preview-section-grid {
      display: grid;
      grid-template-columns: repeat(12, minmax(0, 1fr));
      grid-auto-rows: minmax(0, auto);
      gap: 14px;
      padding: 14px;
    }

    .preview-field-cell {
      min-width: 0;
    }

    .preview-field {
      height: 100%;
      border: 1px solid var(--uplift-line);
      border-radius: 8px;
      background: #fff;
      overflow: hidden;
      box-shadow: 0 8px 18px rgba(8, 66, 143, .06);
      display: flex;
      flex-direction: column;
    }

    .preview-field-header {
      display: flex;
      gap: 12px;
      align-items: flex-start;
      padding: 14px 14px 10px;
      background: linear-gradient(180deg, #f5fbff 0%, #ffffff 100%);
      border-bottom: 1px solid #e5eef8;
      min-height: 132px;
    }

    .preview-field-header > div:last-child {
      min-width: 0;
      min-height: 104px;
      display: flex;
      flex-direction: column;
    }

    .preview-field-icon {
      width: 36px;
      height: 36px;
      border-radius: 7px;
      font-size: .9rem;
    }

    .preview-field-title {
      margin: 0;
      color: var(--uplift-navy);
      font-size: .86rem;
      font-weight: 800;
      line-height: 1.35;
      overflow-wrap: anywhere;
    }

    .preview-required {
      display: inline-flex;
      align-items: center;
      margin-left: 4px;
      color: #d3452f;
      font-weight: 900;
    }

    .preview-field-subtitle {
      margin: 4px 0 0;
      color: #55769a;
      font-size: .74rem;
      line-height: 1.4;
    }

    .preview-field-body {
      padding: 14px;
      display: flex;
      flex: 1 1 auto;
      flex-direction: column;
      gap: 14px;
    }

    .preview-field-type {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      margin-top: auto;
      color: #356b9c;
      font-size: .66rem;
      font-weight: 850;
      text-transform: uppercase;
    }

    .preview-main-control {
      display: flex;
      align-items: flex-start;
      width: 100%;
      min-height: 116px;
    }

    .preview-main-control > .preview-control,
    .preview-main-control > .preview-date-pair,
    .preview-main-control > .preview-repeating-group {
      width: 100%;
    }

    .preview-control {
      min-height: 43px;
      border-color: #cfddec !important;
      border-radius: 8px !important;
      background: #f7fbff !important;
      color: #435f7e !important;
      font-weight: 700;
      box-shadow: none !important;
    }

    textarea.preview-control {
      min-height: 116px;
      resize: none;
    }

    .preview-date-pair {
      display: grid;
      gap: 12px;
    }

    .preview-repeating-group {
      display: grid;
      gap: 10px;
    }

    .preview-repeat-header {
      display: grid;
      grid-template-columns: repeat(var(--preview-repeat-column-count, 1), minmax(120px, 1fr));
      gap: 8px;
    }

    .preview-repeat-header span {
      min-width: 0;
      padding: 0 2px;
      color: #356b9c;
      font-size: .66rem;
      font-weight: 850;
      line-height: 1.25;
    }

    .preview-repeat-rows {
      display: grid;
      gap: 8px;
    }

    .preview-repeat-row {
      display: grid;
      grid-template-columns: repeat(var(--preview-repeat-column-count, 1), minmax(120px, 1fr));
      gap: 8px;
    }

    .preview-repeat-cell {
      display: grid;
      min-width: 0;
      margin: 0;
    }

    .preview-repeat-add {
      justify-self: start;
      min-height: 34px;
      padding: 0 12px;
      border: 1px solid var(--uplift-line);
      border-radius: 8px;
      background: #fff;
      color: var(--uplift-blue);
      font-size: .72rem;
      font-weight: 800;
    }

    .preview-repeat-add:hover,
    .preview-repeat-add:focus {
      border-color: var(--uplift-blue-2);
      background: #eef7ff;
    }

    .preview-remarks {
      margin-top: 0;
    }

    .preview-remarks-label {
      display: block;
      color: #356b9c;
      font-size: .74rem;
      font-weight: 800;
      margin-bottom: 8px;
      text-transform: uppercase;
    }

    .preview-section-empty,
    .preview-empty {
      padding: 42px 16px;
      color: #55769a;
      font-size: .86rem;
      text-align: center;
    }

    .preview-empty-icon {
      width: 56px;
      height: 56px;
      border: 1px solid var(--uplift-line);
      border-radius: 14px;
      background: #f4f9ff;
      color: var(--uplift-blue);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 14px;
      font-size: 1.4rem;
    }

    .preview-empty h6 {
      color: var(--uplift-navy);
      font-weight: 800;
      margin-bottom: 4px;
    }

    .preview-empty p {
      margin-bottom: 0;
    }

    @media (max-width: 767.98px) {
      .form-preview-card > .card-header {
        padding-left: 16px;
        padding-right: 16px;
      }

      .form-preview-card > .card-body {
        padding-left: 12px;
        padding-right: 12px;
      }

      .preview-section-grid,
      .preview-repeat-row {
        grid-template-columns: 1fr;
      }

      .preview-field-header,
      .preview-field-header > div:last-child {
        min-height: 0;
      }

      .preview-field-type {
        margin-top: 8px;
      }

      .preview-field-cell {
        grid-column: 1 !important;
        grid-row: auto !important;
      }

      .preview-repeat-header {
        display: none;
      }
    }
  </style>

  <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'Form Preview'])
      @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
  </nav>

  <div class="container-fluid form-preview-page">
    <div class="row mt-4">
      <div class="col-12">
        <div class="card form-preview-card">
          <div class="card-header">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
              <div>
                <p class="preview-eyebrow mb-1">Interactive Form Preview</p>
                <h5 class="preview-page-title mb-0">{{ $form->title ?? 'Form Preview' }}</h5>
                <p class="preview-page-subtitle text-sm mb-0">
                  {{ optional($form->agency)->display_name ?? optional($form->agency)->UACS_AGY_DSC ?? 'No agency assigned' }}
                </p>
              </div>

              <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2">
                <span class="preview-status-badge">
                  <i class="fa fa-eye me-2"></i>
                  Preview
                </span>

                <a href="{{ route('forms.edit', $form) }}" class="btn btn-outline-primary preview-back-btn mb-0">
                  <i class="fa fa-arrow-left me-2"></i>
                  Back to Edit
                </a>
              </div>
            </div>
          </div>

          <div class="card-body p-3">
            @php
              $activeFields = $form->fields
                ->where('status', 1)
                ->sortBy([
                  ['row_number', 'asc'],
                  ['order', 'asc'],
                  ['id', 'asc'],
                ])
                ->values();

              $sections = $activeFields
                ->where('value_type', 'section')
                ->values();

              $regularFields = $activeFields
                ->where('value_type', '!=', 'section')
                ->values();

              $fieldsBySection = $regularFields->groupBy(function ($field) {
                return (int) $field->row_number;
              });

              $sectionGroups = $sections->map(function ($section) use ($fieldsBySection) {
                return [
                  'section' => $section,
                  'fields' => ($fieldsBySection[(int) $section->row_number] ?? collect())
                    ->sortBy([
                      ['order', 'asc'],
                      ['id', 'asc'],
                    ])
                    ->values(),
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

              $typeLabels = [
                'integer' => 'Integer',
                'decimal' => 'Decimal',
                'text' => 'Text',
                'date' => 'Date',
                'date_range' => 'Date Range',
                'repeating_group' => 'Repeating Group',
              ];

              $typeIcons = [
                'integer' => 'fa-hashtag',
                'decimal' => 'fa-line-chart',
                'text' => 'fa-align-left',
                'date' => 'fa-calendar',
                'date_range' => 'fa-calendar-check-o',
                'repeating_group' => 'fa-list-alt',
              ];
            @endphp

            <div class="preview-summary mb-4">
              <div class="row align-items-center g-3">
                <div class="col-lg-8">
                  <div class="d-flex align-items-start">
                    <div class="preview-summary-icon me-3">
                      <i class="fa fa-file-text-o"></i>
                    </div>
                    <div>
                      <p class="preview-eyebrow mb-1">Agency Form</p>
                      <div class="preview-summary-label mb-1">{{ $form->title ?? 'Untitled Form' }}</div>
                      <div class="preview-summary-value">
                        {{ optional($form->agency)->display_name ?? optional($form->agency)->UACS_AGY_DSC ?? 'No agency assigned' }}
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-lg-4">
                  <div class="text-lg-end">
                    <p class="preview-eyebrow mb-1">Active Fields</p>
                    <div class="preview-summary-value">
                      {{ $regularFields->count() }} {{ Str::plural('field', $regularFields->count()) }}
                    </div>
                    <p class="text-xs text-muted mb-0 mt-1">
                      {{ $sections->count() }} {{ Str::plural('section', $sections->count()) }} available
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <div class="preview-grid">
              @forelse($sectionGroups as $sectionGroup)
                @php
                  $section = $sectionGroup['section'];
                  $sectionFields = $sectionGroup['fields'];
                @endphp

                <div class="preview-section mb-4">
                  @if($section)
                    <div class="preview-section-header">
                      <div class="preview-section-icon">
                        <i class="fa fa-folder-open"></i>
                      </div>

                      <div>
                        <h6 class="preview-section-title">{{ $section->label }}</h6>

                        @if(!empty($section->subtitle))
                          <p class="preview-section-subtitle">{{ $section->subtitle }}</p>
                        @endif
                      </div>
                    </div>
                  @endif

                  @if($sectionFields->isNotEmpty())
                    <div class="preview-section-grid">
                      @foreach($sectionFields as $field)
                        @php
                          $fieldType = $field->value_type;
                          $fieldLabel = $typeLabels[$fieldType] ?? ucfirst(str_replace('_', ' ', $fieldType));
                          $fieldIcon = $typeIcons[$fieldType] ?? 'fa-circle';
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

                          $repeatingColumns = [];
                          if ($field->value_type === 'repeating_group') {
                            $fieldOptions = is_array($field->options) ? $field->options : [];
                            $optionColumns = $fieldOptions['columns'] ?? [];
                            $repeatingColumns = collect(is_array($optionColumns) ? $optionColumns : [])
                              ->map(function ($column, $index) {
                                return [
                                  'id' => (string) ($column['id'] ?? ('col_' . ($index + 1))),
                                  'label' => (string) ($column['label'] ?? ('Column ' . ($index + 1))),
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
                                ],
                              ];
                            }
                          }
                        @endphp

                        <div class="preview-field-cell" style="grid-column: {{ $fieldGridColumn }}; grid-row: {{ $fieldRow }};">
                          <div class="preview-field">
                            <div class="preview-field-header">
                              <div class="preview-field-icon">
                                <i class="fa {{ $fieldIcon }}"></i>
                              </div>

                              <div>
                                <label class="preview-field-title">
                                  {{ $field->label }}

                                  @if((int) $field->is_required === 1)
                                    <span class="preview-required">*</span>
                                  @endif
                                </label>

                                @if(!empty($field->subtitle))
                                  <p class="preview-field-subtitle">{{ $field->subtitle }}</p>
                                @endif

                                <div class="preview-field-type">
                                  <i class="fa {{ $fieldIcon }}"></i>
                                  {{ $fieldLabel }}
                                </div>
                              </div>
                            </div>

                            <div class="preview-field-body">
                              <div class="preview-main-control">
                                @switch($field->value_type)
                                  @case('integer')
                                    <input
                                      type="number"
                                      step="1"
                                      class="form-control preview-control"
                                      placeholder="Whole number"
                                    >
                                    @break

                                  @case('decimal')
                                    <input
                                      type="number"
                                      step="0.01"
                                      class="form-control preview-control"
                                      placeholder="Decimal number"
                                    >
                                    @break

                                  @case('date')
                                    <input
                                      type="date"
                                      class="form-control preview-control"
                                      placeholder="YYYY-MM-DD"
                                    >
                                    @break

                                  @case('date_range')
                                    <div class="preview-date-pair">
                                      <input
                                        type="date"
                                        class="form-control preview-control"
                                        placeholder="Start date"
                                      >

                                      <input
                                        type="date"
                                        class="form-control preview-control"
                                        placeholder="End date"
                                      >
                                    </div>
                                    @break

                                  @case('repeating_group')
                                    <div class="preview-repeating-group">
                                      <div class="preview-repeat-header" style="--preview-repeat-column-count: {{ max(1, count($repeatingColumns)) }};">
                                        @foreach($repeatingColumns as $column)
                                          <span>{{ $column['label'] }}</span>
                                        @endforeach
                                      </div>

                                      <div class="preview-repeat-rows">
                                        <div class="preview-repeat-row" style="--preview-repeat-column-count: {{ max(1, count($repeatingColumns)) }};">
                                          @foreach($repeatingColumns as $column)
                                            <div class="preview-repeat-cell">
                                              <input
                                                type="text"
                                                class="form-control preview-control"
                                                aria-label="{{ $column['label'] }}"
                                              >
                                            </div>
                                          @endforeach
                                        </div>
                                      </div>

                                      <button type="button" class="preview-repeat-add">
                                        <i class="fa fa-plus me-1"></i>
                                        Add Row
                                      </button>
                                    </div>
                                    @break

                                  @case('text')
                                  @default
                                    <textarea
                                      class="form-control preview-control"
                                      rows="4"
                                      placeholder="Text response"
                                    ></textarea>
                                    @break
                                @endswitch
                              </div>

                              @if((int) $field->has_remarks === 1)
                                <div class="preview-remarks">
                                  <span class="preview-remarks-label">Remarks</span>

                                  <textarea
                                    class="form-control preview-control"
                                    rows="4"
                                    placeholder="Optional remarks"
                                  ></textarea>
                                </div>
                              @endif
                            </div>
                          </div>
                        </div>
                      @endforeach
                    </div>
                  @else
                    <div class="preview-section-empty">
                      No active fields inside this section.
                    </div>
                  @endif
                </div>
              @empty
                <div class="preview-empty">
                  <div class="preview-empty-icon">
                    <i class="fa fa-folder-open"></i>
                  </div>

                  <h6>No active fields available</h6>
                  <p>Add or activate fields in the form builder to preview the form.</p>
                </div>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </div>

    @include('layouts.footers.auth.footer')
  </div>
@endsection

@section('scripts')
  <script>
    document.addEventListener('click', function (event) {
      const addButton = event.target.closest('.preview-repeat-add');

      if (!addButton) {
        return;
      }

      const group = addButton.closest('.preview-repeating-group');
      const rows = group ? group.querySelector('.preview-repeat-rows') : null;
      const firstRow = rows ? rows.querySelector('.preview-repeat-row') : null;

      if (!group || !rows || !firstRow) {
        return;
      }

      const newRow = firstRow.cloneNode(true);

      newRow.querySelectorAll('input').forEach(function (input) {
        input.value = '';
      });

      rows.appendChild(newRow);
    });
  </script>
@endsection
