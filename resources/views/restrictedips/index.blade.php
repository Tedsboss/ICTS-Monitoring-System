@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  <!-- Navbar -->
  <nav class="navbar navbar-main navbar-expand-lg  px-0 mx-4 shadow-none border-radius-xl z-index-sticky " id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'Restricted IPs'])
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
              <h5 class="mb-0">Restricted IPs</h5>
            </div>
            @can('create', App\Models\RestrictedIp::class)
              <div class="text-end ms-auto">
                <button type="button" data-bs-toggle="tooltip" data-bs-original-title="Add New IP" class="btn btn-xs btn-dark mb-0" onclick="showIP()">
                  <i class="fa fa-plus pe-2"></i>New IP
                </button>
              </div>
            @endcan
          </div>

          <div class="card-body p-3">
            <div class="table-responsive">
              <table class="table table-bordered table-hover" id="datatable-restrictedips" cellspacing="0" width="100%" style="width:100%">
                <thead class="thead-light">
                  <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Date
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      IP Address
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Remarks
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Created By
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Status
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

  <form method="post" id="frmRestrictedIp" autocomplete="off" class="form-horizontal">
    @csrf
    @method('post')
    <div class="modal fade" id="restrictedip-modal" style="display: none" tabindex="-1" role="dialog" hidden>
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content {{ isset($class_theme) && $class_theme == 'dark' ? 'bg-default' : '' }}">
          <div class="modal-header">
            <h5 class="h5 modal-title" id="h5RestrictedIp"></h5>
            <div hidden>
              <input name="restrictedipTitle" id="restrictedipTitle" value="{{ old('restrictedipTitle') }}"/>
              <input name="restrictedipAction" id="restrictedipAction" value="{{ old('restrictedipAction') }}"/>
              <input name="restrictedipMethod" id="restrictedipMethod" value="{{ old('restrictedipMethod') }}"/>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="pt-2 modal-body">
            <div class="multisteps-form__content">
              <div class="row mt-3">
                <div class="col-12 col-sm-6">
                  <label>IP Address</label>
                  <div class="input-group">
                    <input name="ipaddress" id="ipaddress" class="form-control" type="text" placeholder="IP Address" value="{{ old('ipaddress') }}">
                  </div>
                  @error('ipaddress') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
                <div class="col-12 col-sm-6">
                  <label>Date</label>
                  <div class="input-group">
                    <input name="updated_at" id="updated_at" class="form-control" type="text" placeholder="Date created" value="{{ old('updated_at') }}" readonly>
                  </div>
                  @error('updated_at') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-12 col-sm-6">
                  <label>Created By</label>
                  <div class="input-group">
                    <input name="updated_by" id="updated_by" class="form-control" type="text" placeholder="Created By" value="{{ old('updated_by') }}" readonly>
                  </div>
                </div>
                <div class="col-12 col-sm-6">
                  <label>Status</label>
                  <div class="input-group">
                    <input name="status" id="status" class="form-control" type="text" placeholder="Status" value="{{ old('status') }}" readonly>
                  </div>
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-12 col-sm-12">
                  <label>Remarks</label>
                  <textarea name="content" id="content" rows="3" class="w-100 form-control" placeholder="Remarks">{{ old('content') }}</textarea>
                  @error('content') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
              </div>
            </div>
            <div class="text-center pt-4" id="divBtn">
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
      dtName = 'datatable-restrictedips';
      createColumnSearch(dtName, [5], [0, 1, 4]);
      table = $('#datatable-restrictedips').DataTable({
        ajax: getAjaxConfig("{{ route('getrestrictedips') }}", "{{ csrf_token() }}"),
        stateSave: true,
        stateLoadParams: function(settings, data) {
          setupStateLoadParams(dtName, data)
        },
        searchDelay: 500,
        serverSide: true,
        processing: true,
        columns:[
          { data: 'updated_at' },
          { data: 'ipaddress' },
          { data: 'content' },
          { data: 'editor' },
          { data: 'status' },
          { data: 'actions' },
        ],
        columnDefs: [
          { targets: [0], className: "text-sm2 text-center font-weight-normal text-truncate mnw-60 mxw-80" },
          { targets: [1], className: "text-sm2 text-center font-weight-normal text-truncate mnw-60 mxw-80" },
          { targets: [2], className: "text-sm2 font-weight-normal text-truncate mnw-260 mxw-280" },
          { targets: [3], className: "text-sm2 font-weight-normal text-truncate mnw-100 mxw-120" },
          { targets: [4], className: "text-sm2 text-center font-weight-normal text-truncate mnw-60 mxw-80" },
          { targets: [5], className: "text-sm2 text-center text-truncate mnw-60 mxw-80", orderable: false, searchable: false },
        ],
        order: [[0, 'desc']],
        pagingType: "full_numbers",
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        responsive: false,
        language: getLanguageConfig('IP'),
        initComplete : function(settings, json){
          setupInitComplete(table, dtName);
        }
      });
      setupKeyUpColumnSearch(table, dtName);

      @if($errors->any())
        $('#h5RestrictedIp').text($('#restrictedipTitle').val());
        $('input[name="_method"]').val($('#restrictedipMethod').val());
        $('#frmRestrictedIp').attr('action', $('#restrictedipAction').val());

        $('#content').attr('readonly', false);
        $('#ipaddress').attr('readonly', false);
        $('#divBtn').attr('hidden', false);

        $("#restrictedip-modal").attr("hidden", false);
        $("#restrictedip-modal").modal("show");
      @endif
    });

    $('#datatable-restrictedips').on('draw.dt', function() {
      refreshToolTip();
    });

    function ocSubmit() {
      $("#btnSave").attr("hidden", true);
      $("#btnSaveDisabled").attr("hidden", false);
      $("#frmAct").submit();
    }

    function showIP(myData = [], myAction = '') {
      if (myData.length == 0) {
        $('#restrictedipTitle').val('New IP');
        $('#restrictedipMethod').val('post');
        $('#restrictedipAction').val("{{ route('restrictedips.store') }}");
        
        $('#content').attr('readonly', false);
        $('#ipaddress').attr('readonly', false);
        $('#divBtn').attr('hidden', false);

        $('#ipaddress').val('');
        $('#updated_at').val(generateCurrentDateTime());
        $('#updated_by').val('{{ auth()->user()->full_name }}');
        $('#status').val('Blocked');
        $('#content').val('');
      } else {
        $('#restrictedipTitle').val('View');
        $('#restrictedipMethod').val(null);
        $('#restrictedipAction').val(null);

        $('#content').attr('readonly', true);
        $('#ipaddress').attr('readonly', true);
        $('#divBtn').attr('hidden', true);

        $('#ipaddress').val(myData.ipaddress);
        $('#updated_at').val(generateCurrentDateTime(myData.upated_at));
        $('#updated_by').val(myData.editor == '' || myData.editor == null ? '' : (myData.editor.firstname + ' ' + myData.editor.lastname));
        $('#status').val(myData.status == 1 ? 'Blocked' : 'Unblocked');
        $('#content').val(myData.content);
      }
      $('#h5RestrictedIp').text($('#restrictedipTitle').val());
      $('input[name="_method"]').val($('#restrictedipMethod').val());
      $('#frmRestrictedIp').attr('action', $('#restrictedipAction').val());
      $("#restrictedip-modal").attr("hidden", false);
      $("#restrictedip-modal").modal("show");
    }

    function ocReenacted() {
      if ($('#reenacted_flag_yes').is(":checked")) {
        $('#number').prop('disabled', true);
        $('#number').val('');
      } else {
        $('#number').prop('disabled', false);
      }
    }

    function ocSubmit() {
      $("#btnSave").attr("hidden", true);
      $("#btnSaveDisabled").attr("hidden", false);
      $("#frmRestrictedIp").submit();
    }
  </script>
@endpush
