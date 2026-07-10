@php
  $class_theme = session('user_settings.class_theme', '');
  $class = trim(($class ?? '') . ' form-builder-sidenav-page g-sidenav-hidden');
  $canUpdate = auth()->user()->can('update', $measure->pillar);
  $canDelete = auth()->user()->can('delete', $measure->pillar);

  $topLevelFields = $fields->whereNull('parent_id')->sortBy([
    ['row_number', 'asc'],
    ['order', 'asc'],
    ['id', 'asc'],
  ]);

  $builderSectionGroups = $topLevelFields
    ->where('value_type', '!=', 'section')
    ->groupBy(fn($field) => $field->section ?: 'General Information');
  $builderFields = collect();

  $topLevelFields
    ->groupBy('row_number')
    ->each(function ($rowFields) use (&$builderFields) {
      $sectionField = $rowFields->firstWhere('value_type', 'section');
      $sectionQuestionFields = $rowFields->where('value_type', '!=', 'section')->values();
      $sectionName = $sectionField?->label
        ?? $sectionQuestionFields->first()?->section
        ?? 'General Information';
      $rowNumber = $builderFields->where('value_type', 'section')->count() + 1;

      $builderFields->push([
        'id' => $sectionField ? (string) $sectionField->id : 'uplift_section_' . $rowNumber,
        'is_new' => !$sectionField,
        'label' => $sectionName,
        'subtitle' => $sectionField?->guide ?? '',
        'value_type' => 'section',
        'options' => [],
        'column_size' => 12,
        'row_number' => $rowNumber,
        'order' => 0,
        'is_required' => 0,
        'has_remarks' => 0,
        'status' => (int) ($sectionField?->status ?? 1),
      ]);

      $sectionQuestionFields->each(function ($field) use (&$builderFields, $rowNumber) {
        $builderFields->push([
          'id' => (string) $field->id,
          'is_new' => false,
          'label' => $field->label,
          'subtitle' => $field->guide,
          'value_type' => $field->value_type,
          'options' => $field->options ?? [],
          'column_size' => (int) ($field->column_size ?? 4),
          'row_number' => $rowNumber,
          'order' => (int) ($field->order ?? 1),
          'is_required' => (int) $field->is_required,
          'has_remarks' => (int) $field->has_remarks,
          'status' => (int) $field->status,
        ]);
      });
    });

  $builderFields = $builderFields->values()->all();
@endphp

@extends('layouts.app')

