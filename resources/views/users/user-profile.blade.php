@php
  $class_theme = session('user_settings.class_theme', '');
  $profileAgencyId = old('agency_id', auth()->user()->agency_id);
  $showStaffDivision = in_array((string) $profileAgencyId, $depDevAgencyIds, true);
@endphp
@extends('layouts.app')

@section('content')
  <!-- Navbar -->
  <nav class="navbar navbar-main navbar-expand-lg  px-0 mx-4 shadow-none border-radius-xl z-index-sticky " id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', ['title' => 'My Profile'])
      @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
  </nav>
  <!-- End Navbar -->

  <div class="container-fluid my-5 py-2">
    {{-- <div class="d-flex justify-content-center mb-5"> --}}
    <div class="row justify-content-center mb-5">
      <div class="col-lg-9 mt-lg-0 mt-4">
        <form autocomplete="off" method="POST" action="{{ route('user-profile.perform') }}" enctype="multipart/form-data" id="frmUpdate">
          @csrf
          <!-- Card Profile -->
          <div class="card card-body" id="profile">
            <div class="row justify-content-center">
              <div class="col-sm-auto col-4">
                <div class="avatar avatar-xl position-relative">
                  <div>
                    <label for="file-input" class="btn btn-sm btn-icon-only bg-gradient-light position-absolute bottom-0 end-0 mb-n2 me-n2">
                      <i class="fa fa-pencil top-0" data-bs-toggle="tooltip" data-bs-placement="top" title="" aria-hidden="true" data-bs-original-title="Edit Image" aria-label="Edit Image"></i>
                      <span class="sr-only">Edit Image</span>
                    </label>

                    <span class="h-12 w-12 rounded-full overflow-hidden bg-gray-100">
                      <img id="avatar-preview" src="{{ auth()->user()->avatarUrl() }}" alt="bruce" class="w-100 border-radius-lg shadow-sm">
                    </span>
                  </div>
                </div>
              </div>

              <div class="col-sm-auto col-8 my-auto">
                <div class="h-100">
                  <h5 class="mb-1 font-weight-bolder"> {{ auth()->user()->firstname ?? '' }} {{ auth()->user()->lastname ?? '' }}</h5>
                  <p class="mb-0 font-weight-bold text-sm">{{ auth()->user()->position_name()  }}</p>
                </div>
              </div>
              <div class="col-sm-auto ms-sm-auto mt-sm-0 mt-3 d-flex">
                {{-- @can('enableMyEmailNotification', App\Models\User::class)
                  <div class="form-group">
                    <p class="form-text text-muted text-xs ms-1">Recieve email notification1?</p>
                    <div class="form-check form-switch ms-2  justify-content-end">
                      <input class="form-check-input" type="checkbox" name="emailnotif" value="1" @if(auth()->user()->emailnotif == 'Y') checked @endif>
                    </div>
                  </div>
                @endcan --}}
              </div>
            </div>
          </div>

          <!-- Card Basic Info -->
          <div class="card mt-4" id="basic-info">
            <div class="card-header">
              <h5>Basic Info</h5>
            </div>
            <div class="card-body pt-0">
              <input type="file" name="avatar" id="file-input" accept="image/*" class="d-none" onchange="updateavatar()">
              <div class="row">
                <div class="col-4">
                  <label class="form-label">First Name <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input id="firstname" name="firstname" class="form-control" type="text" value="{{ old('firstname', auth()->user()->firstname) }}" placeholder="First Name" readonly>
                  </div>
                  @error('firstname')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
                <div class="col-4">
                  <label class="form-label">Middle Name</label>
                  <div class="input-group">
                    <input id="middlename" name="middlename" class="form-control" type="text" value="{{ old('middlename', auth()->user()->middlename) }}" placeholder="Middle Name" readonly>
                  </div>
                  @error('middlename')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
                <div class="col-4">
                  <label class="form-label">Last Name <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input id="lastname" name="lastname" class="form-control" type="text" value="{{ old('lastname', auth()->user()->lastname) }}" placeholder="Last Name" readonly>
                  </div>
                  @error('lastname')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
              </div>
              <div class="row">
                <div class="col-12">
                  <label class="form-label mt-4">Agency <span class="text-danger">*</span></label>
                  @if (auth()->user()->isSuperAdmin() || (auth()->user()->first_login == 'Y' && empty(auth()->user()->agency_id)))
                    <select name="agency_id" id="agency_id" placeholder="Agency" autocomplete="off" onchange="updateStaffDivisionFields()">
                      <option value="">Agency</option>
                      @foreach ($agencies as $agency)
                        <option value="{{ $agency->id }}" @if (old('agency_id', auth()->user()->agency_id) == $agency->id) selected @endif>{{ $agency->display_name }}</option>
                      @endforeach
                    </select>
                  @else
                    <div class="input-group">
                      <input id="agency" name="agency" class="form-control" type="text" disabled value="{{ old('agency', optional(auth()->user()->agency)->display_name) }}" placeholder="Agency">
                    </div>
                  @endif
                  @error('agency_id')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
              </div>
              <div class="row">
                <div class="col-4">
                  <label class="form-label mt-4">Gender <span class="text-danger">*</span></label>
                  <select name="gender" id="gender" placeholder="Gender" autocomplete="off" class="hide-search">
                    <option value="">Gender</option>
                    <option value="Male" {{ old('gender', auth()->user()->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ old('gender', auth()->user()->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                  </select>
                  @error('gender')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
                <div class="col-4">
                  <label class="form-label mt-4">Birth Date <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input id="birthday" name="birthday" class="form-control" type="date" value="{{ old('birthday', auth()->user()->birthday) }}" placeholder="Birth Date">
                  </div>
                  @error('birthday')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
                <div class="col-4">
                  <label class="form-label mt-4">Position <span class="text-danger">*</span></label>
                  <select name="position_id" id="position_id" placeholder="Position" autocomplete="off">
                    <option value="">Position</option>
                    @foreach ($positions as $position)
                      <option value="{{ $position->id }}" @if (old('position_id', auth()->user()->position_id) == $position->id) selected @endif>{{ $position->name }}</option>
                    @endforeach
                  </select>
                  @error('position_id')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
              </div>
              <div class="row">
                <div class="col-6">
                  <label class="form-label mt-4">Email <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input id="email" name="email" class="form-control" type="email" value="{{ old('email', auth()->user()->email) }}" placeholder="example@email.com" readonly>
                  </div>
                  @error('email')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
                <div class="col-6">
                  <label class="form-label mt-4">Phone Number <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input id="phone" name="phone" class="form-control" type="text" value="{{ old('phone', auth()->user()->phone) }}" placeholder="+63 901 567 8910">
                  </div>
                  @error('phone')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
              </div>
              <div class="row" id="rowStaffDivision" @if(!$showStaffDivision) hidden @endif>
                <div class="col-6">
                  <label class="form-label mt-4">Staff <span class="text-danger">*</span></label>
                  @if (auth()->user()->first_login == 'Y')
                    <select name="staff_id" id="staff_id" placeholder="Staff" autocomplete="off" onchange="ocStaff()">
                      <option value="">Staff</option>
                      @foreach ($staffs as $staff)
                        <option value="{{ $staff->id }}" @if (old('staff_id', auth()->user()->staff_id) == $staff->id) selected @endif>{{ $staff->name . ' (' . $staff->abbreviation . ')' }}</option>
                      @endforeach
                    </select>
                    @error('staff_id')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                  @else
                    <div class="input-group">
                      <input id="staff" name="staff" class="form-control" type="text" disabled value="{{ old('staff', optional(auth()->user()->staff)->abbreviation) }}" placeholder="Staff">
                    </div>
                  @endif
                </div>
                <div class="col-6">
                  <label class="form-label mt-4">Division <span class="text-danger">*</span></label>
                  @if (auth()->user()->first_login == 'Y')
                    <select name="division_id" id="division_id" placeholder="Division" autocomplete="off">
                      <option value="">Division</option>
                      @foreach ($divisions as $division)
                        <option value="{{ $division->id }}" data-name="{{ $division->name }}" data-abbreviation="{{ $division->abbreviation }}" @if (old('division_id', auth()->user()->division_id) == $division->id) selected @endif>{{ $division->name . ' (' . $division->abbreviation . ')' }}</option>
                      @endforeach
                    </select>
                    @error('division_id')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                  @else
                    <div class="input-group">
                      <input id="division" name="division" class="form-control" type="text" disabled value="{{ old('division', optional(auth()->user()->division)->abbreviation) }}" placeholder="Division">
                    </div>
                  @endif
                </div>
              </div>
              <div class="row">
                <div class="col-12">
                  <label class="form-label mt-4">Office Location <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input id="location" name="location" class="form-control" type="text" value="{{ old('location', auth()->user()->location) }}" placeholder="Location">
                  </div>
                  @error('location')<p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
              </div>
              <div class="row">
                <div class="col-4">
                  <label class="form-label mt-4">Old Password</label>
                  <div class="input-group">
                    {{-- <input id="old-password" name="old-password" class="form-control" type="password" value="{{ old('old-password') }}" placeholder="Password"> --}}
                    <input id="old-password" name="old-password" class="form-control" type="password" value="" placeholder="Password">
                  </div>
                  @error('old-password') <p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
                <div class="col-4">
                  <label class="form-label mt-4">New Password</label>
                  <div class="input-group">
                    <input id="new-password" name="new-password" class="form-control" type="password" placeholder="Password">
                  </div>
                  @error('new-password') <p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
                <div class="col-4">
                  <label class="form-label mt-4">Confirm Password</label>
                  <div class="input-group">
                    <input id="confirm-password" name="confirm-password" class="form-control" type="password" placeholder="Confirm Password">
                  </div>
                  @error('confirm-password') <p class='text-danger text-xs pt-1'> {{ $message }} </p>@enderror
                </div>
              </div>
            </div>
          </div>


          <!-- Card Security Settings -->
          <div class="card mt-4" id="basic-settings">
            <div class="card-header">
              <h5>Security and Settings</h5>
            </div>
            <div class="card-body pt-0">
              @include('users.components.settings', ['user' => auth()->user()])
            </div>
          </div>

          
          <!-- Card Trusted Devices -->
          <div id="divTrustedDevices" class="card mt-4" id="basic-devices" @if(auth()->user()->twofactor == 'Y') @else hidden @endif>
            <div class="card-header">
              <h5>Trusted Devices</h5>
            </div>
            <div class="card-body pt-0">
              @include('users.components.sessions', ['user' => auth()->user()])
            </div>
          </div>

          <div class="row justify-content-center" hidden>
            <div class="text-center justify-content-center mt-4" id="divBtnSave">
              <button class="btn bg-gradient-primary m-0 ms-2" type="button" id="btnSave" onclick="ocSubmit()">Save</button>
              <button class="btn bg-gradient-primary m-0 ms-2" type="button" id="btnSaveDisabled" disabled hidden>
                <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                Saving...
              </button>
              <button type="button" class="btn bg-gradient-dark m-0 ms-2" id="btnCancel" onclick="occancel()">Cancel</button>
            </div>
          </div>
        </form>
      </div>
    </div>
    @include('layouts.footers.auth.footer')
  </div>
  <div class="fixed-plugin align-middle" id="divSaveFinal" onclick="ocSubmit()" >
    <a data-bs-toggle="tooltip" style="background-color: #e7a167 !important; bottom: 27vh !important;" class="fixed-plugin-button text-bold text-xs text-center font-weight-bolder position-fixed p-2 fixed-plugin-btn opacity-7 align-middle fixed-plugin-btn-custom">
      <div class="row text-center align-middle btn-text-align-center m-0">
        <div class="col-md-3 text-center align-middle btn-text-align-center p-0">
          <i class="fa fa-floppy-o fa-2x"></i>
        </div>
        <div class="col-md-9 fixed-plugin-btn-custom-span text-center align-middle btn-text-align-center p-0 pt-1">
          <span>SAVE</span>
        </div>
      </div>
    </a>
  </div>
  {{-- <div class="fixed-plugin align-middle" id="divSaveFinal" onclick="occancel()" >
    <a data-bs-toggle="tooltip" style="background-color: #2f2f2f !important; bottom: 20vh !important;" class="fixed-plugin-button text-bold text-xs text-center font-weight-bolder position-fixed p-2 fixed-plugin-btn opacity-7 align-middle fixed-plugin-btn-custom">
      <div class="row text-center align-middle btn-text-align-center m-0">
        <div class="col-md-3 text-center align-middle btn-text-align-center p-0">
          <i class="fa fa-close fa-2x"></i>
        </div>
        <div class="col-md-9 fixed-plugin-btn-custom-span text-center align-middle btn-text-align-center p-0 pt-1">
          <span>CANCEL</span>
        </div>
      </div>
    </a>
  </div> --}}
@endsection

@push('js')
  @include('users.components.scripts')
  <script>
    var divisions = @php echo json_encode($divisions) @endphp;
    var depDevAgencyIds = @json($depDevAgencyIds);
    var currentAgencyId = "{{ auth()->user()->agency_id }}";
    initTomSelect('gender');
    initTomSelect('position_id', true);
    @if(auth()->user()->isSuperAdmin() || (auth()->user()->first_login == 'Y' && empty(auth()->user()->agency_id)))
      initTomSelect('agency_id', true);
    @endif
    @if(auth()->user()->first_login == 'Y')
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
    @endif
    updateStaffDivisionFields();

    function ocSubmit() {
      $("#btnSave").attr("hidden", true);
      $("#btnCancel").attr("disabled", true);
      $("#btnSaveDisabled").attr("hidden", false);
      $("#frmUpdate").submit();
    }

    function updateavatar() {
      var reader = new FileReader();
      reader.onload = function (e) {
        $('#avatar-preview').attr('src', e.target.result);
      };
      reader.readAsDataURL($('#file-input').prop('files')[0]);
    }

    function occancel() {
      if (confirm('Are you sure you want to cancel?') == true) {
        location.reload();
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

    function updateStaffDivisionFields() {
      let agencyId = document.getElementById('agency_id') ? $('#agency_id').val() : currentAgencyId;
      let showStaffDivision = depDevAgencyIds.includes(String(agencyId));

      $("#rowStaffDivision").attr("hidden", !showStaffDivision);

      if (!showStaffDivision && typeof tomSelects !== 'undefined' && tomSelects['staff_id']) {
        tomSelects['staff_id'].setValue('');
        tomSelects['division_id'].setValue('');
        tomSelects['division_id'].clearOptions();
      }
    }
  </script>
@endpush
