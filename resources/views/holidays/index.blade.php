@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  <!-- Navbar -->
  <nav class="navbar navbar-main navbar-expand-lg  px-0 mx-4 shadow-none border-radius-xl z-index-sticky " id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'Holidays and Suspensions'])
      @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
  </nav>
  <!-- End Navbar -->

  <div class="container-fluid">
    <div class="row mt-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between">
            <div class="d-flex align-items-center">
              <h5 class="mb-0">Holidays and Suspensions</h5>
            </div>
            <div class="text-end ms-auto">
              <button type="button" data-bs-toggle="tooltip" data-bs-original-title="Add New Holiday/Suspension" class="btn btn-xs btn-dark mb-0" onclick="showHoliday()">
                <i class="fa fa-plus pe-2"></i> Holiday/Suspension
              </button>
            </div>
          </div>

          <div class="card-body p-3">
            <div class="table-responsive">
              <table class="table table-bordered table-hover" id="datatable-holidays" cellspacing="0" width="100%" style="width:100%">
                <thead class="thead-light">
                  <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Name
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Type
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Date
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Repeat
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Updated By
                    </th>
                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Action
                    </th>
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

  <form method="post" id="frmHoliday" autocomplete="off" class="form-horizontal">
    @csrf
    @method('post')
    <div class="modal fade" id="holiday-modal" style="display: none" tabindex="-1" role="dialog" hidden>
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content {{ isset($class_theme) && $class_theme == 'dark' ? 'bg-default' : '' }}">
          <div class="modal-header">
            <h5 class="h5 modal-title" id="h5holidayTitle"></h5>
            <div hidden>
              <input name="holidayTitle" id="holidayTitle" value="{{ old('holidayTitle') }}"/>
              <input name="holidayAction" id="holidayAction" value="{{ old('holidayAction') }}"/>
              <input name="holidayMethod" id="holidayMethod" value="{{ old('holidayMethod') }}"/>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="pt-2 modal-body">
            <div class="multisteps-form__content">
              <div class="row mt-3">
                <div class="col-12 col-sm-6">
                  <label>Name</label>
                  <div class="input-group">
                    <input name="name" id="name" class="form-control" type="text" placeholder="Name" value="{{ old('name') }}">
                  </div>
                  @error('name') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
                <div class="col-12 col-sm-6">
                  <label>Type</label>
                  <select name="type" id="type" placeholder="Type" autocomplete="off" class="hide-search" onchange="ocType()">
                    <option value="">Type</option>
                    <option value="Holiday" {{ old('type') == 'Holiday' ? 'selected' : '' }}>Holiday</option>
                    <option value="Suspension" {{ old('type') == 'Suspension' ? 'selected' : '' }}>Suspension</option>
                  </select>
                  @error('type')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-12 col-sm-6">
                  <label>Whole Day</label>
                  <select name="whole_day" id="whole_day" placeholder="Whole Day" autocomplete="off" class="hide-search" onchange="ocDate()">
                    <option value="">Whole Day</option>
                    <option value="Y" {{ old('whole_day') == 'Y' ? 'selected' : '' }}>Yes</option>
                    <option value="N" {{ old('whole_day') == 'N' ? 'selected' : '' }}>No</option>
                  </select>
                  @error('whole_day')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
                <div class="col-12 col-sm-6">
                  <label>Repeat Every Year</label>
                  <select name="repeat_every_year" id="repeat_every_year" placeholder="Repeat" autocomplete="off" class="hide-search">
                    <option value="">Repeat</option>
                    <option value="Y" {{ old('repeat_every_year') == 'Y' ? 'selected' : '' }}>Yes</option>
                    <option value="N" {{ old('repeat_every_year') == 'N' ? 'selected' : '' }}>No</option>
                  </select>
                  @error('repeat_every_year')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-12 col-sm-6">
                  <label>Start</label>
                  <div class="input-group">
                    <input name="start" id="start" class="form-control" type="datetime-local" step="1" placeholder="Start" value="{{ old('start') }}" onchange="ocDate()">
                  </div>
                  @error('start') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
                <div class="col-12 col-sm-6">
                  <label>End</label>
                  <div class="input-group">
                    <input name="end" id="end" class="form-control" type="datetime-local" step="1" placeholder="End" value="{{ old('end') }}" onchange="ocDate()">
                  </div>
                  @error('end') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
              </div>
            </div>
            <div class="text-center pt-4">
              <button class="m-1 btn btn-primary" type="button" id="btnSave" data-toggle="modal" onclick="ocSubmit()">
                Save
              </button>
              <button class="m-1 btn btn-primary" type="button" id="btnSaveDisabled" disabled hidden>
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

