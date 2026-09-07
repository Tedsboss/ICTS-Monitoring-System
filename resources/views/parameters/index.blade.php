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
            @include('layouts.navbars.auth.topnav', ['title' => 'System Parameters'])
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
                            <i class="fa fa-sliders" aria-hidden="true"></i>
                        </div>

                        <div>
                            <h2 class="mb-0 text-base font-bold text-slate-900">
                                System Parameters
                            </h2>

                            <p class="mb-0 mt-1 text-sm text-slate-500">
                                Manage configurable values used throughout DIREK.
                            </p>
                        </div>
                    </div>

                    <div
                        class="inline-flex w-fit items-center gap-2 rounded-full
                               border border-slate-200 bg-slate-50 px-3 py-1.5
                               text-xs font-semibold text-slate-600"
                    >
                        <i class="fa fa-cogs" aria-hidden="true"></i>
                        Configuration
                    </div>
                </div>

                {{-- Table --}}
                <div class="p-4 sm:p-5">
                    <div class="overflow-hidden rounded-xl border border-slate-200">
                        <div class="table-responsive">
                            <table
                                class="table table-hover mb-0"
                                id="datatable-parameters"
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
                                            class="text-uppercase text-secondary text-xxs
                                                   font-weight-bolder opacity-7 p-3"
                                        >
                                            Description
                                        </th>

                                        <th
                                            class="text-uppercase text-center text-secondary
                                                   text-xxs font-weight-bolder opacity-7 p-3"
                                        >
                                            Category
                                        </th>

                                        <th
                                            class="text-uppercase text-secondary text-xxs
                                                   font-weight-bolder opacity-7 p-3"
                                        >
                                            Updated By
                                        </th>

                                        <th
                                            class="text-uppercase text-center text-secondary
                                                   text-xxs font-weight-bolder opacity-7 p-3"
                                        >
                                            Updated Date
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

    {{-- Parameter form --}}
    <form
        method="POST"
        id="frmParameter"
        autocomplete="off"
    >
        @csrf
        @method('post')

        <div
            class="modal fade"
            id="parameter-modal"
            style="display: none;"
            tabindex="-1"
            role="dialog"
            aria-labelledby="h5parameterTitle"
            aria-hidden="true"
            hidden
        >
            <div class="modal-dialog modal-dialog-centered modal-xl">
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
                                <i class="fa fa-sliders" aria-hidden="true"></i>
                            </div>

                            <div>
                                <h5
                                    class="modal-title mb-0 text-base font-bold"
                                    id="h5parameterTitle"
                                >
                                    Parameter
                                </h5>

                                <p class="mb-0 mt-0.5 text-xs text-slate-500">
                                    Update the selected system configuration.
                                </p>
                            </div>
                        </div>

                        <div hidden>
                            <input
                                name="parameterTitle"
                                id="parameterTitle"
                                value="{{ old('parameterTitle') }}"
                            >

                            <input
                                name="parameterAction"
                                id="parameterAction"
                                value="{{ old('parameterAction') }}"
                            >

                            <input
                                name="parameterMethod"
                                id="parameterMethod"
                                value="{{ old('parameterMethod') }}"
                            >

                            <input
                                name="parameterWithDuration"
                                id="parameterWithDuration"
                                value="{{ old('parameterWithDuration') }}"
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
                        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

                            {{-- Name --}}
                            <div class="lg:col-span-2">
                                <label
                                    for="name"
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Name
                                </label>

                                <input
                                    name="name"
                                    id="name"
                                    class="form-control"
                                    type="text"
                                    placeholder="Name"
                                    value="{{ old('name') }}"
                                >

                                @error('name')
                                    <p class="mb-0 mt-1 text-xs text-danger">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Title --}}
                            <div class="lg:col-span-2">
                                <label
                                    for="title"
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Title
                                </label>

                                <input
                                    name="title"
                                    id="title"
                                    class="form-control"
                                    type="text"
                                    placeholder="Title"
                                    value="{{ old('title') }}"
                                >

                                @error('title')
                                    <p class="mb-0 mt-1 text-xs text-danger">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Type --}}
                            <div>
                                <label
                                    for="type"
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Type
                                </label>

                                <select
                                    name="type"
                                    id="type"
                                    placeholder="Type"
                                    autocomplete="off"
                                    class="hide-search"
                                    onchange="loadValue()"
                                    readonly
                                >
                                    <option value="">
                                        Type
                                    </option>

                                    <option
                                        value="html"
                                        @if (old('type') == 'html') selected @endif
                                    >
                                        HTML
                                    </option>

                                    <option
                                        value="string"
                                        @if (old('type') == 'string') selected @endif
                                    >
                                        Text
                                    </option>

                                    <option
                                        value="time"
                                        @if (old('type') == 'time') selected @endif
                                    >
                                        Time
                                    </option>

                                    <option
                                        value="integer"
                                        @if (old('type') == 'integer') selected @endif
                                    >
                                        Integer
                                    </option>

                                    <option
                                        value="boolean"
                                        @if (old('type') == 'boolean') selected @endif
                                    >
                                        Boolean (yes, no)
                                    </option>
                                </select>

                                @error('type')
                                    <p class="mb-0 mt-1 text-xs text-danger">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Category --}}
                            <div>
                                <label
                                    for="category"
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Category
                                </label>

                                <select
                                    name="category"
                                    id="category"
                                    placeholder="Category"
                                    autocomplete="off"
                                    class="hide-search"
                                    onchange="loadValue()"
                                    readonly
                                >
                                    <option value="">
                                        Category
                                    </option>

                                    <option
                                        value="Email Notification"
                                        @if (old('category') == 'Email Notification') selected @endif
                                    >
                                        Email Notification
                                    </option>

                                    <option
                                        value="Pop-Up Notification"
                                        @if (old('category') == 'Pop-Up Notification') selected @endif
                                    >
                                        Pop-Up Notification
                                    </option>

                                    <option
                                        value="Prompt (Error,Warning,Info)"
                                        @if (old('category') == 'Prompt (Error,Warning,Info)') selected @endif
                                    >
                                        Prompt (Error, Warning, Info)
                                    </option>

                                    <option
                                        value="Configuration"
                                        @if (old('category') == 'Configuration') selected @endif
                                    >
                                        Configuration
                                    </option>

                                    <option
                                        value="Others"
                                        @if (old('category') == 'Others') selected @endif
                                    >
                                        Others
                                    </option>
                                </select>

                                @error('category')
                                    <p class="mb-0 mt-1 text-xs text-danger">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Duration --}}
                            <div
                                class="lg:col-span-2"
                                id="divDuration"
                            >
                                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                                    {{-- Start date --}}
                                    <div>
                                        <label
                                            for="start_date"
                                            class="mb-2 block text-sm font-semibold text-slate-700"
                                        >
                                            Start Date
                                        </label>

                                        <input
                                            name="start_date"
                                            id="start_date"
                                            class="form-control"
                                            type="datetime-local"
                                            step="1"
                                            placeholder="Start"
                                            value="{{ old('start_date') }}"
                                        >

                                        @error('start_date')
                                            <p class="mb-0 mt-1 text-xs text-danger">
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>

                                    {{-- End date --}}
                                    <div>
                                        <label
                                            for="end_date"
                                            class="mb-2 block text-sm font-semibold text-slate-700"
                                        >
                                            End Date
                                        </label>

                                        <input
                                            name="end_date"
                                            id="end_date"
                                            class="form-control"
                                            type="datetime-local"
                                            step="1"
                                            placeholder="End"
                                            value="{{ old('end_date') }}"
                                        >

                                        @error('end_date')
                                            <p class="mb-0 mt-1 text-xs text-danger">
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>

                                </div>
                            </div>

                            {{-- Description --}}
                            <div class="lg:col-span-2">
                                <label
                                    for="description"
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Description
                                </label>

                                <input
                                    name="description"
                                    id="description"
                                    class="form-control"
                                    type="text"
                                    placeholder="Description"
                                    value="{{ old('description') }}"
                                >

                                @error('description')
                                    <p class="mb-0 mt-1 text-xs text-danger">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Value --}}
                            <div class="lg:col-span-2">
                                <label
                                    for="value"
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Value
                                </label>

                                <div
                                    id="divQuill"
                                    class="overflow-hidden rounded-xl
                                           border border-slate-200 bg-white"
                                >
                                    <div id="quill_value"></div>
                                </div>

                                <div
                                    id="divValue"
                                    hidden
                                >
                                    <textarea
                                        name="value"
                                        id="value"
                                        rows="5"
                                        class="form-control"
                                        placeholder="Value"
                                    >{{ old('value') }}</textarea>
                                </div>

                                @error('value')
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
    .ql-editor {
        min-height: 180px;
    }

    #datatable-parameters_wrapper .dataTables_filter input,
    #datatable-parameters_wrapper .dataTables_length select {
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        background: #ffffff;
    }

    #datatable-parameters_wrapper .dataTables_filter input:focus,
    #datatable-parameters_wrapper .dataTables_length select:focus {
        border-color: #94a3b8;
        outline: none;
        box-shadow: none;
    }

    #datatable-parameters tbody tr {
        transition: background-color 0.15s ease;
    }

    #datatable-parameters tbody tr:hover {
        background: #f8fafc;
    }

    #datatable-parameters_wrapper .dataTables_info,
    #datatable-parameters_wrapper .dataTables_length,
    #datatable-parameters_wrapper .dataTables_filter {
        color: #64748b;
        font-size: 0.78rem;
    }

    #datatable-parameters_wrapper .paginate_button {
        border-radius: 0.5rem !important;
    }
