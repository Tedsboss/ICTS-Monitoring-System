@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  <!-- Navbar -->
  <nav class="navbar navbar-main navbar-expand-lg  px-0 mx-4 shadow-none border-radius-xl z-index-sticky " id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'System Logs'])
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
              <h5 class="mb-0">System Logs</h5>
            </div>
          </div>

          @php
            $canshowhistory = false;
            if (auth()->user()->can('showhistory', App\Models\SystemLog::class)) {
              $canshowhistory = true;
            }
          @endphp

          <div class="card-body p-3">
            <div class="table-responsive">
              <table class="table table-bordered table-hover" id="datatable-logs" cellspacing="0" width="100%" style="width:100%">
                <thead class="thead-light">
                  <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Username
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Activity
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      IP Address
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Date
                    </th>
                    @if($canshowhistory)
                      <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                        Action
                      </th>
                    @endif
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


  <div class="modal fade" id="historyModal" style="display: none" tabindex="-1" role="dialog" hidden>
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content {{ isset($class_theme) && $class_theme == 'dark' ? 'bg-default' : '' }}">
        <div class="modal-header">
          <h5 class="h5 modal-title">Change Details</h5>&nbsp;&nbsp;
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="pt-2 modal-body">
          <div class="multisteps-form__content">
            <div class="row" id="divHistories" hidden>
              <div class="col-12 col-lg-12 mt-4">
                <div class="card border">
                  <div class="card-body">
                    <div class="row">
                      <div id="json-container" style="background:#f8f9fa; border-radius:8px; padding:10px; font-family:monospace; white-space:pre; overflow:auto; max-height:400px;">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('js')
  <script>
    var table = null;
    var canshowhistory = @if($canshowhistory) true @else false @endif;
    $(document).ready(function() {
      dtName = 'datatable-logs';
      createColumnSearch(dtName, [4], [2, 3]);
      table = $('#' + dtName).DataTable({
        ajax: getAjaxConfig("{{ route('getsystemlogs') }}", "{{ csrf_token() }}"),
        stateSave: true,
        stateLoadParams: function(settings, data) {
          setupStateLoadParams(dtName, data)
        },
        searchDelay: 500,
        serverSide: true,
        processing: true,
        columns:[
          { data: 'name' },
          { data: 'activity' },
          { data: 'ipaddress' },
          { data: 'created_at' },
          @if($canshowhistory)
            { data: 'actions' },
          @endif
        ],
        columnDefs: [
          { targets: [0], className: "text-sm2 font-weight-normal text-truncate mnw-80 mxw-100" },
          { targets: [1], className: "text-sm2 font-weight-normal text-truncate mnw-260 mxw-280" },
          { targets: [2], className: "text-sm2 text-center font-weight-normal text-truncate mnw-60 mxw-80" },
          { targets: [3], className: "text-sm2 text-center font-weight-normal text-truncate mnw-80 mxw-100" },
          @if($canshowhistory)
            { targets: [4], className: "text-sm2 text-center font-weight-normal text-truncate mnw-40 mxw-60", orderable: false, searchable: false },
          @endif
        ],
        order: [[3, 'desc']],
        pagingType: "full_numbers",
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        responsive: false,
        language: getLanguageConfig('Logs'),
        initComplete : function(settings, json){
          setupInitComplete(table, dtName, 3, 'desc');
        }
      });
      setupKeyUpColumnSearch(table, dtName);
    });

    $('#datatable-logs').on('draw.dt', function() {
      refreshToolTip();
    });

    
    function showHistory(mySystemLogId) {
      $('#loader').fadeIn('slow');
      setTimeout(function() { 
        $("#json-container").empty();
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });
        $.ajax({
          data: { 
                  "systemlog_id" : mySystemLogId,
                },
          url: "{{ route('gethistory') }}",
          type: "POST",
          dataType: 'json',
          success: function (data) {
            $("#divHistories").attr("hidden", false);
            let jsonObj = JSON.parse(data.data);
            let prettyJson = JSON.stringify(jsonObj, null, 2); // 2 spaces indentation
            $('#json-container').text(prettyJson);
            $('#loader').fadeOut('slow');
          },
          error: function (xhr) {
            $("#divHistories").attr("hidden", true);
            showToast("warning", xhr.responseJSON.error)
            $('#loader').fadeOut('slow');
          }
        });
      }, 500);

      $("#historyModal").attr("hidden", false);
      $("#historyModal").modal("show");
    }
  </script>
@endpush
