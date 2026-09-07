@php
    $class_theme = session('user_settings.class_theme', '');
@endphp

@extends('layouts.app')

@section('content')

    {{-- Navbar --}}
    <nav
        class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
        id="navbarBlur"
        data-scroll="false"
    >
        <div class="container-fluid py-1 px-3">
            @include('layouts.navbars.auth.topnav', ['title' => 'Roles and Permissions'])
            @include('layouts.navbars.auth.topnav-withdatetime')
        </div>
    </nav>

    {{-- Main content --}}
    <div class="container-fluid">

        <div class="mt-4">
            <div
                class="overflow-hidden rounded-2xl
                       border border-slate-200 bg-white shadow-sm"
            >

                {{-- Header --}}
                <div
                    class="flex flex-col gap-4 border-b border-slate-200
                           px-5 py-5
                           sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center
                                   justify-center rounded-xl
                                   bg-slate-100 text-slate-600"
                        >
                            <i class="fa fa-key" aria-hidden="true"></i>
                        </div>

                        <div>
                            <h2
                                class="mb-0 text-base font-bold
                                       text-slate-900"
                            >
                                Roles and Permissions
                            </h2>

                            <p
                                class="mb-0 mt-1 text-sm
                                       text-slate-500"
                            >
                                Manage DIREK user roles and their access permissions.
                            </p>
                        </div>
                    </div>

                    @can('create', App\Models\Role::class)
                        <button
                            type="button"
                            class="inline-flex w-fit items-center
                                   gap-2 rounded-lg border-0
                                   bg-sky-600 px-4 py-2.5
                                   text-sm font-semibold text-white
                                   shadow-sm transition
                                   hover:bg-sky-700"
                            onclick="showRole()"
                            data-bs-toggle="tooltip"
                            data-bs-original-title="Add New Role"
                        >
                            <i
                                class="fa fa-plus"
                                aria-hidden="true"
                            ></i>

                            Add Role
                        </button>
                    @endcan
                </div>

                {{-- Information --}}
                <div
                    class="flex items-start gap-2
                           border-b border-sky-100
                           bg-sky-50 px-5 py-3
                           text-xs text-sky-700"
                >
                    <i
                        class="fa fa-info-circle mt-0.5"
                        aria-hidden="true"
                    ></i>

                    <span>
                        Roles define what users can access and perform in DIREK.
                        Permission assignments can be managed from the role actions.
                    </span>
                </div>

                {{-- Table --}}
                <div class="p-4 sm:p-5">
                    <div
                        class="overflow-hidden rounded-xl
                               border border-slate-200"
                    >
                        <div class="table-responsive">
                            <table
                                class="table table-hover mb-0"
                                id="datatable-roles"
                                cellspacing="0"
                                width="100%"
                                style="width: 100%;"
                            >
                                <thead>
                                    <tr>
                                        <th
                                            class="text-uppercase
                                                   text-secondary text-xxs
                                                   font-weight-bolder
                                                   opacity-7 p-3"
                                        >
                                            Name
                                        </th>

                                        <th
                                            class="text-uppercase
                                                   text-secondary text-xxs
                                                   font-weight-bolder
                                                   opacity-7 p-3"
                                        >
                                            Description
                                        </th>

                                        <th
                                            class="text-uppercase
                                                   text-secondary text-xxs
                                                   font-weight-bolder
                                                   opacity-7 p-3"
                                        >
                                            Created By
                                        </th>

                                        <th
                                            class="text-uppercase
                                                   text-center text-secondary
                                                   text-xxs font-weight-bolder
                                                   opacity-7 p-3"
                                        >
                                            Created Date
                                        </th>

                                        <th
                                            class="text-uppercase
                                                   text-center text-secondary
                                                   text-xxs font-weight-bolder
                                                   opacity-7 p-3"
                                        >
                                            Action
                                        </th>
                                    </tr>
                                </thead>

                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Footer --}}
        @include('layouts.footers.auth.footer')

    </div>

    {{-- Role form --}}
    <form
        method="POST"
        id="frmRole"
        autocomplete="off"
    >
        @csrf
        @method('post')

        <div
            class="modal fade"
            id="role-modal"
            style="display: none;"
            tabindex="-1"
            role="dialog"
            aria-labelledby="h5roleTitle"
            aria-hidden="true"
            hidden
        >
            <div
                class="modal-dialog modal-dialog-centered"
            >
                <div
                    class="modal-content
                           {{ isset($class_theme) && $class_theme === 'dark'
                                ? 'bg-default'
                                : '' }}"
                >

                    {{-- Modal header --}}
                    <div
                        class="modal-header border-bottom
                               border-slate-200 px-4 py-3"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-9 w-9 shrink-0
                                       items-center justify-center
                                       rounded-lg bg-slate-100
                                       text-slate-600"
                            >
                                <i
                                    class="fa fa-key"
                                    aria-hidden="true"
                                ></i>
                            </div>

                            <div>
                                <h5
                                    class="modal-title mb-0
                                           text-base font-bold"
                                    id="h5roleTitle"
                                >
                                    Role
                                </h5>

                                <p
                                    class="mb-0 mt-0.5 text-xs
                                           text-slate-500"
                                >
                                    Define the role name and its purpose in DIREK.
                                </p>
                            </div>
                        </div>

                        <div hidden>
                            <input
                                name="roleTitle"
                                id="roleTitle"
                                value="{{ old('roleTitle') }}"
                            >

                            <input
                                name="roleAction"
                                id="roleAction"
                                value="{{ old('roleAction') }}"
                            >

                            <input
                                name="roleMethod"
                                id="roleMethod"
                                value="{{ old('roleMethod') }}"
                            >
                        </div>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>
                    </div>

                    {{-- Modal body --}}
                    <div class="modal-body p-4 sm:p-5">

                        {{-- Name --}}
                        <div>
                            <label
                                for="name"
                                class="mb-2 block text-sm
                                       font-semibold text-slate-700"
                            >
                                Role Name
                                <span class="text-rose-500">*</span>
                            </label>

                            <input
                                name="name"
                                id="name"
                                class="form-control"
                                type="text"
                                placeholder="e.g. Director"
                                value="{{ old('name') }}"
                            >

                            @error('name')
                                <p
                                    class="mb-0 mt-1
                                           text-xs text-danger"
                                >
                                    {{ $message }}
                                </p>
                            @enderror

                            <p
                                class="mb-0 mt-1.5
                                       text-xs text-slate-500"
                            >
                                Use a clear role name based on the user's responsibility.
                            </p>
                        </div>

                        {{-- Description --}}
                        <div class="mt-4">
                            <label
                                for="description"
                                class="mb-2 block text-sm
                                       font-semibold text-slate-700"
                            >
                                Description
                                <span class="text-rose-500">*</span>
                            </label>

                            <textarea
                                name="description"
                                id="description"
                                rows="4"
                                class="form-control w-100"
                                placeholder="Describe the responsibilities of this role."
                            >{{ old('description') }}</textarea>

                            @error('description')
                                <p
                                    class="mb-0 mt-1
                                           text-xs text-danger"
                                >
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                    </div>

                    {{-- Modal footer --}}
                    <div
                        class="modal-footer border-top
                               border-slate-200 px-4 py-3"
                    >
                        <button
                            type="button"
                            class="btn btn-outline-secondary mb-0"
                            data-bs-dismiss="modal"
                        >
                            Cancel
                        </button>

                        <button
                            class="btn btn-primary mb-0"
                            type="button"
                            id="btnSave"
                            onclick="ocSubmit()"
                        >
                            <i
                                class="fa fa-save me-1"
                                aria-hidden="true"
                            ></i>

                            Save Role
                        </button>

                        <button
                            class="btn btn-primary mb-0"
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
                    </div>

                </div>
            </div>
        </div>
    </form>

