@php
  $class_theme = session('user_settings.class_theme', '');
  $class = trim(($class ?? '') . ' form-builder-sidenav-page g-sidenav-hidden');
  $canUpdate = auth()->user()->can('update', $form);
  $canDelete = auth()->user()->can('delete', $form);

  $builderFields = $form->fields
    ->sortBy([
      ['row_number', 'asc'],
      ['order', 'asc'],
    ])
    ->values()
    ->map(function ($field) {
      return [
        'id' => (string) $field->id,
        'is_new' => false,
        'label' => $field->label,
        'subtitle' => $field->subtitle,
        'value_type' => $field->value_type,
        'options' => $field->options ?? [],
        'column_size' => (int) $field->column_size,
        'row_number' => (int) $field->row_number,
        'order' => (int) $field->order,
        'column_size' => (int) ($field->column_size ?? 12),
        'is_required' => (int) $field->is_required,
        'has_remarks' => (int) $field->has_remarks,
        'status' => (int) $field->status,
      ];
    })
    ->toArray();
@endphp

@extends('layouts.app')

@section('content')
  @include('forms.partials.edit.css')

  <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'Edit Form Builder'])
      @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
  </nav>

  <div class="container-fluid builder-page py-3">
    <form id="form-details-form" method="post" action="{{ route('forms.update', $form) }}">
      @csrf
      @method('put')

      <div id="builderGeneratedInputs"></div>

      <div class="builder-details-card mb-3">
        <div class="builder-details-header">
          <div class="builder-details-heading">
            <div class="builder-details-icon">
              <i class="fa fa-file-text-o"></i>
            </div>

            <div>
              <h6 class="builder-details-title">Form Details</h6>
              <p class="builder-details-subtitle">
                Manage form identity, assigned agency, and publishing status.
              </p>
            </div>
          </div>

          <span class="builder-details-status {{ (int) $form->status === 1 ? 'is-active' : 'is-inactive' }}">
            <span></span>
            {{ (int) $form->status === 1 ? 'Active' : 'Inactive' }}
          </span>
        </div>

        <div class="builder-details-body">
          <div class="builder-details-grid">
            <div class="builder-field-group">
              <label for="agency_id" class="builder-field-label">
                Lead Agency <span class="text-danger">*</span>
              </label>

              <select
                name="agency_id"
                id="agency_id"
                autocomplete="off"
                class="agency-select @error('agency_id') is-invalid @enderror"
                placeholder="Search or select lead agency..."
                data-placeholder="Search or select lead agency..."
                {{ !$canUpdate ? 'disabled' : '' }}
              >
                <option value="">Select lead agency</option>

                @foreach($initialAgencies as $agency)
                  <option value="{{ $agency->id }}" {{ (string) old('agency_id', $form->agency_id) === (string) $agency->id ? 'selected' : '' }}>
                    {{ $agency->selection_name }}
                  </option>
                @endforeach
              </select>

              @error('agency_id')
                <p class="builder-error-text">{{ $message }}</p>
              @enderror
            </div>

            <div class="builder-field-group builder-field-title">
              <label for="title" class="builder-field-label">
                Form Title <span class="text-danger">*</span>
              </label>

              <input
                name="title"
                id="title"
                class="form-control builder-compact-input @error('title') is-invalid @enderror"
                type="text"
                value="{{ old('title', $form->title) }}"
                placeholder="Form title"
                {{ !$canUpdate ? 'disabled' : '' }}
              >

              @error('title')
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
                  <option value="{{ $sector->id }}" {{ (string) old('assigned_sector_id', $form->assigned_sector_id) === (string) $sector->id ? 'selected' : '' }}>
                    {{ $sector->name }} ({{ $sector->abbreviation }})
                  </option>
                @endforeach
              </select>

              @error('assigned_sector_id')
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
                    id="form_status_active"
                    value="1"
                    {{ (string) old('status', $form->status) === '1' ? 'checked' : '' }}
                    {{ !$canUpdate ? 'disabled' : '' }}
                  >
                  <span>Active</span>
                </label>

                <label class="builder-status-option">
                  <input
                    type="radio"
                    name="status"
                    id="form_status_inactive"
                    value="0"
                    {{ (string) old('status', $form->status) === '0' ? 'checked' : '' }}
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
            <a href="{{ route('forms.index') }}" class="builder-secondary-btn">
              <i class="fa fa-arrow-left"></i>
              Back
            </a>

            <a href="{{ route('forms.preview', $form) }}" class="builder-secondary-btn">
              <i class="fa fa-eye"></i>
              Preview
            </a>

            @can('update', $form)
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
                        <option value="repeating_group">Repeating Group</option>
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
                  @can('update', $form)
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

              <div class="field-type-item" draggable="true" data-type="integer">
                <div class="field-type-icon">
                  <i class="fa fa-hashtag"></i>
                </div>
                <div>
                  <div class="field-type-name">Integer</div>
                  <div class="field-type-desc">Whole number values.</div>
                </div>
              </div>

              <div class="field-type-item" draggable="true" data-type="decimal">
                <div class="field-type-icon">
                  <i class="fa fa-calculator"></i>
                </div>
                <div>
                  <div class="field-type-name">Decimal</div>
                  <div class="field-type-desc">Numbers with decimal places.</div>
                </div>
              </div>

              <div class="field-type-item" draggable="true" data-type="text">
                <div class="field-type-icon">
                  <i class="fa fa-align-left"></i>
                </div>
                <div>
                  <div class="field-type-name">Text</div>
                  <div class="field-type-desc">Long answer or narrative field.</div>
                </div>
              </div>

              <div class="field-type-item" draggable="true" data-type="date">
                <div class="field-type-icon">
                  <i class="fa fa-calendar"></i>
                </div>
                <div>
                  <div class="field-type-name">Date</div>
                  <div class="field-type-desc">Single date field.</div>
                </div>
              </div>

              <div class="field-type-item" draggable="true" data-type="date_range">
                <div class="field-type-icon">
                  <i class="fa fa-calendar-o"></i>
                </div>
                <div>
                  <div class="field-type-name">Date Range</div>
                  <div class="field-type-desc">Start and end date field.</div>
                </div>
              </div>

              <div class="field-type-item" draggable="true" data-type="repeating_group">
                <div class="field-type-icon">
                  <i class="fa fa-list-alt"></i>
                </div>
                <div>
                  <div class="field-type-name">Repeating Group</div>
                  <div class="field-type-desc">Repeatable rows. Always takes one full row.</div>
                </div>
              </div>
            </div>
          </section>
        </aside>

        <main class="builder-card builder-canvas">
          <div class="builder-card-header">
            <div>
              <p class="builder-card-title">Field Canvas</p>
              <p class="builder-card-subtitle">Each section can hold multiple field types.</p>
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

    <div class="form-floating-actions">
      @can('update', $form)
        <button class="form-floating-action" type="submit" form="form-details-form">
          <i class="fa fa-save"></i>
          <span>Save</span>
        </button>
      @endcan

      <a class="form-floating-action is-secondary" href="{{ route('forms.preview', $form) }}">
        <i class="fa fa-eye"></i>
        <span>Preview</span>
      </a>
    </div>

    @foreach($form->fields as $field)
      <form method="post" action="{{ route('forms.fields.destroy', [$form, $field]) }}" id="remove-field-{{ $field->id }}" hidden>
        @csrf
        @method('delete')
      </form>
    @endforeach

    @include('layouts.footers.auth.footer')
  </div>

  @include('forms.partials.edit.script')
@endsection
