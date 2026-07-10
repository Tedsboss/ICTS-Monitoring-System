@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  <!-- Navbar -->
  <nav class="navbar navbar-main navbar-expand-lg  px-0 mx-4 shadow-none border-radius-xl z-index-sticky " id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'System Parameters'])
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
              <h5 class="mb-0">System Parameters</h5>
            </div>
          </div>

          <div class="card-body p-3">
            <div class="table-responsive">
              <table class="table table-bordered table-hover" id="datatable-parameters" cellspacing="0" width="100%" style="width:100%">
                <thead class="thead-light">
                  <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Name
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Description
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Category
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Updated By
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Updated Date
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

  <form method="post" id="frmParameter" autocomplete="off" class="form-horizontal">
    @csrf
    @method('post')
    <div class="modal fade" id="parameter-modal" style="display: none" tabindex="-1" role="dialog" hidden>
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content {{ isset($class_theme) && $class_theme == 'dark' ? 'bg-default' : '' }}">
          <div class="modal-header">
            <h5 class="h5 modal-title" id="h5parameterTitle"></h5>
            <div hidden>
              <input name="parameterTitle" id="parameterTitle" value="{{ old('parameterTitle') }}"/>
              <input name="parameterAction" id="parameterAction" value="{{ old('parameterAction') }}"/>
              <input name="parameterMethod" id="parameterMethod" value="{{ old('parameterMethod') }}"/>
              <input name="parameterWithDuration" id="parameterWithDuration" value="{{ old('parameterWithDuration') }}"/>
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
                  <label>Title</label>
                  <div class="input-group">
                    <input name="title" id="title" class="form-control" type="text" placeholder="Title" value="{{ old('title') }}">
                  </div>
                  @error('title') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-12 col-sm-6">
                  <label>Type</label>
                  <select name="type" id="type" placeholder="Type" autocomplete="off" class="hide-search" onchange="loadValue()" readonly>
                    <option value="">Type</option>
                    <option value="html" @if(old('type') == 'html') selected @endif>HTML</option>
                    <option value="string" @if(old('type') == 'string') selected @endif>Text</option>
                    <option value="time" @if(old('type') == 'time') selected @endif>Time</option>
                    <option value="integer" @if(old('type') == 'integer') selected @endif>Integer</option>
                    <option value="boolean" @if(old('type') == 'boolean') selected @endif>Boolean(yes,no)</option>
                  </select>
                  @error('type') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
                <div class="col-12 col-sm-6">
                  <label>Category</label>
                  <select name="category" id="category" placeholder="Category" autocomplete="off" class="hide-search" onchange="loadValue()" readonly>
                    <option value="">Category</option>
                    <option value="Email Notification" @if(old('category') == 'Email Notification') selected @endif>Email Notification</option>
                    <option value="Pop-Up Notification" @if(old('category') == 'Pop-Up Notification') selected @endif>Pop-Up Notification</option>
                    <option value="Prompt (Error,Warning,Info)" @if(old('category') == 'Prompt (Error,Warning,Info)') selected @endif>Prompt (Error,Warning,Info)</option>
                    <option value="Configuration" @if(old('category') == 'Configuration') selected @endif>Configuration</option>
                    <option value="Others" @if(old('category') == 'Others') selected @endif>Others</option>
                  </select>
                  @error('category') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
              </div>


              <div class="row mt-3" id="divDuration">
                <div class="col-12 col-sm-6">
                  <label>Start Date</label>
                  <div class="input-group">
                    <input name="start_date" id="start_date" class="form-control" type="datetime-local" step="1" placeholder="Start" value="{{ old('start_date') }}">
                  </div>
                  @error('start_date') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
                <div class="col-12 col-sm-6">
                  <label>End Date</label>
                  <div class="input-group">
                    <input name="end_date" id="end_date" class="form-control" type="datetime-local" step="1" placeholder="End" value="{{ old('end_date') }}">
                  </div>
                  @error('end_date') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
              </div>


              <div class="row mt-3">
                <div class="col-12 col-sm-12">
                  <label>Description</label>
                  <div class="input-group">
                    <input name="description" id="description" class="form-control" type="text" placeholder="Description" value="{{ old('description') }}">
                  </div>
                  @error('description') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-12 col-sm-12 h-100">
                  <label>Value</label>
                  <div id="divQuill">
                    <div id="quill_value"></div>
                  </div>
                  <div class="input-group" id="divValue" hidden>
                    <textarea name="value" id="value" rows="5" class="form-control" placeholder="Value">{{ old('value') }}</textarea>
                  </div>
                  @error('value') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
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

