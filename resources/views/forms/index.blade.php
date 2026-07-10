@php
  $class_theme = session('user_settings.class_theme', '');
@endphp

@extends('layouts.app')

@section('content')
  <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'Form Management'])
      @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
  </nav>

  <div class="container-fluid py-3">

    <div class="row">
      <div class="col-12">
        <div class="card form-management-card">

          {{-- Header --}}
          <div class="card-header pb-2">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
              <div>
                <h5 class="mb-1">Form Management</h5>
                <p class="text-sm text-secondary mb-0">
                  Create and manage weekly report forms per agency.
                </p>
              </div>

              <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border form-count-badge">
                  {{ $formsCount }} {{ Str::plural('form', $formsCount) }}
                </span>

                @can('create', App\Models\Form::class)
                  <button
                    class="btn btn-primary btn-sm mb-0"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#createFormPanel"
                    aria-expanded="false"
                    aria-controls="createFormPanel">
                    <i class="fa fa-plus me-1"></i>
                    New Form
                  </button>
                @endcan
              </div>
            </div>
          </div>

          <div class="card-body pt-2">

            {{-- Compact Create Form --}}
            @can('create', App\Models\Form::class)
              <div class="collapse mb-3" id="createFormPanel">
                <div class="quick-create-box">

                  <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                      <h6 class="mb-1">
                        <i class="fa fa-file-text-o text-primary me-1"></i>
                        Create New Form
                      </h6>
                      <p class="text-xs text-secondary mb-0">
                        Add one form per agency, then manage its questions and fields.
                      </p>
                    </div>

                    <button
                      type="button"
                      class="btn-close"
                      data-bs-toggle="collapse"
                      data-bs-target="#createFormPanel"
                      aria-label="Close">
                    </button>
                  </div>

                  <form method="post" action="{{ route('forms.store') }}">
                    @csrf
                    <input type="hidden" name="_form_action" value="create">
                    <div class="row g-3 align-items-end">
                      <div class="col-12">
                        @php
                          $selectedCreateAgencyIds = old('agency_ids', old('agency_id') ? [old('agency_id')] : (auth()->user()->isSuperAdmin() ? [] : [auth()->user()->agency_id]));
                          $selectedCreateAgencyIds = array_map('strval', (array) $selectedCreateAgencyIds);
                        @endphp

                        <div class="d-flex justify-content-between align-items-center mb-1">
                          <label class="form-label text-sm mb-0">
                            Agency <span class="text-danger">*</span>
                          </label>

                          @if(auth()->user()->isSuperAdmin())
                            <button class="btn btn-link btn-sm text-primary p-0 mb-0" type="button" id="selectAllAgencies">
                              Select all
                            </button>
                          @endif
                        </div>

                        <select name="agency_ids[]" id="agency_ids" autocomplete="off" multiple>
                          @foreach($agencies as $agency)
                            <option value="{{ $agency->id }}" {{ in_array((string) $agency->id, $selectedCreateAgencyIds, true) ? 'selected' : '' }}>
                              {{ $agency->selection_name }}
                            </option>
                          @endforeach
                        </select>

                        @error('agency_ids')
                          <p class="text-danger text-xs mt-1 mb-0">{{ $message }}</p>
                        @enderror

                        @error('agency_ids.*')
                          <p class="text-danger text-xs mt-1 mb-0">{{ $message }}</p>
                        @enderror
                      </div>

                      {{-- Title --}}
                      <div class="col-xl-4 col-lg-4 col-md-12">
                        <label class="form-label text-sm mb-1">
                          Form Title <span class="text-danger">*</span>
                        </label>

                        <input
                          name="title"
                          class="form-control form-control-sm"
                          type="text"
                          value="{{ old('title') }}"
                          placeholder="e.g. Weekly Accomplishment Report">

                        @error('title')
                          <p class="text-danger text-xs mt-1 mb-0">{{ $message }}</p>
                        @enderror
                      </div>

                      <div class="col-xl-4 col-lg-4 col-md-6">
                        <label class="form-label text-sm mb-1">
                          Assigned Sector
                        </label>

                        <select name="assigned_sector_id" id="assigned_sector_id" autocomplete="off">
                          <option value="">Select sector</option>
                          @foreach($sectors as $sector)
                            <option value="{{ $sector->id }}" {{ old('assigned_sector_id') == $sector->id ? 'selected' : '' }}>
                              {{ $sector->name }} ({{ $sector->abbreviation }})
                            </option>
                          @endforeach
                        </select>

                        @error('assigned_sector_id')
                          <p class="text-danger text-xs mt-1 mb-0">{{ $message }}</p>
                        @enderror
                      </div>

                      {{-- Status --}}
                      <div class="col-xl-2 col-lg-2 col-md-6">
                        <label class="form-label text-sm mb-1">
                          Status <span class="text-danger">*</span>
                        </label>

                        <select name="status" id="status" autocomplete="off" class="hide-search">
                          <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option>
                          <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>

                        @error('status')
                          <p class="text-danger text-xs mt-1 mb-0">{{ $message }}</p>
                        @enderror
                      </div>

                      {{-- Save --}}
                      <div class="col-xl-2 col-lg-2 col-md-6">
                        <button class="btn btn-primary btn-sm w-100 mb-0" type="submit">
                          <i class="fa fa-save me-1"></i>
                          Save Form
                        </button>
                      </div>
                    </div>
                  </form>

                </div>
              </div>
            @endcan

            {{-- Helper Text --}}
            <div class="form-helper mb-3">
              <div class="d-flex align-items-start gap-2">
                <i class="fa fa-info-circle mt-1"></i>
                <p class="mb-0">
                  Open a form using <strong>Manage</strong> to add questions, assign data types,
                  arrange rows, and preview the form before agencies submit reports.
                </p>
              </div>
            </div>

            {{-- Forms Table --}}
            <div class="table-responsive">
              <table class="table align-middle form-table mb-0" id="datatable-forms" cellspacing="0" width="100%" style="width:100%">
                <thead>
                  <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder ps-3">#</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Agency</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Form Title</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center">Fields</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center">Status</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-end pe-3">Action</th>
                  </tr>
                </thead>

                <tbody>
                </tbody>
              </table>
            </div>

          </div>
        </div>
      </div>
    </div>

    @include('layouts.footers.auth.footer')
  </div>

  @can('create', App\Models\Form::class)
    <div class="modal fade" id="duplicate-form-modal" tabindex="-1" aria-labelledby="duplicate-form-label" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content duplicate-form-modal">
          <form method="post" id="duplicateForm" action="{{ $duplicateErrorForm ? route('forms.duplicate', $duplicateErrorForm) : '#' }}">
            @csrf
            <input type="hidden" name="_form_action" value="duplicate">
            <input type="hidden" name="_duplicate_form_id" id="duplicate_form_id" value="{{ old('_duplicate_form_id') }}">

            <div class="modal-header">
              <div>
                <h6 class="modal-title" id="duplicate-form-label">Duplicate Structure</h6>
                <p class="text-xs text-secondary mb-0" id="duplicate_form_source_title">
                  {{ $duplicateErrorForm?->title ?? 'Select a form to duplicate' }}
                </p>
              </div>

              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
              @if(auth()->user()->isSuperAdmin())
                <label class="form-label text-sm mb-1">
                  Target Agency <span class="text-danger">*</span>
                </label>

                <select
                  id="duplicate_agency_id"
                  name="agency_id"
                  autocomplete="off"
                  class="mb-3 @error('agency_id') is-invalid @enderror">
                  <option value="">Select agency</option>
                  @foreach($agencies as $agency)
                    <option value="{{ $agency->id }}" {{ (string) old('agency_id', optional($duplicateErrorForm)->agency_id) === (string) $agency->id ? 'selected' : '' }}>
                      {{ $agency->selection_name }}
                    </option>
                  @endforeach
                </select>

                @error('agency_id')
                  <p class="text-danger text-xs mt-1 mb-2">{{ $message }}</p>
                @enderror
              @endif

              <label class="form-label text-sm mb-1">
                New Form Title <span class="text-danger">*</span>
              </label>

              <input
                name="title"
                id="duplicate_title"
                class="form-control form-control-sm @error('title') is-invalid @enderror"
                type="text"
                value="{{ old('_form_action') === 'duplicate' ? old('title') : '' }}"
                placeholder="Enter form title">

              @error('title')
                <p class="text-danger text-xs mt-1 mb-0">{{ $message }}</p>
              @enderror
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary btn-sm mb-0" data-bs-dismiss="modal">
                Cancel
              </button>

              <button type="submit" class="btn btn-primary btn-sm mb-0">
                <i class="fa fa-copy me-1"></i>
                Duplicate
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endcan
@endsection