@section('content')
  @include('forms.partials.edit.css')

  <style>
    /* Keep the Lead Agency Tom Select dropdown from widening the whole builder page. */
    .builder-page,
    .builder-details-card,
    .builder-details-body,
    .builder-details-grid,
    .builder-field-group {
      max-width: 100%;
      min-width: 0;
    }

    .builder-page {
      overflow-x: clip;
    }

    #lead_agency_id + .agency-tomselect,
    .agency-tomselect.ts-wrapper {
      width: 100% !important;
      max-width: 100% !important;
      min-width: 0 !important;
    }

    .agency-tomselect .ts-control {
      width: 100% !important;
      max-width: 100% !important;
      min-width: 0 !important;
      overflow: hidden;
    }

    .agency-tomselect .ts-control .item,
    .agency-tomselect .ts-control input {
      min-width: 0 !important;
      max-width: 100% !important;
    }

    .agency-tomselect-dropdown,
    .agency-tomselect .ts-dropdown {
      width: 100% !important;
      min-width: 100% !important;
      max-width: 100% !important;
      left: 0 !important;
      right: auto !important;
      box-sizing: border-box !important;
      overflow: visible !important;
      max-height: none !important;
    }

    .agency-tomselect-dropdown .ts-dropdown-content {
      max-width: 100% !important;
      max-height: 260px !important;
      overflow-x: hidden !important;
      overflow-y: auto !important;
    }

    .agency-tomselect-dropdown .option {
      max-width: 100% !important;
      overflow-x: hidden !important;
      white-space: normal !important;
      overflow-wrap: anywhere;
      word-break: break-word;
    }


    /* Hide unused row/column placeholder boxes in the UPLIFT builder canvas.
       These were showing as Row 4 / Column 1, Column 2, Column 3 and made
       the measure builder look like it had unnecessary extra rows. */
    .builder-page .section-slot-placeholder {
      display: none !important;
    }

    .builder-page .section-row-drop-target {
      min-height: 18px !important;
      opacity: .45;
    }

    /* Keep only one visible drop target per section to avoid duplicate
       section-row-drop-target bars in the builder canvas. */
    .builder-page .section-field-list > .section-row-drop-target {
      display: none !important;
    }

    .builder-page .section-field-list > .section-row-drop-target:last-child {
      display: block !important;
    }

    .builder-page .section-row-drop-target span {
      display: none !important;
    }

    .builder-page .section-row-drop-target.is-dragover {
      min-height: 34px !important;
      opacity: 1;
    }

    .builder-page .builder-section-actions {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      align-self: flex-start;
      justify-self: end;
      margin-left: auto;
    }

    .builder-page .builder-section-move {
      width: 30px;
      height: 30px;
      border-radius: 8px;
      border: 1px solid #d8e4f1;
      background: #fff;
      color: #08428f;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: .72rem;
      transition: .15s ease;
    }

    .builder-page .builder-section-move:not(:disabled):hover {
      background: #eef7ff;
      border-color: #bfd2e8;
    }

    .builder-page .builder-section-move:disabled {
      opacity: .42;
      cursor: not-allowed;
    }

    .builder-page .builder-section.is-section-drop-before,
    .builder-page .builder-section.is-section-drop-after {
      position: relative;
    }

    .builder-page .builder-section.is-section-drop-before::before,
    .builder-page .builder-section.is-section-drop-after::after {
      content: "";
      position: absolute;
      left: 12px;
      right: 12px;
      height: 4px;
      border-radius: 999px;
      background: #08428f;
      box-shadow: 0 0 0 4px rgba(8, 66, 143, .12);
      z-index: 2;
    }

    .builder-page .builder-section.is-section-drop-before::before {
      top: -2px;
    }

    .builder-page .builder-section.is-section-drop-after::after {
      bottom: -2px;
    }
  </style>

  <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'UPLIFT Form Builder'])
      @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
  </nav>

  <div class="container-fluid builder-page py-3">
    <form id="form-details-form" method="post" action="{{ route('uplift-builder.measures.update', $measure) }}">
      @csrf
      @method('put')

      <div id="builderGeneratedInputs"></div>

      <div class="builder-details-card mb-3">
        <div class="builder-details-header">
          <div class="builder-details-heading">
            <div class="builder-details-icon">
              <i class="fa fa-line-chart"></i>
            </div>

            <div>
              <h6 class="builder-details-title">UPLIFT Measure Details</h6>
              <p class="builder-details-subtitle">
                Manage the measure, lead agency, assigned sector, and publishing status.
              </p>
            </div>
          </div>

          <span class="builder-details-status {{ (int) $measure->status === 1 ? 'is-active' : 'is-inactive' }}">
            <span></span>
            {{ (int) $measure->status === 1 ? 'Active' : 'Inactive' }}
          </span>
        </div>

        <div class="builder-details-body">
          <div class="builder-details-grid">
            <div class="builder-field-group">
              <label for="uplift_pillar_id" class="builder-field-label">
                Pillar <span class="text-danger">*</span>
              </label>

              <select
                name="uplift_pillar_id"
                id="uplift_pillar_id"
                autocomplete="off"
                class="builder-compact-select @error('uplift_pillar_id') is-invalid @enderror"
                placeholder="Select pillar"
                {{ !$canUpdate ? 'disabled' : '' }}
              >
                @foreach($pillars as $pillar)
                  <option value="{{ $pillar->id }}" {{ (string) old('uplift_pillar_id', $measure->uplift_pillar_id) === (string) $pillar->id ? 'selected' : '' }}>
                    {{ $pillar->title }}
                  </option>
                @endforeach
              </select>

              @error('uplift_pillar_id')
                <p class="builder-error-text">{{ $message }}</p>
              @enderror
            </div>

            <div class="builder-field-group builder-field-title">
              <label for="title" class="builder-field-label">
                Title of Response / Measure / Assistance <span class="text-danger">*</span>
              </label>

              <input
                name="title"
                id="title"
                class="form-control builder-compact-input @error('title') is-invalid @enderror"
                type="text"
                value="{{ old('title', $measure->title) }}"
                placeholder="UPLIFT measure title"
                {{ !$canUpdate ? 'disabled' : '' }}
              >

              @error('title')
                <p class="builder-error-text">{{ $message }}</p>
              @enderror
            </div>

            <div class="builder-field-group">
              <label for="lead_agency_id" class="builder-field-label">
                Lead Agency
              </label>

              <select
                name="lead_agency_id"
                id="lead_agency_id"
                autocomplete="off"
                class="agency-select @error('lead_agency_id') is-invalid @enderror"
                placeholder="Search or select lead agency..."
                data-placeholder="Search or select lead agency..."
                {{ !$canUpdate ? 'disabled' : '' }}
              >
                <option value="">Select lead agency</option>
                @foreach($agencies as $agency)
                  <option value="{{ $agency->id }}" {{ (string) old('lead_agency_id', $measure->lead_agency_id) === (string) $agency->id ? 'selected' : '' }}>
                    {{ $agency->selection_name }}
                  </option>
                @endforeach
              </select>

              @error('lead_agency_id')
                <p class="builder-error-text">{{ $message }}</p>
              @enderror
            </div>

            <div class="builder-field-group">
              <label for="assigned_sector_id" class="builder-field-label">
                Assigned Sector
              </label>

              <select
                name="assigned_sector_id"
                id="assigned_sector_id"
                autocomplete="off"
                class="builder-compact-select @error('assigned_sector_id') is-invalid @enderror"
                placeholder="Select sector"
                {{ !$canUpdate ? 'disabled' : '' }}
              >
                <option value="">Select sector</option>
                @foreach($sectors as $sector)
                  <option value="{{ $sector->id }}" {{ (string) old('assigned_sector_id', $measure->assigned_sector_id) === (string) $sector->id ? 'selected' : '' }}>
                    {{ $sector->name }} ({{ $sector->abbreviation }})
                  </option>
                @endforeach
              </select>

              @error('assigned_sector_id')
                <p class="builder-error-text">{{ $message }}</p>
              @enderror
            </div>

            <div class="builder-field-group builder-field-title">
              <label for="brief_description" class="builder-field-label">
                Brief Description
              </label>

              <textarea
                name="brief_description"
                id="brief_description"
                class="form-control builder-compact-input @error('brief_description') is-invalid @enderror"
                rows="3"
                placeholder="Optional description"
                {{ !$canUpdate ? 'disabled' : '' }}
              >{{ old('brief_description', $measure->brief_description) }}</textarea>

              @error('brief_description')
                <p class="builder-error-text">{{ $message }}</p>
              @enderror
            </div>

            <div class="builder-field-group">
              <label class="builder-field-label">
                Status <span class="text-danger">*</span>
              </label>

              <div class="builder-status-toggle">
                <label class="builder-status-option">
                  <input
                    type="radio"
                    name="status"
                    id="measure_status_active"
                    value="1"
                    {{ (string) old('status', $measure->status) === '1' ? 'checked' : '' }}
                    {{ !$canUpdate ? 'disabled' : '' }}
                  >
                  <span>Active</span>
                </label>

                <label class="builder-status-option">
                  <input
                    type="radio"
                    name="status"
                    id="measure_status_inactive"
                    value="0"
                    {{ (string) old('status', $measure->status) === '0' ? 'checked' : '' }}
                    {{ !$canUpdate ? 'disabled' : '' }}
                  >
                  <span>Inactive</span>
                </label>
              </div>

              @error('status')
                <p class="builder-error-text">{{ $message }}</p>
              @enderror
            </div>
          </div>
        </div>

        <div class="builder-details-footer">
          <div class="builder-details-meta">
            <span>
              <i class="fa fa-check-circle-o"></i>
              <strong id="activeCountMeta">0</strong> active
            </span>

            <span>
              <i class="fa fa-list-ul"></i>
              <strong id="totalCountMeta">0</strong> total
            </span>
          </div>

          <div class="builder-details-actions">
            <a href="{{ route('uplift-builder.index') }}" class="builder-secondary-btn">
              <i class="fa fa-arrow-left"></i>
              Back
            </a>

            <a href="{{ route('uplift-builder.preview', $measure) }}" class="builder-secondary-btn">
              <i class="fa fa-eye"></i>
              Preview
            </a>

            @can('update', $measure->pillar)
              <button class="builder-primary-btn" type="submit">
                <i class="fa fa-save"></i>
                Save Form
              </button>
            @endcan
          </div>
        </div>
      </div>

      <div class="builder-shell">
        <aside class="builder-sidebar">
          <section class="builder-card builder-panel settings-panel" id="settingsPanel">
            <div class="builder-card-header settings-panel-header">
              <div>
                <p class="builder-card-title">Field Settings</p>
                <p class="builder-card-subtitle">Edit the selected section or field.</p>
              </div>

              <div class="settings-header-actions">
                <span id="settingsSelectedBadge" class="settings-selected-badge" style="display: none;">
                  Selected
                </span>

                <button type="button" class="settings-collapse-toggle" id="settingsCollapseToggle" aria-expanded="true" aria-controls="settingsPanelContent" title="Show or hide field settings">
                  <i class="fa fa-chevron-up"></i>
                </button>
              </div>
            </div>

            <div id="settingsPanelContent">
              <div class="settings-panel-scroll builder-panel-body">
                <div id="settingsEmpty" class="settings-empty">
                  <div class="settings-empty-icon">
                    <i class="fa fa-sliders"></i>
                  </div>
                  <h6>Select an item</h6>
                  <p>Click a section or field from the canvas to update its settings here.</p>
                </div>

                <div id="settingsForm" style="display: none;">
                  <div class="settings-group-card">
                    <p class="settings-section-title">Content</p>

                    <div class="mb-3">
                      <label>Title / Field Label <span class="text-danger">*</span></label>
                      <input
                        id="settingLabel"
                        class="form-control builder-setting-input"
                        type="text"
                        placeholder="Section title or field label"
                        {{ !$canUpdate ? 'disabled' : '' }}
                      >
                    </div>

                    <div class="mb-0">
                      <label>Helper Text</label>
                      <textarea
                        id="settingSubtitle"
                        class="form-control builder-setting-input"
                        rows="3"
                        placeholder="Optional short guide"
                        {{ !$canUpdate ? 'disabled' : '' }}
                      ></textarea>
                      <span class="field-help-text">This appears below the selected section or field.</span>
                    </div>
                  </div>

                  <div class="settings-group-card mt-3">
                    <p class="settings-section-title">Behavior</p>

                    <div class="mb-3 field-behavior-option">
                      <label>Data Type</label>
                      <select id="settingValueType" class="form-control builder-setting-input" {{ !$canUpdate ? 'disabled' : '' }}>
                        <option value="integer">Integer</option>
                        <option value="decimal">Decimal</option>
                        <option value="text">Text</option>
                        <option value="date">Date</option>
                        <option value="date_range">Date Range</option>
                        <option value="select">Select</option>
                        <option value="boolean">Yes/No</option>
                        <option value="repeating_group">Repeating Group</option>
                        <option value="user_picker">User Picker</option>
                      </select>
                    </div>

                    <div class="mb-3 field-behavior-option">
                      <label class="d-block">Width</label>

                      <div class="settings-radio-row">
                        <label class="settings-radio-pill" for="settingWidthColumn">
                          <input class="form-check-input" type="radio" name="settingWidth" id="settingWidthColumn" value="4" {{ !$canUpdate ? 'disabled' : '' }}>
                          <span>Column</span>
                        </label>

                        <label class="settings-radio-pill" for="settingWidthFull">
                          <input class="form-check-input" type="radio" name="settingWidth" id="settingWidthFull" value="12" {{ !$canUpdate ? 'disabled' : '' }}>
                          <span>Whole Row</span>
                        </label>
                      </div>
                    </div>

                    <div class="mb-3 repeating-group-option" id="repeatingGroupSettings" style="display: none;">
                      <div class="repeating-columns-header">
                        <label class="mb-0">Columns</label>
                        <button type="button" id="addRepeatingGroupColumn" class="repeating-column-add" {{ !$canUpdate ? 'disabled' : '' }}>
                          <i class="fa fa-plus"></i>
                        </button>
                      </div>

                      <div id="repeatingGroupColumnsList" class="repeating-columns-list"></div>
                    </div>

                    <div class="mb-3 field-behavior-option" id="selectOptionsSettings" style="display: none;">
                      <label for="settingSelectOptions">Select Options</label>
                      <textarea
                        id="settingSelectOptions"
                        class="form-control builder-setting-input"
                        rows="5"
                        placeholder="One option per line"
                        {{ !$canUpdate ? 'disabled' : '' }}
                      ></textarea>
                      <span class="field-help-text">Used only when Data Type is Select.</span>
                    </div>

                    <div class="mb-3 field-behavior-option">
                      <label class="d-block">Required</label>

                      <div class="settings-radio-row">
                        <label class="settings-radio-pill" for="settingRequiredYes">
                          <input class="form-check-input" type="radio" name="settingRequired" id="settingRequiredYes" value="1" {{ !$canUpdate ? 'disabled' : '' }}>
                          <span>Yes</span>
                        </label>

                        <label class="settings-radio-pill" for="settingRequiredNo">
                          <input class="form-check-input" type="radio" name="settingRequired" id="settingRequiredNo" value="0" {{ !$canUpdate ? 'disabled' : '' }}>
                          <span>No</span>
                        </label>
                      </div>
                    </div>

                    <div class="mb-3 field-behavior-option">
                      <label class="d-block">Add Remarks</label>

                      <div class="settings-radio-row">
                        <label class="settings-radio-pill" for="settingRemarksYes">
                          <input class="form-check-input" type="radio" name="settingRemarks" id="settingRemarksYes" value="1" {{ !$canUpdate ? 'disabled' : '' }}>
                          <span>Yes</span>
                        </label>

                        <label class="settings-radio-pill" for="settingRemarksNo">
                          <input class="form-check-input" type="radio" name="settingRemarks" id="settingRemarksNo" value="0" {{ !$canUpdate ? 'disabled' : '' }}>
                          <span>No</span>
                        </label>
                      </div>
                    </div>

                    <div class="mb-0">
                      <label class="d-block">Status</label>

                      <div class="settings-radio-row">
                        <label class="settings-radio-pill" for="settingStatusActive">
                          <input class="form-check-input" type="radio" name="settingStatus" id="settingStatusActive" value="1" {{ !$canUpdate ? 'disabled' : '' }}>
                          <span>Active</span>
                        </label>

                        <label class="settings-radio-pill" for="settingStatusInactive">
                          <input class="form-check-input" type="radio" name="settingStatus" id="settingStatusInactive" value="0" {{ !$canUpdate ? 'disabled' : '' }}>
                          <span>Inactive</span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="settings-sticky-actions">
                <div class="settings-action-note">
                  <i class="fa fa-info-circle"></i>
                  <span id="settingsActionText">Select an item to edit settings.</span>
                </div>

                <div class="settings-action-buttons">
                  @can('update', $measure->pillar)
                    <button type="button" id="removeFieldBtn" class="settings-danger-btn" disabled>
                      <i class="fa fa-trash"></i>
                      Remove
                    </button>

                    <button type="submit" form="form-details-form" id="saveFieldSettingsBtn" class="settings-save-btn" disabled>
                      <i class="fa fa-save"></i>
                      Save
                    </button>
                  @endcan
                </div>
              </div>
            </div>
          </section>

          <section class="builder-card builder-panel field-types-panel">
            <div class="builder-card-header">
              <div>
                <p class="builder-card-title">Field Types</p>
                <p class="builder-card-subtitle">Add sections, then place multiple fields inside each section.</p>
              </div>
            </div>

            <div class="builder-panel-body" id="fieldTypesPanel">
              <div class="field-type-item field-type-section" draggable="true" data-type="section">
                <div class="field-type-icon">
                  <i class="fa fa-folder-open"></i>
                </div>
                <div>
                  <div class="field-type-name">Section</div>
                  <div class="field-type-desc">Container that holds multiple fields.</div>
                </div>
              </div>

              @foreach([
                'integer' => ['Integer', 'fa-hashtag', 'Whole number values.'],
                'decimal' => ['Decimal', 'fa-calculator', 'Numbers with decimal places.'],
                'text' => ['Text', 'fa-align-left', 'Long answer or narrative field.'],
                'date' => ['Date', 'fa-calendar', 'Single date field.'],
                'date_range' => ['Date Range', 'fa-calendar-o', 'Start and end date field.'],
                'select' => ['Select', 'fa-list', 'Choice list field.'],
                'boolean' => ['Yes/No', 'fa-toggle-on', 'Boolean yes or no field.'],
                'repeating_group' => ['Repeating Group', 'fa-list-alt', 'Table with configurable columns and rows.'],
                'user_picker' => ['User Picker', 'fa-user', 'Agency user with auto designation metadata.'],
              ] as $type => [$label, $icon, $desc])
                <div class="field-type-item" draggable="true" data-type="{{ $type }}">
                  <div class="field-type-icon">
                    <i class="fa {{ $icon }}"></i>
                  </div>
                  <div>
                    <div class="field-type-name">{{ $label }}</div>
                    <div class="field-type-desc">{{ $desc }}</div>
                  </div>
                </div>
              @endforeach
            </div>
          </section>
        </aside>

        <main class="builder-card builder-canvas">
          <div class="builder-card-header">
            <div>
              <p class="builder-card-title">Field Canvas</p>
              <p class="builder-card-subtitle">Each section can hold multiple UPLIFT field types.</p>
            </div>

            <div class="canvas-toolbar">
              <span class="field-count-pill">
                <i class="fa fa-check-circle"></i>
                <span id="activeCount">0</span> active
              </span>

              <span class="field-count-pill">
                <i class="fa fa-list"></i>
                <span id="totalCount">0</span> total
              </span>
            </div>
          </div>

          <div class="builder-canvas-body">
            @error('fields')
              <p class="text-danger text-xs">{{ $message }}</p>
            @enderror

            <div id="canvasDropZone" class="canvas-drop-zone">
              <div id="canvasFields"></div>

              <div id="canvasEmpty" class="canvas-empty">
                <div class="canvas-empty-icon">
                  <i class="fa fa-folder-open"></i>
                </div>
                <h6>No section yet</h6>
                <p>
                  Add a section first. Then drag or click field types to add multiple fields inside that section.
                </p>
              </div>
            </div>
          </div>
        </main>
      </div>
    </form>

    <section class="builder-details-card mt-3">
      <div class="builder-details-header">
        <div class="builder-details-heading">
          <div class="builder-details-icon">
            <i class="fa fa-sitemap"></i>
          </div>

          <div>
            <h6 class="builder-details-title">Supporting Agencies</h6>
            <p class="builder-details-subtitle">Multiple agencies can support this UPLIFT measure.</p>
          </div>
        </div>
      </div>

      <div class="builder-details-body">
        @can('update', $measure->pillar)
          <form method="post" action="{{ route('uplift-builder.supporting-agencies.store', $measure) }}" class="row g-3 align-items-end mb-3">
            @csrf
            <div class="col-lg-10">
              <label for="supporting_agency_id" class="builder-field-label">Agency</label>
              <select name="agency_id" id="supporting_agency_id" autocomplete="off">
                <option value="">Select supporting agency</option>
                @foreach($agencies as $agency)
                  <option value="{{ $agency->id }}">{{ $agency->selection_name }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-lg-2">
              <button class="builder-primary-btn w-100 justify-content-center" type="submit">
                <i class="fa fa-plus"></i>
                Add
              </button>
            </div>
          </form>
        @endcan

        <div class="row g-2">
          @forelse($measure->supportingAgencies as $agency)
            <div class="col-lg-4 col-md-6">
              <div class="d-flex align-items-center border rounded p-2 h-100">
                <span class="text-sm font-weight-bold text-dark">{{ $agency->selection_name }}</span>

                @can('update', $measure->pillar)
                  <form method="post" action="{{ route('uplift-builder.supporting-agencies.destroy', [$measure, $agency]) }}" class="ms-auto">
                    @csrf
                    @method('delete')
                    <button class="border-0 bg-transparent" type="submit" onclick="return confirm('Remove this supporting agency?')">
                      <i class="fa fa-times text-danger"></i>
                    </button>
                  </form>
                @endcan
              </div>
            </div>
          @empty
            <div class="col-12">
              <p class="text-sm text-secondary mb-0">No supporting agencies yet.</p>
            </div>
          @endforelse
        </div>
      </div>
    </section>

    <div class="form-floating-actions">
      @can('update', $measure->pillar)
        <button class="form-floating-action" type="submit" form="form-details-form">
          <i class="fa fa-save"></i>
          <span>Save</span>
        </button>
      @endcan

      <a class="form-floating-action is-secondary" href="{{ route('uplift-builder.preview', $measure) }}">
        <i class="fa fa-eye"></i>
        <span>Preview</span>
      </a>
    </div>

    @foreach($fields as $field)
      <form method="post" action="{{ route('uplift-builder.fields.destroy', [$measure, $field]) }}" id="remove-field-{{ $field->id }}" hidden>
        @csrf
        @method('delete')
      </form>
    @endforeach

    @include('layouts.footers.auth.footer')
  </div>

  @include('forms.partials.edit.script')

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (document.getElementById('uplift_pillar_id') && typeof initTomSelect === 'function') {
        initTomSelect('uplift_pillar_id', true);
      }

      if (document.getElementById('lead_agency_id') && typeof initTomSelect === 'function') {
        initTomSelect('lead_agency_id', true);

        if (window.tomSelects && window.tomSelects.lead_agency_id) {
          const leadAgencyTomSelect = window.tomSelects.lead_agency_id;

          const syncLeadAgencyDropdownWidth = function () {
            const wrapperWidth = leadAgencyTomSelect.wrapper.getBoundingClientRect().width;

            if (!wrapperWidth) {
              return;
            }

            leadAgencyTomSelect.dropdown.style.width = `${wrapperWidth}px`;
            leadAgencyTomSelect.dropdown.style.minWidth = `${wrapperWidth}px`;
            leadAgencyTomSelect.dropdown.style.maxWidth = `${wrapperWidth}px`;
          };

          leadAgencyTomSelect.wrapper.classList.add('agency-tomselect');
          leadAgencyTomSelect.dropdown.classList.add('agency-tomselect-dropdown');

          leadAgencyTomSelect.on('dropdown_open', function () {
            syncLeadAgencyDropdownWidth();

            const searchInput = leadAgencyTomSelect.dropdown.querySelector('.dropdown-input');

            if (searchInput) {
              searchInput.placeholder = 'Type agency name or abbreviation...';
              searchInput.focus();
            }
          });

          leadAgencyTomSelect.on('type', syncLeadAgencyDropdownWidth);
          window.addEventListener('resize', syncLeadAgencyDropdownWidth);
        }
      }

      if (document.getElementById('supporting_agency_id') && typeof initTomSelect === 'function') {
        initTomSelect('supporting_agency_id', true);
      }
    });
  </script>
@endsection