</style>

@endpush

@push('js')

<script>
    // Initialize fields
    initTomSelect('type');
    initTomSelect('category');
    initQuillJs('quill_value');

    var table = null;
    var newAction = "{{ route('parameters.store') }}";

    $(document).ready(function () {
        var dtName = 'datatable-parameters';

        // Initialize table search
        createColumnSearch(
            dtName,
            [5],
            [2, 4]
        );

        // Initialize DataTable
        table = $('#' + dtName).DataTable({
            ajax: getAjaxConfig(
                "{{ route('getparameters') }}",
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
                    data: 'category'
                },
                {
                    data: 'editor'
                },
                {
                    data: 'updated_at'
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
                        "text-truncate mnw-100 mxw-120"
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
                        "text-sm2 text-center " +
                        "font-weight-normal text-truncate " +
                        "mnw-100 mxw-120"
                },
                {
                    targets: [3],
                    className:
                        "text-sm2 font-weight-normal " +
                        "text-truncate mnw-100 mxw-120"
                },
                {
                    targets: [4],
                    className:
                        "text-sm2 text-center " +
                        "font-weight-normal text-truncate " +
                        "mnw-80 mxw-100"
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
                [0, 'desc']
            ],

            pagingType: "full_numbers",

            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],

            responsive: false,

            language: getLanguageConfig('Parameters'),

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
            $('#h5parameterTitle')
                .text($('#parameterTitle').val());

            $('input[name="_method"]')
                .val($('#parameterMethod').val());

            $('#frmParameter')
                .attr(
                    'action',
                    $('#parameterAction').val()
                );

            loadValue();

            $("#parameter-modal")
                .attr("hidden", false);

            $("#parameter-modal")
                .modal("show");
        @endif
    });

    // Refresh tooltips
    $('#datatable-parameters').on(
        'draw.dt',
        function () {
            refreshToolTip();
        }
    );

    // Save parameter
    function ocSubmit() {
        if ($('#type').val() === 'html') {
            $('#value').val(
                quills['quill_value'].root.innerHTML
            );
        }

        $("#btnSave").attr("hidden", true);
        $("#btnSaveDisabled").attr("hidden", false);

        $("#frmParameter").submit();
    }

    // Open parameter modal
    function showParameter(myData, myAction) {
        $('#parameterTitle').val(
            'Edit Parameter : ' + myData.name
        );

        $('#parameterMethod').val('put');
        $('#parameterAction').val(myAction);

        $('#name').val(myData.name);

        $('#parameterWithDuration').val(
            myData.with_duration
        );

        $('#start_date').val(
            myData.start_date
        );

        $('#end_date').val(
            myData.end_date
        );

        tomSelects['type'].setValue(
            myData.type
        );

        tomSelects['category'].setValue(
            myData.category
        );

        $('#title').val(
            myData.title
        );

        $('#description').val(
            myData.description
        );

        $('#value').val(
            myData.value
        );

        loadValue();

        $('#h5parameterTitle').text(
            $('#parameterTitle').val()
        );

        $('input[name="_method"]').val(
            $('#parameterMethod').val()
        );

        $('#frmParameter').attr(
            'action',
            $('#parameterAction').val()
        );

        $("#parameter-modal")
            .attr("hidden", false);

        $("#parameter-modal")
            .modal("show");
    }

    // Update value field
    function loadValue() {
        if (
            $('#parameterWithDuration').val() === 'Y'
        ) {
            $("#divDuration").attr(
                "hidden",
                false
            );
        } else {
            $("#divDuration").attr(
                "hidden",
                true
            );
        }

        if ($('#type').val() === 'html') {
            $("#divValue").attr(
                "hidden",
                true
            );

            $("#divQuill").attr(
                "hidden",
                false
            );

            if (
                $('#value').val() === null ||
                $('#value').val() === ''
            ) {
                quills['quill_value']
                    .setContents([]);
            } else {
                quills['quill_value']
                    .root
                    .innerHTML = $('#value').val();
            }
        } else {
            $("#divValue").attr(
                "hidden",
                false
            );

            $("#divQuill").attr(
                "hidden",
                true
            );
        }
    }
</script>

@endpush
