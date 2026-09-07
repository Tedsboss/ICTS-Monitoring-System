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
            @include('layouts.navbars.auth.topnav', ['title' => 'Division Management'])
            @include('layouts.navbars.auth.topnav-withdatetime')
        </div>
    </nav>

    {{-- Main content --}}
    <div class="container-fluid">

        <div class="mt-4">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                {{-- Header --}}
                <div
                    class="flex flex-col gap-3 border-b border-slate-200 px-5 py-5
                           sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center
                                   rounded-xl bg-slate-100 text-slate-600"
                        >
                            <i class="fa fa-sitemap" aria-hidden="true"></i>
                        </div>

                        <div>
                            <h2 class="mb-0 text-base font-bold text-slate-900">
                                Division Management
                            </h2>

                            <p class="mb-0 mt-1 text-sm text-slate-500">
                                Manage divisions, reviewing status, and division head information.
                            </p>
                        </div>
                    </div>

                    <div
                        class="inline-flex w-fit items-center gap-2 rounded-full
                               border border-slate-200 bg-slate-50 px-3 py-1.5
                               text-xs font-semibold text-slate-600"
                    >
                        <i class="fa fa-building" aria-hidden="true"></i>
                        Organizational Structure
                    </div>
                </div>

                {{-- Table --}}
                <div class="p-4 sm:p-5">
                    <div class="overflow-hidden rounded-xl border border-slate-200">
                        <div class="table-responsive">
                            <table
                                class="table table-hover mb-0"
                                id="datatable-divisions"
                                cellspacing="0"
                                width="100%"
                                style="width: 100%;"
                            >
                                <thead>
                                    <tr>
                                        <th
                                            class="text-uppercase text-secondary text-xxs
                                                   font-weight-bolder opacity-7 p-3"
                                        >
                                            Name
                                        </th>

                                        <th
                                            class="text-uppercase text-center text-secondary
                                                   text-xxs font-weight-bolder opacity-7 p-3"
                                        >
                                            Abbreviation
                                        </th>

                                        <th
                                            class="text-uppercase text-center text-secondary
                                                   text-xxs font-weight-bolder opacity-7 p-3"
                                        >
                                            Staff
                                        </th>

                                        <th
                                            class="text-uppercase text-secondary text-xxs
                                                   font-weight-bolder opacity-7 p-3"
                                        >
                                            Head Name
                                        </th>

                                        <th
                                            class="text-uppercase text-center text-secondary
                                                   text-xxs font-weight-bolder opacity-7 p-3"
                                        >
                                            Head Position
                                        </th>

                                        <th
                                            class="text-uppercase text-center text-secondary
                                                   text-xxs font-weight-bolder opacity-7 p-3"
                                        >
                                            Head Email
                                        </th>

                                        <th
                                            class="text-uppercase text-center text-secondary
                                                   text-xxs font-weight-bolder opacity-7 p-3"
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

        @include('layouts.footers.auth.footer')

    </div>

    {{-- Division form --}}
    <form
        method="POST"
        id="frmDivision"
        autocomplete="off"
    >
        @csrf
        @method('post')

        <div
            class="modal fade"
            id="division-modal"
            style="display: none;"
            tabindex="-1"
            role="dialog"
            aria-labelledby="h5divisionTitle"
            aria-hidden="true"
            hidden
        >
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div
                    class="modal-content
                           {{ isset($class_theme) && $class_theme === 'dark'
                                ? 'bg-default'
                                : '' }}"
                >

                    {{-- Modal header --}}
                    <div class="modal-header border-bottom border-slate-200 px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-9 w-9 items-center justify-center
                                       rounded-lg bg-slate-100 text-slate-600"
                            >
                                <i class="fa fa-sitemap" aria-hidden="true"></i>
                            </div>

                            <div>
                                <h5
                                    class="modal-title mb-0 text-base font-bold"
                                    id="h5divisionTitle"
                                >
                                    Division
                                </h5>

                                <p class="mb-0 mt-0.5 text-xs text-slate-500">
                                    Update the selected division information.
                                </p>
                            </div>
                        </div>

                        <div hidden>
                            <input
                                name="divisionTitle"
                                id="divisionTitle"
                                value="{{ old('divisionTitle') }}"
                            >

                            <input
                                name="divisionAction"
                                id="divisionAction"
                                value="{{ old('divisionAction') }}"
                            >

                            <input
                                name="divisionMethod"
                                id="divisionMethod"
                                value="{{ old('divisionMethod') }}"
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
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                            {{-- Name --}}
                            <div class="sm:col-span-2">
                                <label
                                    for="name"
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Division Name
                                </label>

                                <input
                                    name="name"
                                    id="name"
                                    class="form-control"
                                    type="text"
                                    placeholder="Division Name"
                                    value="{{ old('name') }}"
                                >

                                @error('name')
                                    <p class="mb-0 mt-1 text-xs text-danger">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Abbreviation --}}
                            <div>
                                <label
                                    for="abbreviation"
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Abbreviation
                                </label>

                                <input
                                    name="abbreviation"
                                    id="abbreviation"
                                    class="form-control"
                                    type="text"
                                    placeholder="Abbreviation"
                                    value="{{ old('abbreviation') }}"
                                >

                                @error('abbreviation')
                                    <p class="mb-0 mt-1 text-xs text-danger">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Staff --}}
                            <div>
                                <label
                                    for="staff"
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Staff
                                </label>

                                <input
                                    id="staff"
                                    class="form-control bg-slate-50"
                                    type="text"
                                    placeholder="Staff"
                                    value=""
                                    disabled
                                >

                                <p class="mb-0 mt-1 text-xs text-slate-500">
                                    Parent staff assigned to this division.
                                </p>
                            </div>

                            {{-- Status --}}
                            <div>
                                <label
                                    for="can_review"
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Review Status
                                </label>

                                <select
                                    name="can_review"
                                    id="can_review"
                                    placeholder="Status"
                                    autocomplete="off"
                                    class="hide-search"
                                >
                                    <option value="">
                                        Status
                                    </option>

                                    <option
                                        value="Y"
                                        {{ old('can_review') == 'Y' ? 'selected' : '' }}
                                    >
                                        Enabled
                                    </option>

                                    <option
                                        value="N"
                                        {{ old('can_review') == 'N' ? 'selected' : '' }}
                                    >
                                        Disabled
                                    </option>
                                </select>

                                @error('can_review')
                                    <p class="mb-0 mt-1 text-xs text-danger">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Head name --}}
                            <div>
                                <label
                                    for="head_name"
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Head Name
                                </label>

                                <input
                                    name="head_name"
                                    id="head_name"
                                    class="form-control"
                                    type="text"
                                    placeholder="Head Name"
                                    value="{{ old('head_name') }}"
                                >

                                @error('head_name')
                                    <p class="mb-0 mt-1 text-xs text-danger">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Head position --}}
                            <div>
                                <label
                                    for="head_position"
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Head Position
                                </label>

                                <input
                                    name="head_position"
                                    id="head_position"
                                    class="form-control"
                                    type="text"
                                    placeholder="Head Position"
                                    value="{{ old('head_position') }}"
                                >

                                @error('head_position')
                                    <p class="mb-0 mt-1 text-xs text-danger">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Head email --}}
                            <div>
                                <label
                                    for="head_email"
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Head Email
                                </label>

                                <input
                                    name="head_email"
                                    id="head_email"
                                    class="form-control"
                                    type="email"
                                    placeholder="Head Email"
                                    value="{{ old('head_email') }}"
                                >

                                @error('head_email')
                                    <p class="mb-0 mt-1 text-xs text-danger">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                        </div>
                    </div>

                    {{-- Modal footer --}}
                    <div class="modal-footer border-top border-slate-200 px-4 py-3">
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
                            <i class="fa fa-save me-1" aria-hidden="true"></i>
                            Save Changes
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
    #datatable-divisions_wrapper .dataTables_filter input,
    #datatable-divisions_wrapper .dataTables_length select {
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        background: #ffffff;
    }

    #datatable-divisions_wrapper .dataTables_filter input:focus,
    #datatable-divisions_wrapper .dataTables_length select:focus {
        border-color: #94a3b8;
        outline: none;
        box-shadow: none;
    }

    #datatable-divisions tbody tr {
        transition: background-color 0.15s ease;
    }

    #datatable-divisions tbody tr:hover {
        background: #f8fafc;
    }

    #datatable-divisions_wrapper .dataTables_info,
    #datatable-divisions_wrapper .dataTables_length,
    #datatable-divisions_wrapper .dataTables_filter {
        color: #64748b;
        font-size: 0.78rem;
    }

    #datatable-divisions_wrapper .paginate_button {
        border-radius: 0.5rem !important;
    }
