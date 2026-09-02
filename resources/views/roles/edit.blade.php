@php
    $class_theme = session('user_settings.class_theme', '');
@endphp

@extends('layouts.app')

@section('content')

    <!-- Navbar -->
    <nav
        class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
        id="navbarBlur"
        data-scroll="false"
    >
        <div class="container-fluid py-1 px-3">

            @include('layouts.navbars.auth.topnav', [
                'title' => 'Edit: ' . $role->name,
                'subtitle' => 'Edit',
                'links' => [
                    [
                        'name' => 'Roles & Permissions',
                        'url' => route('roles.index')
                    ]
                ]
            ])

            @include('layouts.navbars.auth.topnav-withdatetime')

        </div>
    </nav>
    <!-- End Navbar -->


    <form
        method="POST"
        action="{{ route('roles.update', $role->id) }}"
        enctype="multipart/form-data"
        id="frmUpdate"
    >
        @csrf
        @method('put')


        <div class="container-fluid">

            <!-- Role Information -->
            <div class="row mt-4">

                <div class="col-12">

                    <div class="card">

                        <div class="card-body p-3">

                            <div class="row">

                                <div class="col-12 col-lg-12">

                                    <label class="form-label">
                                        Name
                                    </label>

                                    <div class="input-group">

                                        <input
                                            name="name"
                                            id="name"
                                            value="{{ old('name', $role->name) }}"
                                            class="form-control"
                                            type="text"
                                            placeholder="Name"
                                        >

                                    </div>

                                    @error('name')
                                        <p class="text-danger text-xs">
                                            {{ $message }}
                                        </p>
                                    @enderror

                                </div>

                            </div>


                            <div class="row mt-3">

                                <div class="col-12 col-lg-12">

                                    <label class="form-label">
                                        Description
                                    </label>

                                    <textarea
                                        name="description"
                                        id="description"
                                        rows="2"
                                        class="w-100 form-control"
                                        placeholder="Description"
                                    >{{ old('description', $role->description) }}</textarea>

                                    @error('description')
                                        <p class="text-danger text-xs">
                                            {{ $message }}
                                        </p>
                                    @enderror

                                </div>

                            </div>


                            <div class="row mt-3">

                                <div class="col-12 col-lg-6">

                                    <label class="form-label">
                                        Updated by
                                    </label>

                                    <div class="input-group">

                                        <input
                                            name="creator"
                                            id="creator"
                                            value="{{ old('creator', $role->creator->full_name) }}"
                                            class="form-control"
                                            type="text"
                                            placeholder="Creator"
                                            readonly
                                        >

                                    </div>

                                </div>


                                <div class="col-12 col-lg-6">

                                    <label class="form-label">
                                        Updated At
                                    </label>

                                    <div class="input-group">

                                        <input
                                            name="updated_at"
                                            id="updated_at"
                                            value="{{ old('updated_at', $role->updated_at) }}"
                                            class="form-control"
                                            type="text"
                                            placeholder="Updated At"
                                            readonly
                                        >

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Permissions -->
            <div class="row mt-4">

                <div class="col-12">

                    <div class="card">

                        <div class="card-header d-flex justify-content-between pb-0">

                            <div class="d-flex align-items-center">

                                <h5 class="mb-0">
                                    Permissions
                                </h5>

                            </div>

                        </div>


                        <div class="card-body p-3">

                            <div
                                id="role-permission-accordion"
                                class="role-permission-accordion"
                            >

                                @foreach($categories as $category)

                                    @php
                                        $categoryName =
                                            $category->category == '' ||
                                            $category->category == null
                                                ? 'General'
                                                : ucfirst($category->category);

                                        $categoryKey =
                                            $category->category == '' ||
                                            $category->category == null
                                                ? 'general'
                                                : \Illuminate\Support\Str::slug(
                                                    $category->category
                                                );

                                        $categoryModules = $modules->where(
                                            'category',
                                            '=',
                                            $category->category
                                        );

                                        $hasCheckedPermission = false;

                                        foreach ($categoryModules as $categoryModule) {
                                            foreach ($categoryModule->permissions as $categoryPermission) {
                                                if (in_array(
                                                    $categoryPermission->id,
                                                    $role_permissions
                                                )) {
                                                    $hasCheckedPermission = true;
                                                    break 2;
                                                }
                                            }
                                        }
                                    @endphp


                                    <div
                                        class="role-permission-category border-bottom"
                                        data-permission-category="{{ $categoryKey }}"
                                    >

                                        <button
                                            type="button"
                                            class="role-permission-toggle w-100 border-0 bg-transparent px-2 py-3 d-flex align-items-center text-start"
                                            data-target="rolePermissionPanel{{ $categoryKey }}"
                                            aria-expanded="{{ $hasCheckedPermission ? 'true' : 'false' }}"
                                        >

                                            <span
                                                class="font-weight-bold"
                                                style="font-size: 1rem;"
                                            >
                                                {{ $categoryName }}
                                            </span>


                                            <span class="ms-auto">

                                                <i
                                                    class="fa {{ $hasCheckedPermission ? 'fa-minus' : 'fa-plus' }} text-xs role-permission-toggle-icon"
                                                ></i>

                                            </span>

                                        </button>


                                        <div
                                            id="rolePermissionPanel{{ $categoryKey }}"
                                            class="role-permission-panel"
                                            style="{{ $hasCheckedPermission ? 'display: block;' : 'display: none;' }}"
                                        >

                                            <div class="px-2 pb-3">


                                                @forelse($categoryModules as $module)

                                                    <div
                                                        class="list-group-item border-0 p-4 mb-4 bg-gray-100 border-radius-lg"
                                                    >

                                                        <h6 class="mb-3 text-sm">

                                                            {{ $module->name . ' Module' }}

                                                        </h6>


                                                        <div class="table-responsive">

                                                            <table class="table align-items-center mb-0">

                                                                <thead>

                                                                    <tr>

                                                                        <th
                                                                            class="form-label text-xs font-weight-bolder ps-2 pe-2"
                                                                        >
                                                                            Action
                                                                        </th>

                                                                        <th
                                                                            class="form-label text-xs font-weight-bolder ps-2 pe-2"
                                                                        >
                                                                            Description
                                                                        </th>

                                                                        <th
                                                                            class="form-label text-xs text-center font-weight-bolder ps-2 pe-2"
                                                                        >
                                                                            Allow
                                                                        </th>

                                                                    </tr>

                                                                </thead>


                                                                <tbody>

                                                                    @foreach(
                                                                        $module->permissions->sortBy([
                                                                            ['order', 'asc'],
                                                                            ['id', 'asc'],
                                                                        ])
                                                                        as $permission
                                                                    )

                                                                        <tr
                                                                            @if($permission->id == 48)
                                                                                id="tr_permission_{{ $permission->id }}"

                                                                                @if(!in_array(7, $role_permissions))
                                                                                    hidden
                                                                                @endif
                                                                            @endif
                                                                        >

                                                                            <td
                                                                                class="text-truncate"
                                                                                style="
                                                                                    min-width: 300px;
                                                                                    max-width: 350px;
                                                                                "
                                                                            >

                                                                                <p
                                                                                    class="text-xs mb-0 text-truncate"
                                                                                >
                                                                                    {{ $permission->name }}
                                                                                </p>

                                                                            </td>


                                                                            <td
                                                                                class="text-truncate"
                                                                                style="
                                                                                    min-width: 300px;
                                                                                    max-width: 350px;
                                                                                "
                                                                            >

                                                                                <p
                                                                                    class="text-xs mb-0 text-truncate"
                                                                                >
                                                                                    {{ $permission->description }}
                                                                                </p>

                                                                            </td>


                                                                            <td class="text-center">

                                                                                <div
                                                                                    class="form-check form-switch justify-content-center"
                                                                                >

                                                                                    <input
                                                                                        class="form-check-input"
                                                                                        type="checkbox"
                                                                                        name="permissions[]"
                                                                                        value="{{ $permission->id }}"
                                                                                        id="permission_{{ $permission->id }}"

                                                                                        @if(
                                                                                            in_array(
                                                                                                $permission->id,
                                                                                                $role_permissions
                                                                                            )
                                                                                        )
                                                                                            checked
                                                                                        @endif

                                                                                        @if(
                                                                                            in_array(
                                                                                                $permission->id,
                                                                                                [7, 25, 29, 41, 42]
                                                                                            )
                                                                                        )
                                                                                            onclick="showSubItems({{ $permission->id }})"
                                                                                        @endif
                                                                                    >

                                                                                </div>

                                                                            </td>

                                                                        </tr>

                                                                    @endforeach

                                                                </tbody>

                                                            </table>

                                                        </div>

                                                    </div>

                                                @empty

                                                    <div
                                                        class="px-2 py-3 text-muted text-sm"
                                                    >
                                                        No modules are available in this category.
                                                    </div>

                                                @endforelse

                                            </div>

                                        </div>

                                    </div>

                                @endforeach

                            </div>


                            <!-- Buttons -->
                            <div
                                class="text-center justify-content-center mt-4"
                                id="divBtnSave"
                            >

                                <button
                                    class="btn bg-gradient-primary m-0 ms-2"
                                    type="button"
                                    id="btnSave"
                                    onclick="ocSubmit()"
                                >
                                    Save
                                </button>


                                <button
                                    class="btn bg-gradient-primary m-0 ms-2"
                                    type="button"
                                    id="btnSaveDisabled"
                                    disabled
                                    hidden
                                >

                                    <span
                                        class="spinner-grow spinner-grow-sm"
                                        role="status"
                                        aria-hidden="true"
                                    ></span>

                                    Saving...

                                </button>


                                <button
                                    type="button"
                                    class="btn bg-gradient-dark m-0 ms-2"
                                    id="btnCancel"
                                    onclick="occancel()"
                                >
                                    Cancel
                                </button>

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

        document.addEventListener('DOMContentLoaded', function () {

            // Permission accordion
            const toggles = document.querySelectorAll(
                '.role-permission-toggle'
            );

            toggles.forEach(function (toggle) {

                toggle.addEventListener('click', function () {

                    const targetId = toggle.getAttribute('data-target');

                    const panel = document.getElementById(targetId);

                    if (!panel) {
                        return;
                    }

                    const icon = toggle.querySelector(
                        '.role-permission-toggle-icon'
                    );

                    const isOpen = panel.style.display !== 'none';


                    // Close all categories
                    document
                        .querySelectorAll('.role-permission-panel')
                        .forEach(function (otherPanel) {

                            otherPanel.style.display = 'none';

                        });


                    document
                        .querySelectorAll('.role-permission-toggle')
                        .forEach(function (otherToggle) {

                            otherToggle.setAttribute(
                                'aria-expanded',
                                'false'
                            );

                            const otherIcon = otherToggle.querySelector(
                                '.role-permission-toggle-icon'
                            );

                            if (otherIcon) {

                                otherIcon.classList.remove('fa-minus');

                                otherIcon.classList.add('fa-plus');

                            }

                        });


                    // Open selected category if it was closed
                    if (!isOpen) {

                        panel.style.display = 'block';

                        toggle.setAttribute(
                            'aria-expanded',
                            'true'
                        );

                        if (icon) {

                            icon.classList.remove('fa-plus');

                            icon.classList.add('fa-minus');

                        }

                    }

                });

            });

        });


        function occancel() {

            if (
                confirm(
                    'Are you sure you want to cancel?'
                ) === true
            ) {

                location.reload();

            }

        }


        function ocSubmit() {

            $("#btnSave").attr(
                "hidden",
                true
            );

            $("#btnCancel").attr(
                "disabled",
                true
            );

            $("#btnSaveDisabled").attr(
                "hidden",
                false
            );

            $("#frmUpdate").submit();

        }

    </script>

@endpush
