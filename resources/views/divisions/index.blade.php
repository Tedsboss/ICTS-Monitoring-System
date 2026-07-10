@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  <!-- Navbar -->
  <nav class="navbar navbar-main navbar-expand-lg  px-0 mx-4 shadow-none border-radius-xl z-index-sticky " id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'Division Management'])
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
              <h5 class="mb-0">Division Management</h5>
            </div>
          </div>

          <div class="card-body p-3">
            <div class="table-responsive">
              <table class="table table-bordered table-hover" id="datatable-divisions" cellspacing="0" width="100%" style="width:100%">
                <thead class="thead-light">
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Name
                    </th>
                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Abbreviation
                    </th>
                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Staff
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Head Name
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Head Position
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Head Email
                    </th>
                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Action
                    </th>
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

  <form method="post" id="frmDivision" autocomplete="off" class="form-horizontal">
    @csrf
    @method('post')
    <div class="modal fade" id="division-modal" style="display: none" tabindex="-1" role="dialog" hidden>
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content {{ isset($class_theme) && $class_theme == 'dark' ? 'bg-default' : '' }}">
          <div class="modal-header">
            <h5 class="h5 modal-title" id="h5divisionTitle"></h5>
            <div hidden>
              <input name="divisionTitle" id="divisionTitle" value="{{ old('divisionTitle') }}"/>
              <input name="divisionAction" id="divisionAction" value="{{ old('divisionAction') }}"/>
              <input name="divisionMethod" id="divisionMethod" value="{{ old('divisionMethod') }}"/>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="pt-2 modal-body">
            <div class="multisteps-form__content">
              <div class="row mt-3">
                <div class="col-12 col-sm-12">
                  <label>Name</label>
                  <div class="input-group">
                    <input name="name" id="name" class="form-control" type="text" placeholder="Name" value="{{ old('name') }}">
                  </div>
                  @error('name') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
              </div>
              <div class="row mt-3">
                <div class="col-12 col-sm-6">
                  <label>Abbreviation</label>
                  <div class="input-group">
                    <input name="abbreviation" id="abbreviation" class="form-control" type="text" placeholder="Abbreviation" value="{{ old('abbreviation') }}">
                  </div>
                  @error('abbreviation') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
                <div class="col-12 col-sm-6">
                  <label>Staff</label>
                  <div class="input-group">
                    <input id="staff" class="form-control" type="text" placeholder="Staff" value="" disabled>
                  </div>
                </div>
              </div>


              <div class="row mt-3">
                <div class="col-12 col-sm-6">
                  <label>Status</label>
                  <select name="can_review" id="can_review" placeholder="Status" autocomplete="off" class="hide-search">
                    <option value="">Gender</option>
                    <option value="Y" {{ old('can_review') == 'Y' ? 'selected' : '' }}>Enabled</option>
                    <option value="N" {{ old('can_review') == 'N' ? 'selected' : '' }}>Disabled</option>
                  </select>
                  @error('can_review') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
                <div class="col-12 col-sm-6">
                  <label>Head Name</label>
                  <div class="input-group">
                    <input name="head_name" id="head_name" class="form-control" type="text" placeholder="Head Name" value="{{ old('head_name') }}">
                  </div>
                  @error('head_name') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
              </div>




              <div class="row mt-3">
                <div class="col-12 col-sm-6">
                  <label>Head Position</label>
                  <div class="input-group">
                    <input name="head_position" id="head_position" class="form-control" type="text" placeholder="Head Position" value="{{ old('head_position') }}">
                  </div>
                  @error('head_position') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
                <div class="col-12 col-sm-6">
                  <label>Head Email</label>
                  <div class="input-group">
                    <input name="head_email" id="head_email" class="form-control" type="text" placeholder="Head Email" value="{{ old('head_email') }}">
                  </div>
                  @error('head_email') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
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
    var table = null;
    initTomSelect('can_review');

    $(document).ready(function() {
      dtName = 'datatable-divisions';
      createColumnSearch(dtName, [6], [1, 2, 4]);
      table = $('#' + dtName).DataTable({
        ajax: getAjaxConfig("{{ route('getdivisions') }}", "{{ csrf_token() }}"),
        stateSave: true,
        stateLoadParams: function(settings, data) {
          setupStateLoadParams(dtName, data)
        },
        searchDelay: 500,
        serverSide: true,
        processing: true,
        columns:[
          { data: 'name' },
          { data: 'abbreviation' },
          { data: 'staff', name: 'staff.abbreviation' },
          { data: 'head_name' },
          { data: 'head_position' },
          { data: 'head_email' },
          { data: 'actions' },
        ],
        columnDefs: [
          { targets: [0], className: "text-sm2 font-weight-normal text-truncate mnw-260 mxw-280" },
          { targets: [1, 2], className: "text-sm2 text-center font-weight-normal text-truncate mnw-60 mxw-80" },
          { targets: [3], className: "text-sm2 font-weight-normal text-truncate mnw-260 mxw-280" },
          { targets: [4], className: "text-sm2 text-center font-weight-normal text-truncate mnw-160 mxw-180" },
          { targets: [5], className: "text-sm2 text-center font-weight-normal text-truncate mnw-160 mxw-180" },
          { targets: [6], className: "text-sm2 text-center text-truncate mnw-60 mxw-80", orderable: false, searchable: false },
        ],
        order: [[2, 'asc']],
        pagingType: "full_numbers",
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        responsive: false,
        language: getLanguageConfig('Division'),
        initComplete : function(settings, json){
          setupInitComplete(table, dtName, 2);
        }
      });
      setupKeyUpColumnSearch(table, dtName);

      @if($errors->any())
        $('#h5divisionTitle').text($('#divisionTitle').val());
        $('input[name="_method"]').val($('#divisionMethod').val());
        $('#frmDivision').attr('action', $('#divisionAction').val());
        $("#division-modal").attr("hidden", false);
        $("#division-modal").modal("show");
      @endif
    });

    $('#datatable-divisions').on('draw.dt', function() {
      $("[data-bs-toggle='tooltip']").tooltip();
      $('.tooltip').tooltip('hide');
    });

    function ocSubmit() {
      $("#btnSave").attr("hidden", true);
      $("#btnSaveDisabled").attr("hidden", false);
      $("#frmDivision").submit();
    }

    function showDivision(myData, myAction) {
      $('#divisionTitle').val('Edit : ' + myData.abbreviation);
      $('#divisionMethod').val('put');
      $('#divisionAction').val(myAction);

      $('#name').val(myData.name);
      $('#abbreviation').val(myData.abbreviation);
      $('#head_name').val(myData.head_name);
      $('#head_position').val(myData.head_position);
      $('#head_email').val(myData.head_email);
      $('#staff').val(myData.staff.abbreviation);
      tomSelects['can_review'].setValue(myData.can_review);

      $('#h5divisionTitle').text($('#divisionTitle').val());
      $('input[name="_method"]').val($('#divisionMethod').val());
      $('#frmDivision').attr('action', $('#divisionAction').val());
      $("#division-modal").attr("hidden", false);
      $("#division-modal").modal("show");
    }
  </script>
@endpush
