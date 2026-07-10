<div class="modal fade" id="logsModal" style="display: none" tabindex="-1" role="dialog" hidden>
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content {{ isset($class_theme) && $class_theme == 'dark' ? 'bg-default' : '' }}">
      <div class="modal-header">
        <h5 class="h5 modal-title">Logs</h5>&nbsp;&nbsp;
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      @php
        $canshowhistory = false;
        if (auth()->user()->can('showhistory', App\Models\SystemLog::class)) {
          $canshowhistory = true;
        }
      @endphp

      <div class="pt-2 modal-body">
        <div class="multisteps-form__content">
          <div class="row" id="divDataTableLogs">
            <div class="table-responsive">
              <table class="table table-bordered table-hover" id="datatable-logs" cellspacing="0" width="100%" style="width:100%">
                <thead class="thead-light">
                  <tr>
                    <th class="text-uppercase text-start text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Date
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Name
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Activity
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      IP Address
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
          <div class="row" id="divHistories" hidden>
            <div class="col-12 col-lg-12 mt-4">
              <div class="card border">
                <div class="card-header d-flex justify-content-between pb-0">
                  <div class="d-flex align-items-center">
                    <h6 class="mb-0">Change Details</h6>
                  </div>
                </div>
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

<div class="fixed-plugin align-middle" id="divShowLogs" onclick="showLogs()" >
  <a data-bs-toggle="tooltip" style="background-color: #efae53 !important; bottom: {{ isset($btnPosition) ? $btnPosition : '25' }}vh !important;" class="fixed-plugin-button text-bold text-xs text-center font-weight-bolder position-fixed p-2 fixed-plugin-btn opacity-7 align-middle fixed-plugin-btn-custom">
    <div class="row text-center align-middle btn-text-align-center m-0">
      <div class="col-md-3 text-center align-middle btn-text-align-center p-0">
        <i class="fa fa-file-text-o fa-2x"></i>
      </div>
      <div class="col-md-9 fixed-plugin-btn-custom-span text-center align-middle btn-text-align-center p-0 pt-1">
        <span>VIEW LOGS</span>
      </div>
    </div>
  </a>
</div>

@push('js')
  <script>
    var tableLogs = null;
    var canshowhistory = @if($canshowhistory) true @else false @endif;
    function showLogs() {
      $("#divHistories").attr("hidden", true);
      if (tableLogs != null) {
        $('#datatable-logs').DataTable().clear().destroy();
        tableLogs = null;
        let actioncol = ``;
        if (canshowhistory) {
          actioncol = `<th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 p-2">Action</th>`
        }
        $('#divDataTableLogs').empty();
        $('#divDataTableLogs').append(`
          <div class="table-responsive">
            <table class="table table-bordered table-hover" id="datatable-logs" cellspacing="0" width="100%" style="width:100%">
              <thead class="thead-light">
                <tr>
                  <th class="text-uppercase text-start text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                    Date
                  </th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                    Name
                  </th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                    Activity
                  </th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                    IP Address
                  </th>${actioncol}
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
        `);
      }

      dtName = 'datatable-logs';
      createColumnSearch(dtName, [4], []);
      tableLogs = $('#' + dtName).DataTable({
        ajax: getAjaxConfig("{{ $url }}", "{{ csrf_token() }}"),
        stateSave: true,
        stateLoadParams: function(settings, data) {
          setupStateLoadParams(dtName, data)
        },
        searchDelay: 500,
        serverSide: false,
        processing: true,
        columns:[
          { data: 'created_at' },
          { data: 'full_name' },
          { data: 'activity' },
          { data: 'ipaddress' },
          @if($canshowhistory)
            { data: 'actions' },
          @endif
        ],
        columnDefs: [
          { targets: [0], className: "text-sm text-start font-weight-normal text-truncate mnw-80 mxw-100" },
          { targets: [1], className: "text-sm font-weight-normal text-truncate mnw-260 mxw-280" },
          { targets: [2], className: "text-sm font-weight-normal text-truncate mnw-260 mxw-280" },
          { targets: [3], className: "text-sm font-weight-normal text-truncate mnw-80 mxw-100" },
          @if($canshowhistory)
            { targets: [4], className: "text-sm text-center text-truncate mnw-40 mxw-60", orderable: false, searchable: false },
          @endif
        ],
        order: [[0, 'desc']],
        pagingType: "full_numbers",
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        responsive: false,
        language: getLanguageConfig('Logs'),
        initComplete : function(settings, json){
          setupInitComplete(tableLogs, dtName, 0, 'desc');
        }
      });
      setupKeyUpColumnSearch(tableLogs, dtName);

      refreshToolTip();
      $("#logsModal").attr("hidden", false);
      $("#logsModal").modal("show");
    }

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
    }
  </script>
@endpush