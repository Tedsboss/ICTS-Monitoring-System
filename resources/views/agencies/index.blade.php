@extends('layouts.app')

@section('content')
  <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'Agency Management'])
      @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
  </nav>

  <div class="container-fluid">

    {{-- Page summary --}}
    <div class="row mt-4 g-3">
      <div class="col-12 col-md-4">
        <div class="card h-100">
          <div class="card-body p-3 d-flex align-items-center justify-content-between">
            <div>
              <p class="text-sm text-uppercase text-secondary font-weight-bolder mb-1">Agencies</p>
              <h4 class="mb-0" id="agencyTotalCount">—</h4>
              <p class="text-xs text-secondary mb-0 mt-1" id="agencyFilteredLabel">Loading records...</p>
            </div>
            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
              <i class="fa fa-building text-lg opacity-10"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-8">
        <div class="card h-100">
          <div class="card-body p-3">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
              <div>
                <p class="text-sm text-uppercase text-secondary font-weight-bolder mb-1">Quick Actions</p>
                <p class="text-xs text-secondary mb-0">
                  Search, filter, inspect, and maintain agency records.
                </p>
              </div>

              <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary mb-0" id="btnResetAgencyFilters">
                  <i class="fa fa-rotate-left me-1"></i> Reset Filters
                </button>

                <button type="button" class="btn btn-sm btn-outline-primary mb-0" id="btnRefreshAgencies">
                  <i class="fa fa-refresh me-1"></i> Refresh
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Main table --}}
    <div class="row mt-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header pb-0">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
              <div>
                <h5 class="mb-1">Agency Management</h5>
                <p class="text-sm text-secondary mb-0">
                  Manage agency profile, agency head information, contact details, and status.
                </p>
              </div>

              <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border" id="agencyResultBadge">
                  Loading...
                </span>
              </div>
            </div>
          </div>

          <div class="card-body p-3">
            <div class="table-responsive agency-table-wrap">
              <table class="table table-bordered table-hover align-middle mb-0" id="datatable-agencies" cellspacing="0" width="100%" style="width:100%">
                <thead class="thead-light agency-sticky-head">
                  <tr>
                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 p-2">UACS ID</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">Agency</th>
                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 p-2">Abbreviation</th>
                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 p-2">Status</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">Head Name</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">Designation</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">Email</th>
                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 p-2 agency-action-head">Action</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
              <small class="text-secondary" id="agencyTableHint">
                Tip: use the column filters to quickly narrow the agency list.
              </small>
              <small class="text-secondary">
                Changes are saved only after clicking <strong>Save Changes</strong>.
              </small>
            </div>
          </div>
        </div>
      </div>
    </div>

    @include('layouts.footers.auth.footer')
  </div>

  {{-- Edit agency modal --}}
  <form method="post" id="frmAgency" autocomplete="off" class="form-horizontal">
    @csrf
    @method('post')

    <div class="modal fade" id="agency-modal" style="display: none" tabindex="-1" role="dialog" hidden>
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content {{ session('user_settings.class_theme', '') == 'dark' ? 'bg-default' : '' }}">

          <div class="modal-header">
            <div>
              <h5 class="h5 modal-title mb-1" id="h5agencyTitle"></h5>
              <p class="text-xs text-secondary mb-0">Update the selected agency information.</p>
            </div>

            <div hidden>
              <input name="agencyTitle" id="agencyTitle" value="{{ old('agencyTitle') }}" />
              <input name="agencyAction" id="agencyAction" value="{{ old('agencyAction') }}" />
              <input name="agencyMethod" id="agencyMethod" value="{{ old('agencyMethod') }}" />
            </div>

            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body pt-3">

            {{-- Agency information --}}
            <div class="border rounded p-3 mb-3">
              <div class="d-flex align-items-center mb-3">
                <div class="icon icon-shape bg-gradient-primary shadow-sm text-center border-radius-md me-2">
                  <i class="fa fa-building text-white text-sm"></i>
                </div>
                <div>
                  <h6 class="mb-0">Agency Information</h6>
                  <p class="text-xs text-secondary mb-0">Core agency record and status.</p>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-12 col-md-4">
                  <label class="form-label">UACS Agency ID</label>
                  <input id="display_UACS_AGY_ID" class="form-control" type="text" readonly>
                </div>

                <div class="col-12 col-md-4">
                  <label class="form-label">Abbreviation</label>
                  <input id="display_Abbreviation" class="form-control" type="text" readonly>
                </div>

                <div class="col-12 col-md-4">
                  <label class="form-label">Status</label>
                  <select name="active" id="active" placeholder="Status" autocomplete="off" class="hide-search">
                    <option value="">Unknown</option>
                    <option value="1" {{ old('active') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('active') === '0' ? 'selected' : '' }}>Inactive</option>
                  </select>
                  @error('active')
                    <p class="text-danger text-xs mb-0 mt-1">{{ $message }}</p>
                  @enderror
                </div>

                <div class="col-12">
                  <label class="form-label">Agency Name</label>
                  <input
                    name="UACS_AGY_DSC"
                    id="UACS_AGY_DSC"
                    class="form-control"
                    type="text"
                    placeholder="Agency Name"
                    value="{{ old('UACS_AGY_DSC') }}"
                  >
                  @error('UACS_AGY_DSC')
                    <p class="text-danger text-xs mb-0 mt-1">{{ $message }}</p>
                  @enderror
                </div>
              </div>
            </div>

            {{-- Agency head --}}
            <div class="border rounded p-3 mb-3">
              <div class="d-flex align-items-center mb-3">
                <div class="icon icon-shape bg-gradient-info shadow-sm text-center border-radius-md me-2">
                  <i class="fa fa-user-tie text-white text-sm"></i>
                </div>
                <div>
                  <h6 class="mb-0">Agency Head</h6>
                  <p class="text-xs text-secondary mb-0">Name and official designation.</p>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-12 col-md-4">
                  <label class="form-label">First Name</label>
                  <input
                    name="head_fname"
                    id="head_fname"
                    class="form-control"
                    type="text"
                    placeholder="First Name"
                    value="{{ old('head_fname') }}"
                  >
                  @error('head_fname')
                    <p class="text-danger text-xs mb-0 mt-1">{{ $message }}</p>
                  @enderror
                </div>

                <div class="col-12 col-md-4">
                  <label class="form-label">Middle Name</label>
                  <input
                    name="head_mname"
                    id="head_mname"
                    class="form-control"
                    type="text"
                    placeholder="Middle Name"
                    value="{{ old('head_mname') }}"
                  >
                  @error('head_mname')
                    <p class="text-danger text-xs mb-0 mt-1">{{ $message }}</p>
                  @enderror
                </div>

                <div class="col-12 col-md-4">
                  <label class="form-label">Last Name</label>
                  <input
                    name="head_lname"
                    id="head_lname"
                    class="form-control"
                    type="text"
                    placeholder="Last Name"
                    value="{{ old('head_lname') }}"
                  >
                  @error('head_lname')
                    <p class="text-danger text-xs mb-0 mt-1">{{ $message }}</p>
                  @enderror
                </div>

                <div class="col-12">
                  <label class="form-label">Head Designation</label>
                  <input
                    name="head_designation"
                    id="head_designation"
                    class="form-control"
                    type="text"
                    placeholder="Head Designation"
                    value="{{ old('head_designation') }}"
                  >
                  @error('head_designation')
                    <p class="text-danger text-xs mb-0 mt-1">{{ $message }}</p>
                  @enderror
                </div>
              </div>
            </div>

            {{-- Contact information --}}
            <div class="border rounded p-3">
              <div class="d-flex align-items-center mb-3">
                <div class="icon icon-shape bg-gradient-success shadow-sm text-center border-radius-md me-2">
                  <i class="fa fa-address-card text-white text-sm"></i>
                </div>
                <div>
                  <h6 class="mb-0">Contact Information</h6>
                  <p class="text-xs text-secondary mb-0">Official contact details of the agency head.</p>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label class="form-label">Telephone Number</label>
                  <input
                    name="head_telnumber"
                    id="head_telnumber"
                    class="form-control"
                    type="text"
                    placeholder="Telephone Number"
                    value="{{ old('head_telnumber') }}"
                  >
                  @error('head_telnumber')
                    <p class="text-danger text-xs mb-0 mt-1">{{ $message }}</p>
                  @enderror
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label">Email Address</label>
                  <input
                    name="head_email"
                    id="head_email"
                    class="form-control"
                    type="email"
                    placeholder="Email Address"
                    value="{{ old('head_email') }}"
                  >
                  @error('head_email')
                    <p class="text-danger text-xs mb-0 mt-1">{{ $message }}</p>
                  @enderror
                </div>
              </div>
            </div>

          </div>

          <div class="modal-footer d-flex justify-content-between">
            <small class="text-secondary" id="agencyChangeNotice">No unsaved changes.</small>

            <div>
              <button type="button" class="btn btn-outline-secondary mb-0 me-2" data-bs-dismiss="modal">
                Cancel
              </button>

              <button class="btn btn-primary mb-0" type="button" id="btnSave" onclick="ocSubmit()" disabled>
                <i class="fa fa-save me-1"></i> Save Changes
              </button>

              <button class="btn btn-primary mb-0" type="button" id="btnSaveDisabled" disabled hidden>
                <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                Saving...
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>
  </form>
