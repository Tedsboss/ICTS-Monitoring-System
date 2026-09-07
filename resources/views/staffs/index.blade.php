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
            @include('layouts.navbars.auth.topnav', ['title' => 'Staff Management'])
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
                            <i class="fa fa-building" aria-hidden="true"></i>
                        </div>

                        <div>
                            <h2 class="mb-0 text-base font-bold text-slate-900">
                                Staff Management
                            </h2>

                            <p class="mb-0 mt-1 text-sm text-slate-500">
                                Manage offices and their designated heads in DIREK.
                            </p>
                        </div>
                    </div>

                    <div
                        class="inline-flex w-fit items-center gap-2 rounded-full
                               border border-slate-200 bg-slate-50 px-3 py-1.5
                               text-xs font-semibold text-slate-600"
                    >
                        <i class="fa fa-sitemap" aria-hidden="true"></i>
                        Organizational Structure
                    </div>
                </div>

                {{-- Table --}}
                <div class="p-4 sm:p-5">
                    <div class="overflow-hidden rounded-xl border border-slate-200">
                        <div class="table-responsive">
                            <table
                                class="table table-hover mb-0"
                                id="datatable-staffs"
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

    {{-- Staff form --}}
    <form
        method="POST"
        id="frmStaff"
        autocomplete="off"
    >
        @csrf
        @method('post')

        <div
            class="modal fade"
            id="staff-modal"
            style="display: none;"
            tabindex="-1"
            role="dialog"
            aria-labelledby="h5staffTitle"
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
                                <i class="fa fa-building" aria-hidden="true"></i>
                            </div>

                            <div>
                                <h5
                                    class="modal-title mb-0 text-base font-bold"
                                    id="h5staffTitle"
                                >
                                    Staff
                                </h5>

                                <p class="mb-0 mt-0.5 text-xs text-slate-500">
                                    Update the selected staff or office information.
                                </p>
                            </div>
                        </div>

                        <div hidden>
                            <input
                                name="staffTitle"
                                id="staffTitle"
                                value="{{ old('staffTitle') }}"
                            >

                            <input
                                name="staffAction"
                                id="staffAction"
                                value="{{ old('staffAction') }}"
                            >

                            <input
                                name="staffMethod"
                                id="staffMethod"
                                value="{{ old('staffMethod') }}"
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
                                    Staff Name
                                </label>

                                <input
                                    name="name"
                                    id="name"
                                    class="form-control"
                                    type="text"
                                    placeholder="Staff Name"
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
    #datatable-staffs_wrapper .dataTables_filter input,
    #datatable-staffs_wrapper .dataTables_length select {
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        background: #ffffff;
    }

    #datatable-staffs_wrapper .dataTables_filter input:focus,
    #datatable-staffs_wrapper .dataTables_length select:focus {
        border-color: #94a3b8;
        outline: none;
        box-shadow: none;
    }

    #datatable-staffs tbody tr {
        transition: background-color 0.15s ease;
    }

    #datatable-staffs tbody tr:hover {
        background: #f8fafc;
    }

    #datatable-staffs_wrapper .dataTables_info,
    #datatable-staffs_wrapper .dataTables_length,
    #datatable-staffs_wrapper .dataTables_filter {
        color: #64748b;
        font-size: 0.78rem;
    }

    #datatable-staffs_wrapper .paginate_button {
        border-radius: 0.5rem !important;
    }
</style>

@endpush

@push('js')

<script>
    var table = null;

    $(document).ready(function () {
        var dtName = 'datatable-staffs';

        // Initialize table search
        createColumnSearch(
            dtName,
            [5],
            [1, 3]
        );

        // Initialize DataTable
        table = $('#' + dtName).DataTable({
            ajax: getAjaxConfig(
                "{{ route('getstaffs') }}",
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
                    targets: [1],
                    className:
                        "text-sm2 text-center " +
                        "font-weight-normal text-truncate " +
                        "mnw-60 mxw-80"
                },
                {
                    targets: [2],
                    className:
                        "text-sm2 font-weight-normal " +
                        "text-truncate mnw-260 mxw-280"
                },
                {
                    targets: [3],
                    className:
                        "text-sm2 text-center " +
                        "font-weight-normal text-truncate " +
                        "mnw-160 mxw-180"
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

            language: getLanguageConfig('Staff'),

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
            $('#h5staffTitle')
                .text($('#staffTitle').val());

            $('input[name="_method"]')
                .val($('#staffMethod').val());

            $('#frmStaff')
                .attr(
                    'action',
                    $('#staffAction').val()
                );

            $("#staff-modal")
                .attr("hidden", false);

            $("#staff-modal")
                .modal("show");
        @endif
    });

    // Refresh tooltips
    $('#datatable-staffs').on(
        'draw.dt',
        function () {
            $("[data-bs-toggle='tooltip']")
                .tooltip();

            $('.tooltip')
                .tooltip('hide');
        }
    );

    // Save staff
    function ocSubmit() {
        $("#btnSave")
            .attr("hidden", true);

        $("#btnSaveDisabled")
            .attr("hidden", false);

        $("#frmStaff")
            .submit();
    }

    // Open staff modal
    function showStaff(myData, myAction) {
        $('#staffTitle')
            .val(
                'Edit : ' +
                myData.abbreviation
            );

        $('#staffMethod')
            .val('put');

        $('#staffAction')
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

        $('#h5staffTitle')
            .text(
                $('#staffTitle').val()
            );

        $('input[name="_method"]')
            .val(
                $('#staffMethod').val()
            );

        $('#frmStaff')
            .attr(
                'action',
                $('#staffAction').val()
            );

        $("#staff-modal")
            .attr("hidden", false);

        $("#staff-modal")
            .modal("show");
    }
</script>

@endpush
