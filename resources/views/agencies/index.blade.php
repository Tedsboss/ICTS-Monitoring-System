@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  <nav class="navbar navbar-main navbar-expand-lg  px-0 mx-4 shadow-none border-radius-xl z-index-sticky " id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'Agency Management'])
      @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row mt-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between">
            <div class="d-flex align-items-center">
              <h5 class="mb-0">Agency Management</h5>
            </div>
          </div>

          <div class="card-body p-3">
            <div class="table-responsive">
              <table class="table table-bordered table-hover" id="datatable-agencies" cellspacing="0" width="100%" style="width:100%">
                <thead class="thead-light">
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">UACS ID</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">Agency</th>
                  <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 p-2">Abbreviation</th>
                  <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 p-2">Status</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">Head Name</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">Designation</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">Email</th>
                  <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 p-2">Action</th>
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

  <form method="post" id="frmAgency" autocomplete="off" class="form-horizontal">
    @csrf
    @method('post')
    <div class="modal fade" id="agency-modal" style="display: none" tabindex="-1" role="dialog" hidden>
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content {{ isset($class_theme) && $class_theme == 'dark' ? 'bg-default' : '' }}">
          <div class="modal-header">
            <h5 class="h5 modal-title" id="h5agencyTitle"></h5>
            <div hidden>
              <input name="agencyTitle" id="agencyTitle" value="{{ old('agencyTitle') }}"/>
              <input name="agencyAction" id="agencyAction" value="{{ old('agencyAction') }}"/>
              <input name="agencyMethod" id="agencyMethod" value="{{ old('agencyMethod') }}"/>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="pt-2 modal-body">
            <div class="multisteps-form__content">
              <div class="row mt-3">
                <div class="col-12">
                  <label>Agency Name</label>
                  <input name="UACS_AGY_DSC" id="UACS_AGY_DSC" class="form-control" type="text" placeholder="Agency Name" value="{{ old('UACS_AGY_DSC') }}">
                  @error('UACS_AGY_DSC') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-12 col-md-6">
                  <label>Status</label>
                  <select name="active" id="active" placeholder="Status" autocomplete="off" class="hide-search">
                    <option value="">Unknown</option>
                    <option value="1" {{ old('active') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('active') === '0' ? 'selected' : '' }}>Inactive</option>
                  </select>
                  @error('active') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
                <div class="col-12 col-md-6 mt-3 mt-md-0">
                  <label>Head Designation</label>
                  <input name="head_designation" id="head_designation" class="form-control" type="text" placeholder="Head Designation" value="{{ old('head_designation') }}">
                  @error('head_designation') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-12 col-md-4">
                  <label>Head First Name</label>
                  <input name="head_fname" id="head_fname" class="form-control" type="text" placeholder="First Name" value="{{ old('head_fname') }}">
                  @error('head_fname') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
                <div class="col-12 col-md-4 mt-3 mt-md-0">
                  <label>Head Middle Name</label>
                  <input name="head_mname" id="head_mname" class="form-control" type="text" placeholder="Middle Name" value="{{ old('head_mname') }}">
                  @error('head_mname') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
                <div class="col-12 col-md-4 mt-3 mt-md-0">
                  <label>Head Last Name</label>
                  <input name="head_lname" id="head_lname" class="form-control" type="text" placeholder="Last Name" value="{{ old('head_lname') }}">
                  @error('head_lname') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-12 col-md-6">
                  <label>Head Telephone Number</label>
                  <input name="head_telnumber" id="head_telnumber" class="form-control" type="text" placeholder="Telephone Number" value="{{ old('head_telnumber') }}">
                  @error('head_telnumber') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
                <div class="col-12 col-md-6 mt-3 mt-md-0">
                  <label>Head Email</label>
                  <input name="head_email" id="head_email" class="form-control" type="email" placeholder="Email" value="{{ old('head_email') }}">
                  @error('head_email') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
              </div>
            </div>

            <div class="text-center pt-4">
              <button class="m-1 btn btn-primary" type="button" id="btnSave" onclick="ocSubmit()">
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
    initTomSelect('active');

    $(document).ready(function() {
      dtName = 'datatable-agencies';
      createColumnSearch(dtName, [7], [2, 3]);
      table = $('#' + dtName).DataTable({
        ajax: getAjaxConfig("{{ route('getagencies') }}", "{{ csrf_token() }}"),
        stateSave: true,
        stateLoadParams: function(settings, data) {
          setupStateLoadParams(dtName, data)
        },
        searchDelay: 500,
        serverSide: true,
        processing: true,
        columns:[
          { data: 'UACS_AGY_ID' },
          { data: 'UACS_AGY_DSC' },
          { data: 'Abbreviation' },
          { data: 'active.html', name: 'active' },
          { data: 'head_name', name: 'head_lname' },
          { data: 'head_designation' },
          { data: 'head_email' },
          { data: 'actions' },
        ],
        columnDefs: [
          { targets: [0], className: "text-sm2 text-center font-weight-normal text-truncate mnw-80 mxw-120" },
          { targets: [1], className: "text-sm2 font-weight-normal text-truncate mnw-320 mxw-420" },
          { targets: [2, 3], className: "text-sm2 text-center font-weight-normal text-truncate mnw-90 mxw-120" },
          { targets: [4, 5, 6], className: "text-sm2 font-weight-normal text-truncate mnw-180 mxw-240" },
          { targets: [7], className: "text-sm2 text-center text-truncate mnw-60 mxw-80", orderable: false, searchable: false },
        ],
        order: [[1, 'asc']],
        pagingType: "full_numbers",
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        responsive: false,
        language: getLanguageConfig('Agency'),
        initComplete : function(settings, json){
          setupInitComplete(table, dtName, 1);
        }
      });
      setupKeyUpColumnSearch(table, dtName);

      @if($errors->any())
        $('#h5agencyTitle').text($('#agencyTitle').val());
        $('input[name="_method"]').val($('#agencyMethod').val());
        $('#frmAgency').attr('action', $('#agencyAction').val());
        $("#agency-modal").attr("hidden", false);
        $("#agency-modal").modal("show");
      @endif
    });

    $('#datatable-agencies').on('draw.dt', function() {
      $("[data-bs-toggle='tooltip']").tooltip();
      $('.tooltip').tooltip('hide');
    });

    function ocSubmit() {
      $("#btnSave").attr("hidden", true);
      $("#btnSaveDisabled").attr("hidden", false);
      $("#frmAgency").submit();
    }

    function showAgency(myData, myAction) {
      $('#agencyTitle').val('Edit : ' + (myData.Abbreviation || myData.UACS_AGY_ID || 'Agency'));
      $('#agencyMethod').val('put');
      $('#agencyAction').val(myAction);

      $('#UACS_AGY_DSC').val(myData.UACS_AGY_DSC);
      $('#head_lname').val(myData.head_lname);
      $('#head_mname').val(myData.head_mname);
      $('#head_fname').val(myData.head_fname);
      $('#head_designation').val(myData.head_designation);
      $('#head_telnumber').val(myData.head_telnumber);
      $('#head_email').val(myData.head_email);
      tomSelects['active'].setValue(myData.active === null ? '' : String(myData.active));

      $('#h5agencyTitle').text($('#agencyTitle').val());
      $('input[name="_method"]').val($('#agencyMethod').val());
      $('#frmAgency').attr('action', $('#agencyAction').val());
      $("#agency-modal").attr("hidden", false);
      $("#agency-modal").modal("show");
    }
  </script>
@endpush