@endsection

@push('css')
  <style>
    .agency-table-wrap {
      max-height: 68vh;
      overflow: auto;
      border-radius: .5rem;
    }

    .agency-sticky-head th {
      position: sticky;
      top: 0;
      z-index: 4;
      background: var(--bs-body-bg, #fff);
    }

    #datatable-agencies tbody tr {
      transition: background-color .15s ease, box-shadow .15s ease;
    }

    #datatable-agencies tbody tr:hover {
      box-shadow: inset 3px 0 0 rgba(94, 114, 228, .55);
    }

    #datatable-agencies td,
    #datatable-agencies th {
      vertical-align: middle;
    }

    #datatable-agencies td:last-child,
    #datatable-agencies th:last-child {
      position: sticky;
      right: 0;
      z-index: 3;
      background: var(--bs-body-bg, #fff);
    }

    #datatable-agencies thead th:last-child {
      z-index: 5;
    }

    #datatable-agencies tbody tr:hover td:last-child {
      background: var(--bs-body-bg, #fff);
    }

    .agency-cell-empty {
      color: #adb5bd;
    }

    #agency-modal .form-label {
      font-size: .78rem;
      font-weight: 600;
      margin-bottom: .35rem;
    }

    #agency-modal input[readonly] {
      background-color: rgba(233, 236, 239, .65);
      cursor: default;
    }

    @media (max-width: 767.98px) {
      .agency-table-wrap {
        max-height: none;
      }
    }
  </style>
