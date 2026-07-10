@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  <!-- Navbar -->
  <nav class="navbar navbar-main navbar-expand-lg  px-0 mx-4 shadow-none border-radius-xl z-index-sticky " id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'Roles and Permissions'])
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
              <h5 class="mb-0">Roles and Permissions</h5>
            </div>
            <div class="text-end ms-auto">
              @can('create', App\Models\Role::class)
                <button type="button" data-bs-toggle="tooltip" data-bs-original-title="Add New Role" class="btn btn-xs btn-dark mb-0" onclick="showRole()">
                  <i class="fa fa-plus pe-2"></i> Role
                </button>
              @endcan
            </div>
          </div>

          <div class="card-body p-3">
            <div class="table-responsive">
              <table class="table table-bordered table-hover" id="datatable-roles" cellspacing="0" width="100%" style="width:100%">
                <thead class="thead-light">
                  <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Name
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Description
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Created By
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Created Date
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

  <form method="post" id="frmRole" autocomplete="off" class="form-horizontal">
    @csrf
    @method('post')
    <div class="modal fade" id="role-modal" style="display: none" tabindex="-1" role="dialog" hidden>
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content {{ isset($class_theme) && $class_theme == 'dark' ? 'bg-default' : '' }}">
          <div class="modal-header">
            <h5 class="h5 modal-title" id="h5roleTitle"></h5>
            <div hidden>
              <input name="roleTitle" id="roleTitle" value="{{ old('roleTitle') }}"/>
              <input name="roleAction" id="roleAction" value="{{ old('roleAction') }}"/>
              <input name="roleMethod" id="roleMethod" value="{{ old('roleMethod') }}"/>
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
                <div class="col-12 col-sm-12">
                  <label>Description</label>
                  <textarea name="description" id="description" rows="4" class="w-100 form-control" placeholder="Description">{{ old('description') }}</textarea>
                  @error('description') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
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
    var newAction = "{{ route('roles.store') }}";

    $(document).ready(function() {
      dtName = 'datatable-roles';
      createColumnSearch(dtName, [4], [3]);
      table = $('#' + dtName).DataTable({
        ajax: getAjaxConfig("{{ route('getroles') }}", "{{ csrf_token() }}"),
        stateSave: true,
        stateLoadParams: function(settings, data) {
          setupStateLoadParams(dtName, data)
        },
        searchDelay: 500,
        serverSide: true,
        processing: true,
        columns:[
          { data: 'name' },
          { data: 'description' },
          { data: 'creator' },
          { data: 'created_at' },
          { data: 'actions' },
        ],
        columnDefs: [
          { targets: [0], className: "text-sm2 font-weight-normal text-truncate mnw-180 mxw-200" },
          { targets: [1], className: "text-sm2 font-weight-normal text-truncate mnw-260 mxw-280" },
          { targets: [2], className: "text-sm2 font-weight-normal text-truncate mnw-100 mxw-120" },
          { targets: [3], className: "text-sm2 text-center font-weight-normal text-truncate mnw-80 mxw-100" },
          { targets: [4], className: "text-sm2 text-center text-truncate mnw-60 mxw-80", orderable: false, searchable: false },
        ],
        order: [[0, 'asc']],
        pagingType: "full_numbers",
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        responsive: false,
        language: getLanguageConfig('Expense'),
        initComplete : function(settings, json){
          setupInitComplete(table, dtName);
        }
      });
      setupKeyUpColumnSearch(table, dtName);

      @if($errors->any())
        $('#h5roleTitle').text($('#roleTitle').val());
        $('input[name="_method"]').val($('#roleMethod').val());
        $('#frmRole').attr('action', $('#roleAction').val());
        $("#role-modal").attr("hidden", false);
        $("#role-modal").modal("show");
      @endif
    });

    $('#datatable-roles').on('draw.dt', function() {
      refreshToolTip();
    });

    function ocSubmit() {
      $("#btnSave").attr("hidden", true);
      $("#btnSaveDisabled").attr("hidden", false);
      $("#frmRole").submit();
    }

    function showRole(myData = [], myAction = '') {
      if (myData.length == 0) {
        $('#roleTitle').val('New Role');
        $('#roleMethod').val('post');
        $('#roleAction').val(newAction);
        $('#name').val('');
        $('#description').val('');
      } else {
        $('#roleTitle').val('Edit Role : ' + myData.name);
        $('#roleMethod').val('put');
        $('#roleAction').val(myAction);
        $('#name').val(myData.name);
        $('#description').val(myData.description);
      }
      $('#h5roleTitle').text($('#roleTitle').val());
      $('input[name="_method"]').val($('#roleMethod').val());
      $('#frmRole').attr('action', $('#roleAction').val());
      $("#role-modal").attr("hidden", false);
      $("#role-modal").modal("show");
    }
  </script>
@endpush