</style>

@endpush

@push('js')

<script>
    var table = null;

    // Initialize status field
    initTomSelect('can_review');

    $(document).ready(function () {
        var dtName = 'datatable-divisions';

        // Initialize table search
        createColumnSearch(
            dtName,
            [6],
            [1, 2, 4]
        );

        // Initialize DataTable
        table = $('#' + dtName).DataTable({
            ajax: getAjaxConfig(
                "{{ route('getdivisions') }}",
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
                    data: 'abbreviation'
                },
                {
                    data: 'staff',
                    name: 'staff.abbreviation'
                },
                {
                    data: 'head_name'
                },
                {
                    data: 'head_position'
                },
                {
                    data: 'head_email'
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
                        "text-truncate mnw-260 mxw-280"
                },
                {
                    targets: [1, 2],
                    className:
                        "text-sm2 text-center " +
                        "font-weight-normal text-truncate " +
                        "mnw-60 mxw-80"
                },
                {
                    targets: [3],
                    className:
                        "text-sm2 font-weight-normal " +
                        "text-truncate mnw-260 mxw-280"
                },
                {
                    targets: [4],
                    className:
                        "text-sm2 text-center " +
                        "font-weight-normal text-truncate " +
                        "mnw-160 mxw-180"
                },
                {
                    targets: [5],
                    className:
                        "text-sm2 text-center " +
                        "font-weight-normal text-truncate " +
                        "mnw-160 mxw-180"
                },
                {
                    targets: [6],
                    className:
                        "text-sm2 text-center text-truncate " +
                        "mnw-60 mxw-80",
                    orderable: false,
                    searchable: false
                }
            ],

            order: [
                [2, 'asc']
            ],

            pagingType: "full_numbers",

            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],

            responsive: false,

            language: getLanguageConfig('Division'),

            initComplete: function (settings, json) {
                setupInitComplete(
                    table,
                    dtName,
                    2
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
            $('#h5divisionTitle')
                .text($('#divisionTitle').val());

            $('input[name="_method"]')
                .val($('#divisionMethod').val());

            $('#frmDivision')
                .attr(
                    'action',
                    $('#divisionAction').val()
                );

            $("#division-modal")
                .attr("hidden", false);

            $("#division-modal")
                .modal("show");
        @endif
    });

    // Refresh tooltips
    $('#datatable-divisions').on(
        'draw.dt',
        function () {
            $("[data-bs-toggle='tooltip']")
                .tooltip();

            $('.tooltip')
                .tooltip('hide');
        }
    );

    // Save division
    function ocSubmit() {
        $("#btnSave")
            .attr("hidden", true);

        $("#btnSaveDisabled")
            .attr("hidden", false);

        $("#frmDivision")
            .submit();
    }

    // Open division modal
    function showDivision(myData, myAction) {
        $('#divisionTitle')
            .val(
                'Edit : ' +
                myData.abbreviation
            );

        $('#divisionMethod')
            .val('put');

        $('#divisionAction')
            .val(myAction);

        $('#name')
            .val(myData.name);

        $('#abbreviation')
            .val(myData.abbreviation);

        $('#head_name')
            .val(myData.head_name);

        $('#head_position')
            .val(myData.head_position);

        $('#head_email')
            .val(myData.head_email);

        $('#staff')
            .val(
                myData.staff
                    ? myData.staff.abbreviation
                    : ''
            );

        tomSelects['can_review']
            .setValue(
                myData.can_review
            );

        $('#h5divisionTitle')
            .text(
                $('#divisionTitle').val()
            );

        $('input[name="_method"]')
            .val(
                $('#divisionMethod').val()
            );

        $('#frmDivision')
            .attr(
                'action',
                $('#divisionAction').val()
            );

        $("#division-modal")
            .attr("hidden", false);

        $("#division-modal")
            .modal("show");
    }
</script>

@endpush