@push('css')
  <style>
    .form-management-card {
      border: 0;
      border-radius: 14px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
      overflow: hidden;
    }

    .form-count-badge {
      font-size: 11px;
      font-weight: 600;
      padding: 7px 10px;
      border-radius: 999px;
    }

    .quick-create-box {
      background: #f8fafc;
      border: 1px solid #e9ecef;
      border-radius: 12px;
      padding: 16px;
    }

    .form-helper {
      background: #f8fafc;
      border: 1px solid #edf0f3;
      border-radius: 10px;
      padding: 10px 12px;
      color: #6c757d;
      font-size: 12px;
    }

    .form-helper i {
      color: #5e72e4;
    }

    .form-table {
      border-collapse: separate;
      border-spacing: 0;
    }

    .form-table thead th {
      background: #ffffff;
      border-bottom: 1px solid #e9ecef;
      padding-top: 12px;
      padding-bottom: 12px;
      font-size: 10px;
      letter-spacing: 0.04em;
    }

    .form-table tbody td {
      border-bottom: 1px solid #f1f3f5;
      padding-top: 14px;
      padding-bottom: 14px;
      vertical-align: middle;
    }

    .form-table tbody tr {
      transition: background-color 0.15s ease;
    }

    .form-table tbody tr:hover {
      background: #f8fafc;
    }

    .agency-cell {
      display: flex;
      align-items: center;
      gap: 10px;
      min-width: 220px;
    }

    .agency-avatar {
      width: 34px;
      height: 34px;
      border-radius: 10px;
      background: #eef2ff;
      color: #344767;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 13px;
      font-weight: 700;
      flex-shrink: 0;
    }

    .field-count-pill {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 34px;
      height: 26px;
      padding: 0 10px;
      border-radius: 999px;
      background: #f8f9fa;
      border: 1px solid #e9ecef;
      color: #344767;
      font-size: 12px;
      font-weight: 700;
    }

    .status-pill {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 72px;
      padding: 5px 10px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 700;
    }

    .status-active {
      background: #e8f7ee;
      color: #1f8f4d;
    }

    .status-inactive {
      background: #f1f3f5;
      color: #6c757d;
    }

    .action-btn {
      min-width: 92px;
      border-radius: 8px;
      font-weight: 600;
    }

    .empty-state {
      text-align: center;
      padding: 52px 16px;
      color: #6c757d;
    }

    .empty-icon {
      width: 56px;
      height: 56px;
      border-radius: 16px;
      background: #f8fafc;
      border: 1px solid #e9ecef;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 14px;
    }

    .empty-icon i {
      font-size: 24px;
      opacity: 0.7;
    }

    .empty-state h6 {
      color: #344767;
      margin-bottom: 4px;
    }

    .empty-state p {
      font-size: 13px;
      margin-bottom: 16px;
    }

    .duplicate-form-modal {
      border: 0;
      border-radius: 12px;
      overflow: visible;
    }

    #duplicate-form-modal,
    #duplicate-form-modal .modal-dialog,
    #duplicate-form-modal .modal-content,
    #duplicate-form-modal .modal-body {
      overflow: visible !important;
    }

    .duplicate-agency-dropdown {
      z-index: 20000 !important;
      position: absolute !important;
      width: 100% !important;
      border: 1px solid #d8e4f1 !important;
      border-radius: 10px !important;
      box-shadow: 0 18px 36px rgba(15, 23, 42, .18) !important;
    }

    .duplicate-agency-dropdown .ts-dropdown-content {
      max-height: 260px !important;
    }

    @media (max-width: 767.98px) {
      .form-management-card .card-header {
        padding-left: 16px;
        padding-right: 16px;
      }

      .form-management-card .card-body {
        padding-left: 12px;
        padding-right: 12px;
      }

      .quick-create-box {
        padding: 14px;
      }

      .agency-cell {
        min-width: 180px;
      }

      .action-btn {
        min-width: auto;
      }

    }
  </style>
