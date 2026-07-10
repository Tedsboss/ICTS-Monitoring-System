@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  <!-- Navbar -->
  <nav class="navbar navbar-main navbar-expand-lg  px-0 mx-4 shadow-none border-radius-xl z-index-sticky " id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'Users'])
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
              <h5 class="mb-0">Users Management</h5>
            </div>
            <div class="text-end ms-auto">
              @can('create', App\Models\User::class)
                <button type="button" data-bs-toggle="tooltip" data-bs-original-title="Add New User" class="btn btn-xs btn-dark mb-0" onclick="showUser()">
                  <i class="fa fa-plus pe-2"></i> User
                </button>
              @endcan
            </div>
          </div>

          <div class="card-body p-3">
            <div class="table-responsive">
              <table class="table table-bordered table-hover" id="datatable-users" cellspacing="0" width="100%" style="width:100%">
                <thead class="thead-light">
                  <tr>
                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Photo
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Name
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Staff
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Agency
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Email
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Designation
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 p-2">
                      Role
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

  <form method="post" id="frmUser" autocomplete="off" enctype="multipart/form-data" class="form-horizontal">
    @csrf
    @method('post')
    <div class="modal fade" id="user-modal" style="display: none" tabindex="-1" user="dialog" hidden>
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content {{ isset($class_theme) && $class_theme == 'dark' ? 'bg-default' : '' }}">
          <div class="modal-header">
            <h5 class="h5 modal-title" id="h5userTitle"></h5>
            <div hidden>
              <input name="userTitle" id="userTitle" value="{{ old('userTitle') }}"/>
              <input name="userAction" id="userAction" value="{{ old('userAction') }}"/>
              <input name="userMethod" id="userMethod" value="{{ old('userMethod') }}"/>
              <input name="userAvatar" id="userAvatar" value="{{ old('userAvatar') }}"/>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="pt-2 modal-body">
            <div class="multisteps-form__content">
              <div class="row">
                <div class="col-4">
                  <input type="file" name="avatar" id="file-input" accept="image/*" class="d-none" onchange="updateavatar()">
                  <div class="avatar position-relative" style="height: auto !important; width: auto !important;">
                    <label for="file-input" class="btn btn-sm btn-icon-only bg-gradient-light position-absolute bottom-0 end-0 mb-n2 me-n2">
                      <i class="fa fa-pencil top-0" data-bs-toggle="tooltip" data-bs-placement="top" title="" aria-hidden="true" data-bs-original-title="Edit Image" aria-label="Edit Image"></i>
                      <span class="sr-only">Edit Image</span>
                    </label>
                    <span class="h-12 w-12 rounded-full overflow-hidden bg-gray-100">
                      <img id="avatar-preview" alt="..." class="w-100 border-radius-lg shadow-sm" src="/assets/img/default-avatar.jpg">
                    </span>
                  </div>
                  @error('avatar')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
                <div class="col-8">
                  <div class="row">
                    <div class="col-12">
                      <label class="form-label">First Name <span class="text-danger">*</span></label>
                      <div class="input-group">
                        <input id="firstname" name="firstname" class="form-control" type="text" value="{{ old('firstname') }}" placeholder="First Name">
                      </div>
                      @error('firstname')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                    </div>
                  </div>
                  <div class="row mt-3">
                    <div class="col-12">
                      <label class="form-label">Middle Name</label>
                      <div class="input-group">
                        <input id="middlename" name="middlename" class="form-control" type="text" value="{{ old('middlename') }}" placeholder="Middle Name">
                      </div>
                      @error('middlename')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                    </div>
                  </div>
                  <div class="row mt-3">
                    <div class="col-12">
                      <label class="form-label">Last Name <span class="text-danger">*</span></label>
                      <div class="input-group">
                        <input id="lastname" name="lastname" class="form-control" type="text" value="{{ old('lastname') }}" placeholder="Last Name">
                      </div>
                      @error('lastname')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                    </div>
                  </div>
                </div>
              </div>
              <div class="row mt-3">

                <div class="col-4">
                  <label class="form-label">Email <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input id="email" name="email" class="form-control" type="email" value="{{ old('email') }}" placeholder="example@email.com">
                  </div>
                  @error('email')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>

                
                    <div class="col-4">
                      <label class="form-label">Birth Date</label>
                      <div class="input-group">
                        <input id="birthday" name="birthday" class="form-control" type="date" value="{{ old('birthday') }}" placeholder="Birth Date">
                      </div>
                      @error('birthday')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                    </div>
                    <div class="col-4">
                      <label class="form-label">Gender</label>
                      <select name="gender" id="gender" placeholder="Gender" autocomplete="off" class="hide-search">
                        <option value="">Gender</option>
                        <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                      </select>
                      @error('gender')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                    </div>


                <div class="col-12">
                  <label class="form-label">Agency <span class="text-danger">*</span></label>
                  <select name="agency_id" id="agency_id" placeholder="Agency" autocomplete="off" onchange="updateNedaFields()">
                    <option value="">Agency</option>
                    @foreach ($agencies as $agency)
                      <option value="{{ $agency->id }}" @if (old('agency_id') == $agency->id) selected @endif>{{ $agency->display_name }}</option>
                    @endforeach
                  </select>
                  @error('agency_id')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
              </div>
              <div class="row mt-3">
                <div class="col-6">
                  <label class="form-label">Role <span class="text-danger">*</span></label>
                  <select name="role_id" id="role_id" placeholder="Role" autocomplete="off" class="hide-search" >
                    <option value="">Role</option>
                    @foreach ($roles as $role)
                      <option value="{{ $role->id }}" @if (old('role_id') == $role->id) selected @endif>{{ $role->name }}</option>
                    @endforeach
                  </select>
                  @error('role_id')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
                <div class="col-6" id="divPositionId" hidden>
                  <label class="form-label">Position <span class="text-danger">*</span></label>
                  <select name="position_id" id="position_id" placeholder="Position" autocomplete="off">
                    <option value="">Position</option>
                    @foreach ($positions as $position)
                      <option value="{{ $position->id }}" @if (old('position_id') == $position->id) selected @endif>{{ $position->name }}</option>
                    @endforeach
                  </select>
                  @error('position_id')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-6" id="divStaffId" hidden>
                  <label class="form-label">Staff/Region <span class="text-danger">*</span></label>
                  <select name="staff_id" id="staff_id" placeholder="Staff" autocomplete="off" onchange="ocStaff()">
                    <option value="">Staff</option>
                    @php $old_office_id = null; @endphp
                    @foreach ($staffs as $staff)
                      @php
                        $staff_office_id = $staff->office_id;
                        $office_label = 'Central Office';
                        if ($staff->office_id != 1) {
                          $staff_office_id = 2;
                          $office_label = 'Regional Office';
                        }
                      @endphp
                      @if ($old_office_id != $staff_office_id)
                        @if ($old_office_id != null)
                          </optgroup>
                        @endif
                        <optgroup label="{{ $office_label }}">
                      @endif
                      <option value="{{ $staff->id }}" @if (old('staff_id') == $staff->id) selected @endif>{{ $staff->name . ' (' . $staff->abbreviation . ')' }}</option>
                      @php $old_office_id = $staff_office_id; @endphp
                    @endforeach
                    </optgroup>
                  </select>
                  @error('staff_id')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
                <div class="col-6" id="divDivisionId" hidden>
                  <label class="form-label">Division <span class="text-danger">*</span></label>
                  <select name="division_id" id="division_id" placeholder="Division" autocomplete="off">
                    <option value="">Division</option>
                    @foreach ($divisions as $division)
                      <option value="{{ $division->id }}" data-name="{{ $division->name }}" data-abbreviation="{{ $division->abbreviation }}" @if (old('division_id') == $division->id) selected @endif>{{ $division->name . ' (' . $division->abbreviation . ')' }}</option>
                    @endforeach
                  </select>
                  @error('division_id')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
              </div>
              <div class="row mt-3">
                <div class="col-6">
                  <label class="form-label">Office Location</label>
                  <div class="input-group">
                    <input id="location" name="location" class="form-control" type="text" value="{{ old('location') }}" placeholder="Location">
                  </div>
                  @error('location')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
                <div class="col-6">
                  <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input id="phone" name="phone" class="form-control" type="text" value="{{ old('phone') }}" placeholder="+63 901 567 8910" required>
                  </div>
                  @error('phone')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
              </div>
              <div class="row mt-3">
                <div class="col-6">
                  <label class="form-label">Password <span id="spanNP" class="text-danger">*</span></label>
                  <div class="input-group">
                    <input id="new-password" name="new-password" value="{{ old('new-password') }}" class="form-control" type="password" placeholder="Password">
                  </div>
                  @error('new-password') <p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
                <div class="col-6">
                  <label class="form-label">Confirm Password <span id="spanCP" class="text-danger">*</span></label>
                  <div class="input-group">
                    <input id="confirm-password" name="confirm-password" class="form-control" type="password" placeholder="Confirm Password">
                  </div>
                  @error('confirm-password') <p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
              </div>

              <div class="row mx-2 my-3" id="divAdditionalSettings">
                <ul class="list-group">
                  <div class="accordion" id="accordionRental">

                    <div class="accordion-item mb-3">
                      <h5 class="accordion-header" id="headingAcSecurity">
                        <button style="font-size: 1rem !important;" data-bs-target="#collapseAcSecurity" aria-controls="collapseAcSecurity" aria-expanded="false" data-bs-toggle="collapse" class="accordion-button border-bottom font-weight-bold" type="button" >
                          Security and Settings
                          <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3"></i>
                          <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3"></i>
                        </button>
                      </h5>
                      <div id="collapseAcSecurity" data-bs-parent="#accordionRental" class="accordion-collapse collapse" aria-labelledby="headingAcSecurity">
                        <div class="accordion-body">
                          @include('users.components.settings', ['user' => null])
                        </div>
                      </div>
                    </div>

                    <div class="accordion-item mb-3" id="divAcSession">
                      <h5 class="accordion-header" id="headingAcSession">
                        <button style="font-size: 1rem !important;" data-bs-target="#collapseAcSession" aria-controls="collapseAcSession" aria-expanded="false" data-bs-toggle="collapse" class="accordion-button border-bottom font-weight-bold" type="button" >
                          Trusted Devices
                          <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3"></i>
                          <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3"></i>
                        </button>
                      </h5>
                      <div id="collapseAcSession" data-bs-parent="#accordionRental" class="accordion-collapse collapse" aria-labelledby="headingAcSession">
                        <div class="accordion-body">
                          @include('users.components.sessions', ['user' => null])
                        </div>
                      </div>
                    </div>

                  </div>
                </ul>
              </div>

            </div>
            <div class="text-center pt-4">
              <button class="m-1 btn btn-primary" type="button" id="btnSave" data-toggle="modal" onclick="ocSubmit()">
                Save
              </button>
              <button class="m-1 btn btn-primary" type="button" id="btnSaveDisabled" disabled hidden>
                <span class="spinner-grow spinner-grow-sm" user="status" aria-hidden="true"></span>
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
  @include('users.components.scripts')
  <script>
    var divisions = @php echo json_encode($divisions) @endphp;
    var depDevAgencyIds = @json($depDevAgencyIds);

    initTomSelect('agency_id', true);
    initTomSelect('gender');
    initTomSelect('role_id');
    initTomSelect('staff_id', false);

    $customRender = {
      option: function(data, escape) {
        return `<div>
                      <span class="title">${escape(data.name)}</span>
                      <span class="title"> (${escape(data.abbreviation)})</span>
                    </div>`;
      },
      item: function(data, escape) {
        return `<div>${escape(data.name)} (${escape(data.abbreviation)})</div>`;
      }
    };
    initTomSelect('division_id', true, false, false, null, null, $customRender);
    initTomSelect('position_id', true);

    var table = null;
    var newAction = "{{ route('users.store') }}";
    var dummyImage = "";

    $(document).ready(function() {
      dtName = 'datatable-users';
      createColumnSearch(dtName, [0, 7], [4, 5]);
      table = $('#' + dtName).DataTable({
        ajax: getAjaxConfig("{{ route('getusers') }}", "{{ csrf_token() }}"),
        stateSave: true,
        stateLoadParams: function(settings, data) {
          setupStateLoadParams(dtName, data)
        },
        searchDelay: 500,
        serverSide: true,
        processing: true,
        columns:[
          { data: 'photo' },
          { data: 'fullname' },
          { data: 'staff', name: 'staff.name' },
          { data: 'agency', name: 'agency.UACS_AGY_DSC' },
          { data: 'email' },
          { data: 'designation', name: 'position.name' },
          { data: 'role', name: 'role.name' },
          { data: 'actions' },
        ],
        columnDefs: [
          { targets: [0], className: "text-sm2 text-center font-weight-normal text-truncate align-middle mnw-40 mxw-60", orderable: false, searchable: false },
          { targets: [1], className: "text-sm2 font-weight-normal text-truncate align-middle mnw-140 mxw-160" },
          { targets: [2], className: "text-sm2 font-weight-normal text-truncate align-middle mnw-140 mxw-160" },
          { targets: [3], className: "text-sm2 font-weight-normal text-truncate align-middle mnw-180 mxw-220" },
          { targets: [4], className: "text-sm2 font-weight-normal text-truncate align-middle mnw-140 mxw-140" },
          { targets: [5], className: "text-sm2 text-center font-weight-normal text-truncate align-middle mnw-100 mxw-120" },
          { targets: [6], className: "text-sm2 text-center font-weight-normal text-truncate align-middle mnw-80 mxw-100" },
          { targets: [7], className: "text-sm2 text-center text-truncate align-middle mnw-60 mxw-80", orderable: false, searchable: false },
        ],
        order: [[1, 'asc']],
        pagingType: "full_numbers",
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        responsive: false,
        language: getLanguageConfig('User'),
        initComplete : function(settings, json){
          setupInitComplete(table, dtName, 1);
        }
      });
      setupKeyUpColumnSearch(table, dtName);

      @if($errors->any())
        $('#h5userTitle').text($('#userTitle').val());
        $('input[name="_method"]').val($('#userMethod').val());
        $('#frmUser').attr('action', $('#userAction').val());

        updateNedaFields();
        $("#divAcSession").attr("hidden", true);
        if ($('#userMethod').val() == 'put') {
          generateTDTable(@php echo json_encode(old('trustedDevice'), JSON_HEX_APOS); @endphp);
          $("#divAcSession").attr("hidden", false);
        }
        
        $("#user-modal").attr("hidden", false);
        $("#user-modal").modal("show");
      @endif
    });

    $('#datatable-users').on('draw.dt', function() {
      refreshToolTip();
    });

    function ocSubmit() {
      $("#btnSave").attr("hidden", true);
      $("#btnSaveDisabled").attr("hidden", false);
      $("#frmUser").submit();
    }

    function showUser(myData = [], myAction = '', myPhoto = '/assets/img/default-avatar.jpg') {
      removeAllElementError();
      if (myData.length == 0) {
        $('#userTitle').val('New User');
        $('#userMethod').val('post');
        $('#userAction').val(newAction);

        $('#firstname').val('');
        $('#middlename').val('');
        $('#lastname').val('');
        $('#birthday').val('');
        $('#designation').val('');
        $('#email').val('');
        $('#phone').val('');

        $('#usec_lastname').val('');
        $('#usec_firstname').val('');
        $('#usec_middlename').val('');
        $('#usec_designation').val('');
        $('#usec_email').val('');
        $('#usec_phone').val('');
        $('#director_lastname').val('');
        $('#director_firstname').val('');
        $('#director_middlename').val('');
        $('#director_designation').val('');
        $('#director_email').val('');
        $('#director_phone').val('');

        $('#division').val('');
        $('#unit').val('');
        $('#location').val('');
        $('#new-password').val('');
        $('#confirm-password').val('');

        tomSelects['agency_id'].setValue('');
        tomSelects['gender'].setValue('');
        // tomSelects['emailnotif'].setValue('');
        tomSelects['role_id'].setValue('');
        
        tomSelects['staff_id'].setValue('');
        tomSelects['position_id'].setValue('');
        tomSelects['division_id'].setValue('');

        $("#spanNP").attr("hidden", false);
        $("#spanCP").attr("hidden", false);
        // $("#divAdditionalSettings").attr("hidden", true);

        $('#enabledark').prop('checked', false);
        $('#autohidecharts').prop('checked', false);
        $('#emailnotif').prop('checked', true);
        $('#twofactor').prop('checked', true);
        $('#twofactortype_email').prop('checked', true);
        $('#twofactortype_sms').prop('checked', false);
        $('#twofactortype_auth_app').prop('checked', false);
        $('#pEmail').text('');
        $('#pSMS').text('');
        $('#pAuthApp').text('Microsoft Authenticator');

        generateTDTable([]);
        $("#divAcSession").attr("hidden", true);
      } else {
        $('#userTitle').val('Edit User : ' + myData.email);
        $('#userMethod').val('put');
        $('#userAction').val(myAction);

        $('#firstname').val(myData.firstname);
        $('#middlename').val(myData.middlename);
        $('#lastname').val(myData.lastname);
        $('#birthday').val(myData.birthday);
        $('#designation').val(myData.designation);
        $('#email').val(myData.email);
        $('#phone').val(myData.phone);

        $('#usec_lastname').val(myData.usec_lastname);
        $('#usec_firstname').val(myData.usec_firstname);
        $('#usec_middlename').val(myData.usec_middlename);
        $('#usec_designation').val(myData.usec_designation);
        $('#usec_email').val(myData.usec_email);
        $('#usec_phone').val(myData.usec_phone);
        $('#director_lastname').val(myData.director_lastname);
        $('#director_firstname').val(myData.director_lastname);
        $('#director_middlename').val(myData.director_middlename);
        $('#director_designation').val(myData.director_designation);
        $('#director_email').val(myData.director_email);
        $('#director_phone').val(myData.director_phone);

        $('#division').val(myData.division);
        $('#unit').val(myData.unit);
        $('#location').val(myData.location);
        $('#new-password').val('');
        $('#confirm-password').val('');

        tomSelects['agency_id'].setValue(myData.agency_id);
        tomSelects['gender'].setValue(myData.gender);
        // tomSelects['emailnotif'].setValue(myData.emailnotif);
        tomSelects['role_id'].setValue(myData.role_id);
        
        tomSelects['staff_id'].setValue(myData.staff_id);
        tomSelects['position_id'].setValue(myData.position_id);
        tomSelects['division_id'].setValue(myData.division_id);

        $("#spanNP").attr("hidden", true);
        $("#spanCP").attr("hidden", true);
        // $("#divAdditionalSettings").attr("hidden", false);

        $('#enabledark').prop('checked', (myData.enabledark == 'Y' ? true : false));
        $('#autohidecharts').prop('checked', (myData.autohidecharts == 'Y' ? true : false));
        $('#emailnotif').prop('checked', (myData.emailnotif == 'Y' ? true : false));
        $('#twofactor').prop('checked', (myData.twofactor == 'Y' ? true : false));
        $('#twofactortype_email').prop('checked', (myData.twofactortype == 'Email' ? true : false));
        $('#twofactortype_sms').prop('checked', false);
        $('#twofactortype_auth_app').prop('checked', false);
        $('#pEmail').text(myData.email);
        $('#pSMS').text(myData.phone);
        $('#pAuthApp').text('Microsoft Authenticator');
        
        generateTDTable(myData.trusted_devices);
        $("#divAcSession").attr("hidden", false);
      }
      ocTwoFactor();
      updateNedaFields();

      $('#old_avatar').val(myPhoto);
      $('#avatar-preview').attr('src', myPhoto);

      $('#h5userTitle').text($('#userTitle').val());
      $('input[name="_method"]').val($('#userMethod').val());
      $('#frmUser').attr('action', $('#userAction').val());
      $("#user-modal").attr("hidden", false);
      $("#user-modal").modal("show");
    }

    function generateTDTable(myTrustedDevices) {
      $('#tbodyTrustedDevices').empty();
      $.each(myTrustedDevices, function (key, value) {
        badge_color = '';
        revoke_btn = ``;
        location_rem = ``;
        if (value.status == 'Revoked') {
          badge_color = 'danger';
        } else if (value.status == 'Expired') {
          badge_color = 'warning';
        } else {
          badge_color = 'success';
          revoke_btn = `<a data-bs-toggle="tooltip" data-bs-original-title="Revoke" class="border-0 bg-transparent px-1" href="/user-profile/${value.id}"><i class="fa fa-close text-danger"></i></a>`;
        }
        if (value.location_city == null || value.location_city == '') {

        } else {
          location_rem = ` - near ${value.location_city}`;
        }
        $('#tbodyTrustedDevices').append(`
          <tr>
            <td class="ps-1">
              <div class="my-auto">
                <span class="text-sm d-block text-sm">${value.device_name}${location_rem}</span>
                <input name="trustedDevice[${key}][id]" value="${value.id}" hidden>
                <input name="trustedDevice[${key}][device_name]" value="${value.device_name}" hidden>
                <input name="trustedDevice[${key}][location_city]" value="${value.location_city ?? ''}" hidden>
              </div>
            </td>
            <td class="ps-1">
              <div class="text-center">
                <span class="d-block text-sm">${value.ip}</span>
                <input name="trustedDevice[${key}][ip]" value="${value.ip}" hidden>
              </div>
            </td>
            <td class="ps-1">
              <div class="text-center">
                <span class="d-block text-sm">${value.last_seen_at}</span>
                <input name="trustedDevice[${key}][last_seen_at]" value="${value.last_seen_at}" hidden>
              </div>
            </td>
            <td class="ps-1">
              <div class="text-center">
                <span class="badge badge-${badge_color} badge-sm my-auto ms-auto">${value.status}</span>
                <input name="trustedDevice[${key}][status]" value="${value.status}" hidden>
              </div>
            </td>
            <td class="ps-1">
              <div class="text-center">${revoke_btn}</div>
            </td>
          </tr>
        `);
      });
      refreshToolTip();
    }

    function updateavatar() {
      var reader = new FileReader();
      reader.onload = function (e) {
        $('#avatar-preview').attr('src', e.target.result);
      };
      reader.readAsDataURL($('#file-input').prop('files')[0]);
    }

    function updateNedaFields() {
      $("#divPositionId").attr("hidden", false);
      if (depDevAgencyIds.includes(String($('#agency_id').val()))) {
        $("#divDivisionId").attr("hidden", false);
        $("#divStaffId").attr("hidden", false);
        ocStaff();
      } else {
        $("#divDivisionId").attr("hidden", true);
        $("#divStaffId").attr("hidden", true);
        tomSelects['staff_id'].setValue('');
        tomSelects['division_id'].setValue('');
        tomSelects['division_id'].clearOptions();
      }
    }

    
    function ocStaff() {
      let old_division_id = $('#division_id').val();

      tomSelects['division_id'].clear();
      tomSelects['division_id'].clearOptions();
      if ($('#staff_id').val() == '' || $('#staff_id').val() == null) {
      } else {
        let newOptions = [];
        let filteredobjects = divisions.filter(item => item.staff_id == $('#staff_id').val());
        let hit = 0;
        $.each(filteredobjects, function (key, value) {
          newOptions.push({
            value: value.id.toString(),
            text: value.name + ' (' + value.abbreviation + ')',
            name: value.name,
            abbreviation: value.abbreviation,
          });
          if (value.id == old_division_id) {
            hit = 1;
          }
        });
        tomSelects['division_id'].addOptions(newOptions); 
        if (hit == 1) {
          tomSelects['division_id'].setValue(old_division_id);
        }
      }
      // tomSelects['division_id'].refreshOptions();
    }
  </script>
@endpush
