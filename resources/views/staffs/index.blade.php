@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  <!-- Navbar -->
  <nav class="navbar navbar-main navbar-expand-lg  px-0 mx-4 shadow-none border-radius-xl z-index-sticky " id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'Staff Management'])
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
              <h5 class="mb-0">Staff Management</h5>
            </div>
          </div>

          <div class="card-body p-3">
            <div class="table-responsive">
              <table class="table table-bordered table-hover" id="datatable-staffs" cellspacing="0" width="100%" style="width:100%">
                <thead class="thead-light">
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Name
                    </th>
                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Abbreviation
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

  <form method="post" id="frmStaff" autocomplete="off" class="form-horizontal">
    @csrf
    @method('post')
    <div class="modal fade" id="staff-modal" style="display: none" tabindex="-1" role="dialog" hidden>
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content {{ isset($class_theme) && $class_theme == 'dark' ? 'bg-default' : '' }}">
          <div class="modal-header">
            <h5 class="h5 modal-title" id="h5staffTitle"></h5>
            <div hidden>
              <input name="staffTitle" id="staffTitle" value="{{ old('staffTitle') }}"/>
              <input name="staffAction" id="staffAction" value="{{ old('staffAction') }}"/>
              <input name="staffMethod" id="staffMethod" value="{{ old('staffMethod') }}"/>
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

    $(document).ready(function() {
      dtName = 'datatable-staffs';
      createColumnSearch(dtName, [5], [1, 3]);
      table = $('#' + dtName).DataTable({
        ajax: getAjaxConfig("{{ route('getstaffs') }}", "{{ csrf_token() }}"),
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
          { data: 'head_name' },
          { data: 'head_position' },
          { data: 'head_email' },
          { data: 'actions' },
        ],
        columnDefs: [
          { targets: [0], className: "text-sm2 font-weight-normal text-truncate mnw-260 mxw-280" },
          { targets: [1], className: "text-sm2 text-center font-weight-normal text-truncate mnw-60 mxw-80" },
          { targets: [2], className: "text-sm2 font-weight-normal text-truncate mnw-260 mxw-280" },
          { targets: [3], className: "text-sm2 text-center font-weight-normal text-truncate mnw-160 mxw-180" },
          { targets: [4], className: "text-sm2 text-center font-weight-normal text-truncate mnw-160 mxw-180" },
          { targets: [5], className: "text-sm2 text-center text-truncate mnw-60 mxw-80", orderable: false, searchable: false },
        ],
        order: [[0, 'asc']],
        pagingType: "full_numbers",
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        responsive: false,
        language: getLanguageConfig('Staff'),
        initComplete : function(settings, json){
          setupInitComplete(table, dtName);
        }
      });
      setupKeyUpColumnSearch(table, dtName);

      @if($errors->any())
        $('#h5staffTitle').text($('#staffTitle').val());
        $('input[name="_method"]').val($('#staffMethod').val());
        $('#frmStaff').attr('action', $('#staffAction').val());
        $("#staff-modal").attr("hidden", false);
        $("#staff-modal").modal("show");
      @endif
    });

    $('#datatable-staffs').on('draw.dt', function() {
      $("[data-bs-toggle='tooltip']").tooltip();
      $('.tooltip').tooltip('hide');
    });

    function ocSubmit() {
      $("#btnSave").attr("hidden", true);
      $("#btnSaveDisabled").attr("hidden", false);
      $("#frmStaff").submit();
    }

    function showStaff(myData, myAction) {
      $('#staffTitle').val('Edit : ' + myData.abbreviation);
      $('#staffMethod').val('put');
      $('#staffAction').val(myAction);

      $('#name').val(myData.name);
      $('#abbreviation').val(myData.abbreviation);
      $('#head_name').val(myData.head_name);
      $('#head_position').val(myData.head_position);
      $('#head_email').val(myData.head_email);

      $('#h5staffTitle').text($('#staffTitle').val());
      $('input[name="_method"]').val($('#staffMethod').val());
      $('#frmStaff').attr('action', $('#staffAction').val());
      $("#staff-modal").attr("hidden", false);
      $("#staff-modal").modal("show");
    }

    // function toggleCanCheck(staffId, value) {
    //     Swal.fire({
    //         title: 'Are you sure?',
    //         text: `You are about to turn ${value == 1 ? 'ON' : 'OFF'} validation access.`,
    //         icon: 'warning',
    //         showCancelButton: true,
    //         confirmButtonText: 'Yes, confirm',
    //         cancelButtonText: 'Cancel',
    //         reverseButtons: true
    //     }).then((result) => {
    //         if (result.isConfirmed) {
    //             $.ajax({
    //                 url: `/administrator/staffs/${staffId}/toggle-can-check`,
    //                 method: 'POST',
    //                 data: {
    //                     _token: '{{ csrf_token() }}',
    //                     can_check: value
    //                 },
    //                 success: function(response) {
    //                     Swal.fire({
    //                         icon: 'success',
    //                         title: 'Updated!',
    //                         text: 'Validation access updated successfully.',
    //                         timer: 1500,
    //                         showConfirmButton: false
    //                     });
    //                     staffTable.ajax.reload(null, false);
    //                 },
    //                 error: function(xhr) {
    //                     Swal.fire({
    //                         icon: 'error',
    //                         title: 'Error',
    //                         text: 'Failed to update validation access.'
    //                     });
    //                 }
    //             });
    //         } else {
    //             staffTable.ajax.reload(null, false);
    //         }
    //     });
    // }
  </script>
@endpush