@push('js')
  <script>
    initTomSelect('repeat_every_year');
    initTomSelect('type');
    initTomSelect('whole_day');
    var table = null;
    var newAction = "{{ route('holidays.store') }}";

    $(document).ready(function() {
      dtName = 'datatable-holidays';
      createColumnSearch(dtName, [5], [1, 2, 3]);
      table = $('#' + dtName).DataTable({
        ajax: getAjaxConfig("{{ route('getholidays') }}", "{{ csrf_token() }}"),
        stateSave: true,
        stateLoadParams: function(settings, data) {
          setupStateLoadParams(dtName, data)
        },
        searchDelay: 500,
        serverSide: true,
        processing: true,
        columns:[
          { data: 'name' },
          { data: 'type' },
          { data: 'start' },
          { data: 'repeat_every_year' },
          { data: 'editor' },
          { data: 'actions' },
        ],
        columnDefs: [
          { targets: [0], className: "text-sm2 font-weight-normal text-truncate mnw-260 mxw-280" },
          { targets: [1], className: "text-sm2 text-center font-weight-normal text-truncate mnw-80 mxw-100" },
          { targets: [2], className: "text-sm2 text-center font-weight-normal text-truncate mnw-100 mxw-120" },
          { targets: [3], className: "text-sm2 text-center font-weight-normal text-truncate mnw-80 mxw-100" },
          { targets: [4], className: "text-sm2 font-weight-normal text-truncate mnw-260 mxw-280" },
          { targets: [5], className: "text-sm2 text-center text-truncate mnw-60 mxw-80", orderable: false, searchable: false },
        ],
        order: [[2, 'asc']],
        pagingType: "full_numbers",
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        responsive: false,
        language: getLanguageConfig('Holidays'),
        initComplete : function(settings, json){
          setupInitComplete(table, dtName, 2);
        }
      });
      setupKeyUpColumnSearch(table, dtName);

      @if($errors->any())
        $('#h5holidayTitle').text($('#holidayTitle').val());
        $('input[name="_method"]').val($('#holidayMethod').val());
        $('#frmHoliday').attr('action', $('#holidayAction').val());

        ocType();
        // ocDate();

        $("#holiday-modal").attr("hidden", false);
        $("#holiday-modal").modal("show");
      @endif
    });

    $('#datatable-holidays').on('draw.dt', function() {
      refreshToolTip();
    });

    function ocSubmit() {
      $("#btnSave").attr("hidden", true);
      $("#btnSaveDisabled").attr("hidden", false);
      $("#frmHoliday").submit();
    }

    function showHoliday(myData = [], myAction = '') {
      if (myData.length == 0) {
        $('#holidayTitle').val('New Holiday/Supension');
        $('#holidayMethod').val('post');
        $('#holidayAction').val(newAction);
        $('#name').val('');
        $('#start').val('');
        $('#end').val('');
        tomSelects['type'].setValue('');
        tomSelects['whole_day'].setValue('');
        tomSelects['repeat_every_year'].setValue('');
      } else {
        $('#holidayTitle').val('Edit Holiday/Supension : ' + myData.name);
        $('#holidayMethod').val('put');
        $('#holidayAction').val(myAction);
        $('#name').val(myData.name);
        $('#start').val(myData.start);
        $('#end').val(myData.end);
        tomSelects['type'].setValue(myData.type);
        tomSelects['whole_day'].setValue(myData.whole_day);
        tomSelects['repeat_every_year'].setValue(myData.repeat_every_year);
      }

      ocType();
      
      $('#h5holidayTitle').text($('#holidayTitle').val());
      $('input[name="_method"]').val($('#holidayMethod').val());
      $('#frmHoliday').attr('action', $('#holidayAction').val());
      $("#holiday-modal").attr("hidden", false);
      $("#holiday-modal").modal("show");
    }

    function ocType() {
      if ($('#type').val() == 'Suspension') {
        $("#whole_day").attr("readonly", false);
        tomSelects['repeat_every_year'].setValue('N');
        $("#repeat_every_year").attr("readonly", true);
        $("#end").attr("readonly", false);
      } else if ($('#type').val() == 'Holiday') {
        tomSelects['whole_day'].setValue('Y');
        $("#whole_day").attr("readonly", true);
        $("#repeat_every_year").attr("readonly", false);
        $("#end").attr("readonly", true);
      } else {
        $("#whole_day").attr("readonly", false);
        $("#repeat_every_year").attr("readonly", false);
        $("#end").attr("readonly", false);
      }
      ocDate();
    }

    function ocDate() {
      let startDateTime = $('#start').val();
      let endDateTime = $('#end').val();
      let newStartDateTime = '';
      let newEndDateTime = '';
      let datePart = '';
      let timePart = '';
      if($('#type').val() == 'Holiday' || $('#whole_day').val() == 'Y') {
        if (startDateTime) {
          datePart = startDateTime.split('T')[0];
          newStartDateTime = `${datePart}T00:00:00`;
          newEndDateTime = `${datePart}T23:59:59`;
          $('#start').val(newStartDateTime);
          $('#end').val(newEndDateTime);
        }
        $("#end").attr("readonly", true);
      } else {
        if (endDateTime && startDateTime) {
          datePart = startDateTime.split('T')[0];
          timePart = endDateTime.split('T')[1];
          newEndDateTime = `${datePart}T${timePart}`;
          $('#end').val(newEndDateTime);
        }
        $("#end").attr("readonly", false);
      }
    }
  </script>
@endpush
