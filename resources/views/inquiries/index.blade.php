@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  <!-- Navbar -->
  <nav class="navbar navbar-main navbar-expand-lg  px-0 mx-4 shadow-none border-radius-xl z-index-sticky " id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'User Inquiry'])
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
              <h5 class="mb-0">User Inquiry</h5>
            </div>
          </div>

          <div class="card-body p-3">
            <div class="table-responsive">
              <table class="table table-bordered table-hover" id="datatable-inquiries" cellspacing="0" width="100%" style="width:100%">
                <thead class="thead-light">
                  <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Name
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Staff/Division
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Email
                    </th>
                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Status
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

  <form method="post" id="frmInquiry" autocomplete="off" class="form-horizontal">
    @csrf
    @method('put')
    <div class="modal fade" id="inquiry-modal" style="display: none" tabindex="-1" role="dialog" hidden>
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content {{ isset($class_theme) && $class_theme == 'dark' ? 'bg-default' : '' }}">
          <div class="modal-header">
            <h5 class="h5 modal-title" id="h5inquiryTitle"></h5>
            <div hidden>
              <input name="inquiryTitle" id="inquiryTitle" value="{{ old('inquiryTitle') }}"/>
              <input name="inquiryRA" id="inquiryRA" value="{{ old('inquiryRA') }}"/>
              <input name="inquiryStatus" id="inquiryStatus" value="{{ old('inquiryStatus') }}"/>
              <input name="inquiryCanBlock" id="inquiryCanBlock" value="{{ old('inquiryCanBlock') }}"/>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="pt-2 modal-body">
            <div class="multisteps-form__content">
              <div class="row mt-3">
                <div class="col-12 col-sm-6">
                  <label>Name</label>
                  <div class="input-group">
                    <input name="firstname" id="firstname" class="form-control" type="text" value="{{ old('firstname') }}" readonly>
                  </div>
                </div>
                <div class="col-12 col-sm-6">
                  <label>Lastname</label>
                  <div class="input-group">
                    <input name="lastname" id="lastname" class="form-control" type="text" value="{{ old('lastname') }}" readonly>
                  </div>
                </div>
              </div>
              <div class="row mt-3">
                <div class="col-12 col-sm-6">
                  <label>Email</label>
                  <div class="input-group">
                    <input name="email" id="email" class="form-control" type="text" value="{{ old('email') }}" readonly>
                  </div>
                </div>
                <div class="col-12 col-sm-6">
                  <label>Staff/Division</label>
                  <div class="input-group">
                    <input name="staff" id="staff" class="form-control" type="text" value="{{ old('staff') }}" readonly>
                  </div>
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-12 col-sm-12 h-100">
                  <label>Message</label>
                  <div id="divQuill2">
                    <div id="quill_message" class="ql-border"></div>
                  </div>
                  <textarea name="html_message" id="html_message" hidden>{{ old('html_message') }}</textarea>
                </div>
              </div>

              <div class="row mt-3" id="divUpdatedby" hidden>
                <div class="col-12 col-sm-12">
                  <label>Updated by</label>
                  <div class="input-group">
                    <input name="editor" id="editor" class="form-control" type="text" value="{{ old('editor') }}" readonly>
                  </div>
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-12 col-sm-12 h-100">
                  <label>Reply/Remarks</label>
                  <div id="divQuill">
                    <div id="quill_reply" class="ql-border"></div>
                  </div>
                  <div class="input-group" id="divReply" hidden>
                    <textarea name="html_reply" id="html_reply" rows="3" class="form-control" placeholder="Value">{{ old('html_reply') }}</textarea>
                  </div>
                  @error('html_reply') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
              </div>
            </div>
            <div class="text-center pt-4" id="divAction" hidden>
              <input hidden name="status" id="status" value=""/>
              <button class="m-1 btn btn-success" type="button" id="btnSend" data-toggle="modal" onclick="ocSubmit(3)">
                Send
              </button>
              <button class="m-1 btn btn-secondary" type="button" id="btnBlock" data-toggle="modal" onclick="ocSubmit(4)">
                Block
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
    var table = null;
    initQuillJs('quill_reply');
    initQuillJs('quill_message', true);

    $(document).ready(function() {
      dtName = 'datatable-inquiries';
      createColumnSearch(dtName, [5], [3, 4]);
      table = $('#' + dtName).DataTable({
        ajax: getAjaxConfig("{{ route('getinquiries') }}", "{{ csrf_token() }}"),
        stateSave: true,
        stateLoadParams: function(settings, data) {
          setupStateLoadParams(dtName, data)
        },
        searchDelay: 500,
        serverSide: true,
        processing: true,
        columns:[
          { data: 'fullname' },
          { data: 'staff' },
          { data: 'email' },
          { 
            data: 'status.text', 
            name: 'status', 
            render: function(data, type, row) {
              return type === 'display' ? row.status.html : data;
            }
          },
          { data: 'created_at' },
          { data: 'actions' },
        ],
        columnDefs: [
          { targets: [0], className: "text-sm2 font-weight-normal text-truncate mnw-140 mxw-160" },
          { targets: [1], className: "text-sm2 font-weight-normal text-truncate mnw-220 mxw-240" },
          { targets: [2], className: "text-sm2 font-weight-normal text-truncate mnw-100 mxw-120" },
          { targets: [3], className: "text-sm2 text-center font-weight-normal text-truncate mnw-100 mxw-120" },
          { targets: [4], className: "text-sm2 text-center font-weight-normal text-truncate mnw-80 mxw-100" },
          { targets: [5], className: "text-sm2 text-center text-truncate mnw-60 mxw-80", orderable: false, searchable: false },
        ],
        order: [[4, 'desc']],
        pagingType: "full_numbers",
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        responsive: false,
        language: getLanguageConfig('Users'),
        initComplete : function(settings, json){
          setupInitComplete(table, dtName, 4, 'desc');
        }
      });
      setupKeyUpColumnSearch(table, dtName);

      @if($errors->any())
        $('#h5inquiryTitle').text($('#inquiryTitle').val());
        $("#inquiry-modal").attr("hidden", false);
        updateUrl();
        $("#inquiry-modal").modal("show");
      @endif
    });

    $('#datatable-inquiries').on('draw.dt', function() {
      refreshToolTip();
    });

    function ocSubmit(myType) {
      if (myType == 3) {
        $('#html_reply').val(quills['quill_reply'].root.innerHTML);
      } else {
        $('#html_reply').val(quills['quill_reply'].getText().trim());
      }
      $('#status').val(myType);
      $("#btnSend").attr("hidden", true);
      $("#btnBlock").attr("hidden", true);
      $("#btnSaveDisabled").attr("hidden", false);
      $("#frmInquiry").submit();
    }

    function updateUrl() {
      $('#h5inquiryTitle').text($('#inquiryTitle').val());
      $('#frmInquiry').attr('action', $('#inquiryRA').val());

      $('#divQuill').empty();
      $('#divQuill').append(`<div id="quill_reply" class="ql-border"></div>`);
      quills['quill_reply'] = null; 

      if ($('#inquiryStatus').val() == 1 || $('#inquiryStatus').val() == 2) {
        $('#divAction').attr('hidden', false);
        $('#divUpdatedby').attr('hidden', true);
        initQuillJs('quill_reply');
        if ($('#inquiryCanBlock').val() == 1) {
          $('#btnBlock').attr('hidden', false);
        } else {
          $('#btnBlock').attr('hidden', true);
        }
      } else {
        $('#divAction').attr('hidden', true);
        $('#btnBlock').attr('hidden', true);
        $('#divUpdatedby').attr('hidden', false);
        initQuillJs('quill_reply', true);
      }
      quills['quill_reply'].root.innerHTML = $('#html_reply').val();
      quills['quill_message'].root.innerHTML = $('#html_message').val();
    }

    function showInquiry(myData, myStatusDesc, myCanBlock, myElement) {
      $('#inquiryTitle').val('Status : ' + myStatusDesc);
      $('#firstname').val(myData.firstname);
      $('#lastname').val(myData.lastname);
      $('#email').val(myData.email);
      $('#staff').val(myData.staff);
      $('#html_message').val(myData.html_message);
      $('#html_reply').val(myData.html_reply);
      $('#editor').val(myData.editor?.firstname + ' ' + myData.editor?.lastname || '');

      $('#inquiryStatus').val(myData.status);
      $('#inquiryRA').val("/administrator/inquiries/submit/" + myData.id);
      $('#inquiryCanBlock').val(myCanBlock);

      updateUrl();
      updateStatus(myData, myElement);

      $("#inquiry-modal").attr("hidden", false);
      $("#inquiry-modal").modal("show");
    }

    function updateStatus(myData, myElement) {
      if (myData.status == 1) {
        $('#loader').fadeIn('slow');
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });
        $.ajax({
          method: "POST",
          url: "{{ route('inquiries.updatestatus') }}",
          data: { "inquiry_id" :  myData.id },
          dataType: 'json',
          // contentType: false,
          // processData: false,
          success: function(data) {
            $('#loader').fadeOut('slow');
            if (data.data == 'success') {
              $(myElement).find('i').first().removeClass('fa-pencil text-info').addClass('fa-eye text-secondary');
              $(myElement).closest('td').prevAll('td').eq(1).find('span').first().removeClass('bg-info').addClass('bg-secondary').text('Read');
            } else {
              // showToast('warning', 'Unable to update status')
            }
          },
          error: function(xhr) {
            $('#loader').fadeOut('slow');
            // showToast('warning', 'Unable to update status')
          }
        });
      }
    }
  </script>
@endpush