@endsection

@push('css')

<style>
    #datatable-roles_wrapper .dataTables_filter input,
    #datatable-roles_wrapper .dataTables_length select {
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        background: #ffffff;
    }

    #datatable-roles_wrapper .dataTables_filter input:focus,
    #datatable-roles_wrapper .dataTables_length select:focus {
        border-color: #94a3b8;
        outline: none;
        box-shadow: none;
    }

    #datatable-roles tbody tr {
        transition: background-color 0.15s ease;
    }

    #datatable-roles tbody tr:hover {
        background: #f8fafc;
    }

    #datatable-roles_wrapper .dataTables_info,
    #datatable-roles_wrapper .dataTables_length,
    #datatable-roles_wrapper .dataTables_filter {
        color: #64748b;
        font-size: 0.78rem;
    }

    #datatable-roles_wrapper .paginate_button {
        border-radius: 0.5rem !important;
    }

    #datatable-roles td {
        vertical-align: middle;
    }
</style>

@endpush

@push('js')

<script>
    var table = null;
    var newAction = "{{ route('roles.store') }}";

    $(document).ready(function () {
        var dtName = 'datatable-roles';

        // Initialize table search
        createColumnSearch(
            dtName,
            [4],
            [3]
        );

        // Initialize DataTable
        table = $('#' + dtName).DataTable({
            ajax: getAjaxConfig(
                "{{ route('getroles') }}",
                "{{ csrf_token() }}"
            ),

            stateSave: true,

            stateLoadParams: function (settings, data) {
                setupStateLoadParams(
                    dtName,
                    data
                );
            },

            searchDelay: 500,
            serverSide: true,
            processing: true,

            columns: [
                {
                    data: 'name'
                },
                {
                    data: 'description'
                },
                {
                    data: 'creator'
                },
                {
                    data: 'created_at'
                },
                {
                    data: 'actions'
                }
            ],

            columnDefs: [
                {
                    targets: [0],
                    className:
                        "text-sm2 font-weight-normal " +
                        "text-truncate mnw-180 mxw-200"
                },
                {
                    targets: [1],
                    className:
                        "text-sm2 font-weight-normal " +
                        "text-truncate mnw-260 mxw-280"
                },
                {
                    targets: [2],
                    className:
                        "text-sm2 font-weight-normal " +
                        "text-truncate mnw-100 mxw-120"
                },
                {
                    targets: [3],
                    className:
                        "text-sm2 text-center " +
                        "font-weight-normal text-truncate " +
                        "mnw-80 mxw-100"
                },
                {
                    targets: [4],
                    className:
                        "text-sm2 text-center text-truncate " +
                        "mnw-60 mxw-80",
                    orderable: false,
                    searchable: false
                }
            ],

            order: [
                [0, 'asc']
            ],

            pagingType: "full_numbers",

            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],

            responsive: false,

            language: getLanguageConfig('Roles'),

            initComplete: function (settings, json) {
                setupInitComplete(
                    table,
                    dtName
                );
            }
        });

        // Enable column search
        setupKeyUpColumnSearch(
            table,
            dtName
        );

        // Reopen modal after validation error
        @if ($errors->any())
            $('#h5roleTitle')
                .text($('#roleTitle').val());

            $('input[name="_method"]')
                .val($('#roleMethod').val());

            $('#frmRole')
                .attr(
                    'action',
                    $('#roleAction').val()
                );

            $("#role-modal")
                .attr("hidden", false);

            $("#role-modal")
                .modal("show");
        @endif
    });

    // Refresh tooltips
    $('#datatable-roles').on(
        'draw.dt',
        function () {
            refreshToolTip();
        }
    );

    // Save role
    function ocSubmit() {
        $("#btnSave")
            .attr("hidden", true);

        $("#btnSaveDisabled")
            .attr("hidden", false);

        $("#frmRole")
            .submit();
    }

    // Open role modal
    function showRole(myData = [], myAction = '') {
        if (
            !myData ||
            (
                Array.isArray(myData) &&
                myData.length === 0
            )
        ) {
            $('#roleTitle')
                .val('New Role');

            $('#roleMethod')
                .val('post');

            $('#roleAction')
                .val(newAction);

            $('#name')
                .val('');

            $('#description')
                .val('');
        } else {
            $('#roleTitle')
                .val(
                    'Edit Role : ' +
                    myData.name
                );

            $('#roleMethod')
                .val('put');

            $('#roleAction')
                .val(myAction);

            $('#name')
                .val(
                    myData.name || ''
                );

            $('#description')
                .val(
                    myData.description || ''
                );
        }

        $('#h5roleTitle')
            .text(
                $('#roleTitle').val()
            );

        $('input[name="_method"]')
            .val(
                $('#roleMethod').val()
            );

        $('#frmRole')
            .attr(
                'action',
                $('#roleAction').val()
            );

        $("#btnSave")
            .attr("hidden", false);

        $("#btnSaveDisabled")
            .attr("hidden", true);

        $("#role-modal")
            .attr("hidden", false);

        $("#role-modal")
            .modal("show");
    }

    // Reset save button
    $('#role-modal').on(
        'hidden.bs.modal',
        function () {
            $("#btnSave")
                .attr("hidden", false);

            $("#btnSaveDisabled")
                .attr("hidden", true);
        }
    );
</script>

@endpush