@push('css')
  <style>
    .ql-editor {
      min-height: 100%; /* Ensure the editor fills the container */
    }
  </style>
@endpush

@push('js')
  <script>
    initTomSelect('type');
    initTomSelect('category');
    initQuillJs('quill_value');
    var table = null;
    var newAction = "{{ route('parameters.store') }}";

    $(document).ready(function() {
      dtName = 'datatable-parameters';
      createColumnSearch(dtName, [5], [2, 4]);
      table = $('#' + dtName).DataTable({
        ajax: getAjaxConfig("{{ route('getparameters') }}", "{{ csrf_token() }}"),
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
          { data: 'category' },
          { data: 'editor' },
          { data: 'updated_at' },
          { data: 'actions' },
        ],
        columnDefs: [
          { targets: [0], className: "text-sm2 font-weight-normal text-truncate mnw-100 mxw-120" },
          { targets: [1], className: "text-sm2 font-weight-normal text-truncate mnw-260 mxw-280" },
          { targets: [2], className: "text-sm2 text-center font-weight-normal text-truncate mnw-100 mxw-120" },
          { targets: [3], className: "text-sm2 font-weight-normal text-truncate mnw-100 mxw-120" },
          { targets: [4], className: "text-sm2 text-center font-weight-normal text-truncate mnw-80 mxw-100" },
          { targets: [5], className: "text-sm2 text-center text-truncate mnw-60 mxw-80", orderable: false, searchable: false },
        ],
        order: [[0, 'desc']],
        pagingType: "full_numbers",
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        responsive: false,
        language: getLanguageConfig('Parameters'),
        initComplete : function(settings, json){
          setupInitComplete(table, dtName);
        }
      });
      setupKeyUpColumnSearch(table, dtName);

      @if($errors->any())
        $('#h5parameterTitle').text($('#parameterTitle').val());
        $('input[name="_method"]').val($('#parameterMethod').val());
        $('#frmParameter').attr('action', $('#parameterAction').val());
        
        loadValue();

        $("#parameter-modal").attr("hidden", false);
        $("#parameter-modal").modal("show");
      @endif
    });

    $('#datatable-parameters').on('draw.dt', function() {
      refreshToolTip();
    });

    function ocSubmit() {
      if ($('#type').val() == 'html') {
        $('#value').val(quills['quill_value'].root.innerHTML);
      }
      $("#btnSave").attr("hidden", true);
      $("#btnSaveDisabled").attr("hidden", false);
      $("#frmParameter").submit();
    }

    function showParameter(myData, myAction) {
      $('#parameterTitle').val('Edit Parameter : ' + myData.name);
      $('#parameterMethod').val('put');
      $('#parameterAction').val(myAction);
      $('#name').val(myData.name);
      $('#parameterWithDuration').val(myData.with_duration);
      $('#start_date').val(myData.start_date);
      $('#end_date').val(myData.end_date);

      // dd($('#parameterWithDuration').val());

      tomSelects['type'].setValue(myData.type);
      tomSelects['category'].setValue(myData.category);

      $('#title').val(myData.title);
      $('#description').val(myData.description);
      $('#value').val(myData.value);

      loadValue();

      $('#h5parameterTitle').text($('#parameterTitle').val());
      $('input[name="_method"]').val($('#parameterMethod').val());
      $('#frmParameter').attr('action', $('#parameterAction').val());
      $("#parameter-modal").attr("hidden", false);
      $("#parameter-modal").modal("show");
    }

    function loadValue() {
      if($('#parameterWithDuration').val() == 'Y') {
        $("#divDuration").attr("hidden", false);
      } else {
        $("#divDuration").attr("hidden", true);
      }
      
      if ($('#type').val() == 'html') {
        $("#divValue").attr("hidden", true);
        $("#divQuill").attr("hidden", false);
        if ($('#value').val() == null || $('#value').val() == '') {
          quills['quill_value'].setContents([]);
        } else {
          quills['quill_value'].root.innerHTML = $('#value').val();
        }
      } else {
        $("#divValue").attr("hidden", false);
        $("#divQuill").attr("hidden", true);
      }
    }
  </script>
@endpush
