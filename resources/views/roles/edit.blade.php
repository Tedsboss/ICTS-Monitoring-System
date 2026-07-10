@php
  $class_theme = session('user_settings.class_theme', '');
@endphp
@extends('layouts.app')

@section('content')
  <!-- Navbar -->
  <nav class="navbar navbar-main navbar-expand-lg  px-0 mx-4 shadow-none border-radius-xl z-index-sticky " id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
      @include('layouts.navbars.auth.topnav', [
                                                'title' => 'Edit: ' . $role->name,
                                                'subtitle' => 'Edit',
                                                'links' =>  [
                                                              ['name' => 'Roles & Permissions', 'url' => route('roles.index')]
                                                            ]
                                              ])
      @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
  </nav>
  <!-- End Navbar -->

  <form method="POST" action="{{ route('roles.update', $role->id) }}" enctype="multipart/form-data" id="frmUpdate">
    @csrf
    @method('put')
    <div class="container-fluid">
      <div class="row mt-4">
        <div class="col-12">
          <div class="card">
            {{-- <div class="card-header d-flex justify-content-between pb-0">
              <div class="d-flex align-items-center">
                <h5 class="mb-0">Role</h5>
              </div>
            </div> --}}
            <div class="card-body p-3">
              <div class="row">
                <div class="col-12 col-lg-12">
                  <label class="form-label">Name</label>
                  <div class="input-group">
                    <input name="name" id="name" value="{{ old('name', $role->name) }}" class="form-control" type="text" placeholder="Name">
                  </div>
                  @error('name') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
              </div>
              <div class="row mt-3">
                <div class="col-12 col-lg-12">
                  <label class="form-label">Description</label>
                  <textarea name="description" id="description" rows="2" class="w-100 form-control" placeholder="Description">{{ old('description', $role->description) }}</textarea>
                  @error('description') <p class='text-danger text-xs'> {{ $message }} </p> @enderror
                </div>
              </div>
              <div class="row mt-3">
                <div class="col-12 col-lg-6">
                  <label class="form-label">Updated by</label>
                  <div class="input-group">
                    <input name="creator" id="creator" value="{{ old('creator', $role->creator->full_name) }}" class="form-control" type="text" placeholder="creator" readonly>
                  </div>
                  {{-- @error('name') <p class='text-danger text-xs'> {{ $message }} </p> @enderror --}}
                </div>
                <div class="col-12 col-lg-6">
                  <label class="form-label">Updated At</label>
                  <div class="input-group">
                    <input name="updated_at" id="updated_at" value="{{ old('updated_at', $role->updated_at) }}" class="form-control" type="text" placeholder="Updated At" readonly>
                  </div>
                  {{-- @error('name') <p class='text-danger text-xs'> {{ $message }} </p> @enderror --}}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>


      
      <div class="row mt-4">
        <div class="col-12">
          <div class="card">
            <div class="card-header d-flex justify-content-between pb-0">
              <div class="d-flex align-items-center">
                <h5 class="mb-0">Permissions</h5>
              </div>
            </div>
            <div class="card-body p-3">
              <ul class="list-group">
                <div class="accordion" id="accordionRental">
                  @foreach($categories as $category)
                    @php
                      $categoryKey = $category->category == '' || $category->category == null ? 'general' : \Illuminate\Support\Str::slug($category->category);
                    @endphp
                    <div class="accordion-item mb-3">
                      <h5 class="accordion-header" id="headingAc{{ $categoryKey }}">
                        <button style="font-size: 1rem !important;" data-bs-target="#collapseAc{{ $categoryKey }}" aria-controls="collapseAc{{ $categoryKey }}" aria-expanded="false" data-bs-toggle="collapse" class="accordion-button border-bottom font-weight-bold" type="button" >
                          {{ $category->category == '' || $category->category == null ? 'General' : ucfirst($category->category) }}
                          <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3"></i>
                          <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3"></i>
                        </button>
                      </h5>
                      <div id="collapseAc{{ $categoryKey }}" data-bs-parent="#accordionRental" class="accordion-collapse collapse" aria-labelledby="headingAc{{ $categoryKey }}">
                        <div class="accordion-body">
                          @foreach($modules->where('category', '=', $category->category) as $module)
                            <li class="list-group-item border-0 p-4 mb-5 bg-gray-100 border-radius-lg">
                              <h6 class="mb-3 text-sm">{{ $module->name . ' Module' }}</h6>
                              <div class="table-responsive">
                                <table class="table align-items-center mb-0">
                                  <thead>
                                    <tr>
                                      <th class="form-label text-xs font-weight-bolder ps-2 pe-2">Action</th>
                                      <th class="form-label text-xs font-weight-bolder ps-2 pe-2">Description</th>
                                      <th class="form-label text-xs text-center font-weight-bolder ps-2 pe-2">Allow</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($module->permissions->sortBy([['order', 'asc'],['id', 'asc'],]) as $permission)
                                      <tr @if($permission->id == 48) id="tr_permission_{{ $permission->id }}" @if(!in_array(7, $role_permissions)) hidden @endif @endif>
                                        <td class="text-truncate" style="min-width: 300px; max-width: 350px;"><p class="text-xs mb-0 text-truncate">{{ $permission->name }}</p></td>
                                        <td class="text-truncate" style="min-width: 300px; max-width: 350px;"><p class="text-xs mb-0 text-truncate">{{ $permission->description }}</p></td>
                                        <td class="text-center">
                                          <div class="form-check form-switch justify-content-center">
                                            {{-- <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" @if(in_array($permission->id, $role_permissions)) checked @endif @if($permission->id == 29 || $permission->id == 30) id="permission_{{ $permission->id }}" onclick="showSubItems({{ $permission->id }})"  @endif> --}}
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="permission_{{ $permission->id }}" @if(in_array($permission->id, $role_permissions)) checked @endif @if(in_array($permission->id, [7, 25, 29, 41, 42])) onclick="showSubItems({{ $permission->id }})"  @endif>
                                          </div>
                                        </td>
                                      </tr>
                                    @endforeach
                                  </tbody>
                                </table>
                              </div>
                            </li>
                          @endforeach
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>

              </ul>
              <div class="text-center justify-content-center mt-0" id="divBtnSave">
                <button class="btn bg-gradient-primary m-0 ms-2" type="button" id="btnSave" onclick="ocSubmit()">Save</button>
                <button class="btn bg-gradient-primary m-0 ms-2" type="button" id="btnSaveDisabled" disabled hidden>
                  <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                  Saving...
                </button>
                <button type="button" class="btn bg-gradient-dark m-0 ms-2" id="btnCancel" onclick="occancel()">Cancel</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      @include('layouts.footers.auth.footer')
    </div>
  </form>

@endsection

@push('js')
  <script>
    $(document).ready(function() {

    });

    function occancel() {
      if (confirm('Are you sure you want to cancel?') == true) {
        location.reload();
      }
    }
  
    function ocSubmit() {
      $("#btnSave").attr("hidden", true);
      $("#btnCancel").attr("disabled", true);
      $("#btnSaveDisabled").attr("hidden", false);
      $("#frmUpdate").submit();
    }
  </script>
@endpush