@endpush

@push('js')
  <script>
    var table = null;

    if (document.getElementById('agency_ids')) {
      initTomSelect('agency_ids', true, true);
    }

    const selectAllAgenciesButton = document.getElementById('selectAllAgencies');

    if (selectAllAgenciesButton && window.tomSelects && window.tomSelects.agency_ids) {
      const syncSelectAllAgenciesButton = function () {
        const agencySelect = window.tomSelects.agency_ids;
        const agencyValues = Object.keys(agencySelect.options);
        const allSelected = agencyValues.length > 0 && agencyValues.every(function (value) {
          return agencySelect.items.includes(value);
        });

        selectAllAgenciesButton.textContent = allSelected ? 'Clear all' : 'Select all';
      };

      syncSelectAllAgenciesButton();

      window.tomSelects.agency_ids.on('change', syncSelectAllAgenciesButton);

      selectAllAgenciesButton.addEventListener('click', function () {
        const agencySelect = window.tomSelects.agency_ids;
        const agencyValues = Object.keys(agencySelect.options);
        const allSelected = agencyValues.length > 0 && agencyValues.every(function (value) {
          return agencySelect.items.includes(value);
        });

        if (allSelected) {
          agencySelect.clear();
        } else {
          agencySelect.setValue(agencyValues);
        }
      });
    }

    if (document.getElementById('status')) {
      initTomSelect('status');
    }

    if (document.getElementById('assigned_sector_id')) {
      initTomSelect('assigned_sector_id', true);
    }

    function initDuplicateAgencySelect() {
      const select = document.getElementById('duplicate_agency_id');

      if (!select || (window.tomSelects && window.tomSelects.duplicate_agency_id)) {
        return;
      }

      window.tomSelects.duplicate_agency_id = new TomSelect(select, {
        plugins: ['dropdown_input'],
        persist: false,
        maxOptions: null,
        sortField: {
          field: 'text',
          direction: 'asc'
        },
        onDropdownOpen: function () {
          this.dropdown.classList.add('duplicate-agency-dropdown');
          this.positionDropdown();
        }
      });

      window.tomSelects.duplicate_agency_id.dropdown.classList.add('duplicate-agency-dropdown');
    }

    $('#duplicate-form-modal').on('shown.bs.modal', function () {
      initDuplicateAgencySelect();

      if (window.tomSelects && window.tomSelects.duplicate_agency_id) {
        window.tomSelects.duplicate_agency_id.refreshOptions(false);
        window.tomSelects.duplicate_agency_id.positionDropdown();
      }
    });

    $(document).ready(function() {
      const dtName = 'datatable-forms';

      createColumnSearch(dtName, [0, 5], [3, 4]);

      table = $('#' + dtName).DataTable({
        ajax: getAjaxConfig("{{ route('getforms') }}", "{{ csrf_token() }}"),
        stateSave: true,
        stateLoadParams: function(settings, data) {
          setupStateLoadParams(dtName, data);
        },
        searchDelay: 500,
        serverSide: true,
        processing: true,
        columns: [
          { data: 'DT_RowIndex', name: 'DT_RowIndex' },
          { data: 'agency', name: 'agency' },
          { data: 'form_title', name: 'form_title' },
          { data: 'fields_count', name: 'fields_count' },
          { data: 'status_label', name: 'status_label' },
          { data: 'actions', name: 'actions' },
        ],
        columnDefs: [
          { targets: [0], className: "text-sm2 text-center font-weight-normal text-truncate align-middle mnw-40 mxw-60", orderable: false, searchable: false },
          { targets: [1], className: "text-sm2 font-weight-normal align-middle mnw-220 mxw-320" },
          { targets: [2], className: "text-sm2 font-weight-normal align-middle mnw-260 mxw-420" },
          { targets: [3, 4], className: "text-sm2 text-center font-weight-normal text-truncate align-middle mnw-80 mxw-120" },
          { targets: [5], className: "text-sm2 text-end text-truncate align-middle mnw-190 mxw-240", orderable: false, searchable: false },
        ],
        order: [[1, 'asc'], [2, 'asc']],
        pagingType: "full_numbers",
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        responsive: false,
        language: getLanguageConfig('Form'),
        initComplete: function(settings, json) {
          setupInitComplete(table, dtName, 1);
        }
      });

      setupKeyUpColumnSearch(table, dtName);
    });

    $('#datatable-forms').on('draw.dt', function() {
      refreshToolTip();
    });

    $(document).on('click', '.js-duplicate-form', function() {
      const button = $(this);
      const formId = button.data('form-id');
      const formTitle = button.data('form-title');
      const agencyId = String(button.data('form-agency-id') || '');

      $('#duplicateForm').attr('action', button.data('duplicate-url'));
      $('#duplicate_form_id').val(formId);
      $('#duplicate_form_source_title').text(formTitle);
      $('#duplicate_title').val(formTitle);
      $('#duplicate_agency_id').val(agencyId);

      if (window.tomSelects && window.tomSelects.duplicate_agency_id) {
        window.tomSelects.duplicate_agency_id.setValue(agencyId);
      }
    });

    // Auto-expand create panel if there were validation errors
    @if($errors->any() && old('_form_action') === 'create')
      var panel = document.getElementById('createFormPanel');

      if (panel) {
        var bsCollapse = new bootstrap.Collapse(panel, {
          toggle: false
        });

        bsCollapse.show();
      }
    @endif

    @if($errors->any() && old('_form_action') === 'duplicate' && old('_duplicate_form_id'))
      var duplicateModal = document.getElementById('duplicate-form-modal');

      if (duplicateModal) {
        var bsDuplicateModal = new bootstrap.Modal(duplicateModal);

        bsDuplicateModal.show();
      }
    @endif
  </script>
@endpush