@endpush

@push('js')
  <script>
    var table = null;
    var agencyInitialState = '';
    var agencyHasChanges = false;

    initTomSelect('active');

    $(document).ready(function() {
      dtName = 'datatable-agencies';

      createColumnSearch(dtName, [7], [2, 3]);

      table = $('#' + dtName).DataTable({
        ajax: getAjaxConfig("{{ route('getagencies') }}", "{{ csrf_token() }}"),
        stateSave: true,

        stateLoadParams: function(settings, data) {
          setupStateLoadParams(dtName, data);
        },

        searchDelay: 500,
        serverSide: true,
        processing: true,

        columns: [
          {
            data: 'UACS_AGY_ID',
            render: function(data, type) {
              if (type !== 'display') return data;
              return displayOrDash(data);
            }
          },
          {
            data: 'UACS_AGY_DSC',
            render: function(data, type) {
              if (type !== 'display') return data;

              const value = String(data ?? '').trim();
              if (!value) return emptyValue();

              return '<span class="d-inline-block text-truncate" style="max-width:420px;" title="' + escapeAgencyHtml(value) + '">' +
                escapeAgencyHtml(value) +
              '</span>';
            }
          },
          {
            data: 'Abbreviation',
            render: function(data, type) {
              if (type !== 'display') return data;

              const value = String(data ?? '').trim();
              if (!value) return emptyValue();

              return '<span class="badge bg-light text-dark border">' + escapeAgencyHtml(value) + '</span>';
            }
          },
          {
            data: 'active.html',
            name: 'active'
          },
          {
            data: 'head_name',
            name: 'head_lname',
            render: function(data, type) {
              if (type !== 'display') return data;
              return displayOrDash(data);
            }
          },
          {
            data: 'head_designation',
            render: function(data, type) {
              if (type !== 'display') return data;
              return displayOrDash(data);
            }
          },
          {
            data: 'head_email',
            render: function(data, type) {
              if (type !== 'display') return data;

              const value = String(data ?? '').trim();
              if (!value) return emptyValue();

              return '<a href="mailto:' + escapeAgencyHtml(value) + '" class="text-primary">' +
                escapeAgencyHtml(value) +
              '</a>';
            }
          },
          {
            data: 'actions'
          },
        ],

        columnDefs: [
          { targets: [0], className: "text-sm2 text-center font-weight-normal text-truncate mnw-80 mxw-120" },
          { targets: [1], className: "text-sm2 font-weight-normal mnw-320 mxw-420" },
          { targets: [2, 3], className: "text-sm2 text-center font-weight-normal text-truncate mnw-90 mxw-120" },
          { targets: [4, 5, 6], className: "text-sm2 font-weight-normal mnw-180 mxw-240" },
          { targets: [7], className: "text-sm2 text-center text-truncate mnw-60 mxw-80", orderable: false, searchable: false },
        ],

        order: [[1, 'asc']],
        pagingType: "full_numbers",
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 25,
        responsive: false,
        language: getLanguageConfig('Agency'),

        initComplete: function(settings, json) {
          setupInitComplete(table, dtName, 1);
          updateAgencySummary(json);
        }
      });

      setupKeyUpColumnSearch(table, dtName);

      table.on('xhr.dt', function(e, settings, json) {
        updateAgencySummary(json);
      });

      table.on('draw.dt', function() {
        refreshAgencyTooltips();
      });

      $('#btnRefreshAgencies').on('click', function() {
        table.ajax.reload(null, false);
      });

      $('#btnResetAgencyFilters').on('click', function() {
        resetAgencyFilters();
      });

      $('#frmAgency').on('input change', 'input, select', function() {
        detectAgencyChanges();
      });

      $('#agency-modal').on('hide.bs.modal', function(e) {
        if (agencyHasChanges && !$('#btnSaveDisabled').is(':visible')) {
          const allowClose = confirm('You have unsaved changes. Close without saving?');

          if (!allowClose) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return false;
          }
        }
      });

      $('#agency-modal').on('hidden.bs.modal', function() {
        agencyHasChanges = false;
        agencyInitialState = '';
        updateAgencyChangeState();
      });

      @if($errors->any())
        $('#h5agencyTitle').text($('#agencyTitle').val());
        $('input[name="_method"]').val($('#agencyMethod').val());
        $('#frmAgency').attr('action', $('#agencyAction').val());

        $("#agency-modal").attr("hidden", false);
        $("#agency-modal").modal("show");

        setTimeout(function() {
          captureAgencyInitialState();
        }, 100);
      @endif
    });

    $('#datatable-agencies').on('draw.dt', function() {
      refreshAgencyTooltips();
    });

    function refreshAgencyTooltips() {
      $('.tooltip').tooltip('hide');
      $("[data-bs-toggle='tooltip']").tooltip();
    }

    function escapeAgencyHtml(value) {
      return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    function emptyValue() {
      return '<span class="agency-cell-empty">—</span>';
    }

    function displayOrDash(value) {
      const text = String(value ?? '').trim();

      if (!text) {
        return emptyValue();
      }

      return escapeAgencyHtml(text);
    }

    function updateAgencySummary(json) {
      if (!json) return;

      const total = Number(json.recordsTotal ?? 0);
      const filtered = Number(json.recordsFiltered ?? total);

      $('#agencyTotalCount').text(total.toLocaleString());

      if (filtered !== total) {
        $('#agencyFilteredLabel').text(filtered.toLocaleString() + ' matching record(s)');
        $('#agencyResultBadge').text(filtered.toLocaleString() + ' of ' + total.toLocaleString());
      } else {
        $('#agencyFilteredLabel').text('Total agency records');
        $('#agencyResultBadge').text(total.toLocaleString() + ' record(s)');
      }
    }

    function resetAgencyFilters() {
      if (!table) return;

      table.search('');

      table.columns().every(function() {
        this.search('');
      });

      $('#' + dtName + '_wrapper')
        .find('input[type="search"], thead input')
        .val('');

      $('#' + dtName + '_wrapper')
        .find('thead select')
        .each(function() {
          this.value = '';
          if (this.tomselect) {
            this.tomselect.clear(true);
          }
        });

      table.page('first').draw();
    }

    function serializeAgencyForm() {
      const values = {
        UACS_AGY_DSC: $('#UACS_AGY_DSC').val() ?? '',
        active: tomSelects['active'] ? (tomSelects['active'].getValue() ?? '') : ($('#active').val() ?? ''),
        head_fname: $('#head_fname').val() ?? '',
        head_mname: $('#head_mname').val() ?? '',
        head_lname: $('#head_lname').val() ?? '',
        head_designation: $('#head_designation').val() ?? '',
        head_telnumber: $('#head_telnumber').val() ?? '',
        head_email: $('#head_email').val() ?? '',
      };

      return JSON.stringify(values);
    }

    function captureAgencyInitialState() {
      agencyInitialState = serializeAgencyForm();
      agencyHasChanges = false;
      updateAgencyChangeState();
    }

    function detectAgencyChanges() {
      if (!agencyInitialState) return;

      agencyHasChanges = serializeAgencyForm() !== agencyInitialState;
      updateAgencyChangeState();
    }

    function updateAgencyChangeState() {
      $('#btnSave').prop('disabled', !agencyHasChanges);

      if (agencyHasChanges) {
        $('#agencyChangeNotice')
          .removeClass('text-secondary')
          .addClass('text-warning')
          .text('You have unsaved changes.');
      } else {
        $('#agencyChangeNotice')
          .removeClass('text-warning')
          .addClass('text-secondary')
          .text('No unsaved changes.');
      }
    }

    function ocSubmit() {
      if (!agencyHasChanges) {
        return;
      }

      $("#btnSave").attr("hidden", true);
      $("#btnSaveDisabled").attr("hidden", false);

      agencyHasChanges = false;
      $("#frmAgency").submit();
    }

    // Confirm agency activation or deactivation
    function confirmAgencyStatusChange(link, actionLabel) {
      if (!link) {
        return false;
      }

      const label = actionLabel || 'Update Agency Status';
      const confirmed = window.confirm(label + '?');

      if (!confirmed) {
        return false;
      }

      $(link)
        .addClass('disabled')
        .attr('aria-disabled', 'true')
        .css('pointer-events', 'none');

      const $icon = $(link).find('i');

      if ($icon.length) {
        $icon
          .removeClass('fa-unlock fa-lock text-success text-secondary')
          .addClass('fa-spinner fa-spin text-info');
      }

      return true;
    }

    function showAgency(myData, myAction) {
      $('#agencyTitle').val('Edit : ' + (myData.Abbreviation || myData.UACS_AGY_ID || 'Agency'));
      $('#agencyMethod').val('put');
      $('#agencyAction').val(myAction);

      $('#display_UACS_AGY_ID').val(myData.UACS_AGY_ID ?? '');
      $('#display_Abbreviation').val(myData.Abbreviation ?? '');

      $('#UACS_AGY_DSC').val(myData.UACS_AGY_DSC ?? '');
      $('#head_lname').val(myData.head_lname ?? '');
      $('#head_mname').val(myData.head_mname ?? '');
      $('#head_fname').val(myData.head_fname ?? '');
      $('#head_designation').val(myData.head_designation ?? '');
      $('#head_telnumber').val(myData.head_telnumber ?? '');
      $('#head_email').val(myData.head_email ?? '');

      tomSelects['active'].setValue(
        myData.active === null ? '' : String(myData.active),
        true
      );

      $('#h5agencyTitle').text($('#agencyTitle').val());
      $('input[name="_method"]').val($('#agencyMethod').val());
      $('#frmAgency').attr('action', $('#agencyAction').val());

      $("#agency-modal").attr("hidden", false);
      $("#agency-modal").modal("show");

      setTimeout(function() {
        captureAgencyInitialState();
      }, 100);
    }
  </script>
@endpush
