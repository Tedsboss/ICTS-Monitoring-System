@php
    $class_theme = session('user_settings.class_theme', '');

    $canshowhistory = auth()
        ->user()
        ->can('showhistory', App\Models\SystemLog::class);
@endphp

@extends('layouts.app')

@section('content')

    {{-- ========================================================= --}}
    {{-- TOP NAVIGATION --}}
    {{-- ========================================================= --}}

    <nav
        class="navbar navbar-main navbar-expand-lg
               px-0 mx-4 shadow-none border-radius-xl
               z-index-sticky"
        id="navbarBlur"
        data-scroll="false"
    >
        <div class="container-fluid py-1 px-3">

            @include(
                'layouts.navbars.auth.topnav',
                ['title' => 'System Logs']
            )

            @include(
                'layouts.navbars.auth.topnav-withdatetime'
            )

        </div>
    </nav>

    {{-- ========================================================= --}}
    {{-- MAIN CONTENT --}}
    {{-- ========================================================= --}}

    <div class="container-fluid">

        <div class="mt-4">

            <div
                class="overflow-hidden rounded-2xl
                       border border-slate-200 bg-white
                       shadow-sm"
            >

                {{-- Header --}}
                <div
                    class="flex flex-col gap-3
                           border-b border-slate-200
                           px-5 py-5
                           sm:flex-row
                           sm:items-center
                           sm:justify-between"
                >
                    <div>
                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-10 w-10
                                       items-center justify-center
                                       rounded-xl bg-slate-100
                                       text-slate-600"
                            >
                                <i
                                    class="fa fa-list-alt"
                                    aria-hidden="true"
                                ></i>
                            </div>

                            <div>
                                <h2
                                    class="mb-0 text-base
                                           font-bold text-slate-900"
                                >
                                    System Logs
                                </h2>

                                <p
                                    class="mb-0 mt-1
                                           text-sm text-slate-500"
                                >
                                    Review user activities and
                                    system transactions recorded
                                    by DIREK.
                                </p>
                            </div>

                        </div>
                    </div>

                    <div
                        class="inline-flex w-fit
                               items-center gap-2
                               rounded-full
                               border border-slate-200
                               bg-slate-50
                               px-3 py-1.5
                               text-xs font-semibold
                               text-slate-600"
                    >
                        <i
                            class="fa fa-history"
                            aria-hidden="true"
                        ></i>

                        Activity History
                    </div>
                </div>

                {{-- Table --}}
                <div class="p-4 sm:p-5">

                    <div
                        class="overflow-hidden
                               rounded-xl
                               border border-slate-200"
                    >
                        <div class="table-responsive">

                            <table
                                class="table table-hover mb-0"
                                id="datatable-logs"
                                cellspacing="0"
                                width="100%"
                                style="width: 100%;"
                            >
                                <thead>
                                    <tr>

                                        <th
                                            class="text-uppercase
                                                   text-secondary
                                                   text-xxs
                                                   font-weight-bolder
                                                   opacity-7 p-3"
                                        >
                                            Username
                                        </th>

                                        <th
                                            class="text-uppercase
                                                   text-secondary
                                                   text-xxs
                                                   font-weight-bolder
                                                   opacity-7 p-3"
                                        >
                                            Activity
                                        </th>

                                        <th
                                            class="text-uppercase
                                                   text-secondary
                                                   text-xxs
                                                   font-weight-bolder
                                                   opacity-7 p-3
                                                   text-center"
                                        >
                                            IP Address
                                        </th>

                                        <th
                                            class="text-uppercase
                                                   text-secondary
                                                   text-xxs
                                                   font-weight-bolder
                                                   opacity-7 p-3
                                                   text-center"
                                        >
                                            Date
                                        </th>

                                        @if ($canshowhistory)
                                            <th
                                                class="text-uppercase
                                                       text-center
                                                       text-secondary
                                                       text-xxs
                                                       font-weight-bolder
                                                       opacity-7 p-3"
                                            >
                                                Action
                                            </th>
                                        @endif

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

    {{-- ========================================================= --}}
    {{-- HISTORY MODAL --}}
    {{-- ========================================================= --}}

    <div
        class="modal fade"
        id="historyModal"
        style="display: none;"
        tabindex="-1"
        role="dialog"
        aria-labelledby="historyModalLabel"
        aria-hidden="true"
        hidden
    >
        <div
            class="modal-dialog
                   modal-dialog-centered
                   modal-xl"
        >
            <div
                class="modal-content
                       {{ isset($class_theme)
                            && $class_theme === 'dark'
                                ? 'bg-default'
                                : '' }}"
            >

                {{-- Modal Header --}}
                <div
                    class="modal-header
                           border-bottom border-slate-200
                           px-4 py-3"
                >
                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-9 w-9
                                   items-center justify-center
                                   rounded-lg bg-slate-100
                                   text-slate-600"
                        >
                            <i
                                class="fa fa-history"
                                aria-hidden="true"
                            ></i>
                        </div>

                        <div>
                            <h5
                                class="modal-title mb-0
                                       text-base font-bold"
                                id="historyModalLabel"
                            >
                                Change Details
                            </h5>

                            <p
                                class="mb-0 mt-0.5
                                       text-xs text-slate-500"
                            >
                                Recorded details for the selected
                                system activity.
                            </p>
                        </div>

                    </div>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body p-4">

                    <div
                        class="row"
                        id="divHistories"
                        hidden
                    >
                        <div class="col-12">

                            <div
                                class="overflow-hidden
                                       rounded-xl
                                       border border-slate-200"
                            >
                                <div
                                    class="border-b
                                           border-slate-200
                                           bg-slate-50
                                           px-4 py-3"
                                >
                                    <span
                                        class="text-xs font-bold
                                               uppercase
                                               tracking-wide
                                               text-slate-500"
                                    >
                                        History Data
                                    </span>
                                </div>

                                <div
                                    id="json-container"
                                    class="m-0 overflow-auto
                                           bg-slate-950
                                           p-4 text-sm
                                           text-slate-100"
                                    style="
                                        min-height: 120px;
                                        max-height: 480px;
                                        font-family:
                                            Consolas,
                                            Monaco,
                                            'Courier New',
                                            monospace;
                                        white-space: pre;
                                    "
                                ></div>

                            </div>

                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

@endsection


@push('js')

<script>
    var table = null;

    var canshowhistory =
        @if ($canshowhistory)
            true
        @else
            false
        @endif;

    $(document).ready(function () {
        var dtName = 'datatable-logs';

        createColumnSearch(
            dtName,
            [4],
            [2, 3]
        );

        table = $('#' + dtName).DataTable({
            ajax: getAjaxConfig(
                "{{ route('getsystemlogs') }}",
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
                    data: 'activity'
                },
                {
                    data: 'ipaddress'
                },
                {
                    data: 'created_at'
                },

                @if ($canshowhistory)
                    {
                        data: 'actions'
                    },
                @endif
            ],

            columnDefs: [
                {
                    targets: [0],
                    className:
                        "text-sm2 font-weight-normal " +
                        "text-truncate mnw-80 mxw-100"
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
                        "mnw-60 mxw-80"
                },
                {
                    targets: [3],
                    className:
                        "text-sm2 text-center " +
                        "font-weight-normal text-truncate " +
                        "mnw-80 mxw-100"
                },

                @if ($canshowhistory)
                    {
                        targets: [4],
                        className:
                            "text-sm2 text-center " +
                            "font-weight-normal " +
                            "text-truncate mnw-40 mxw-60",
                        orderable: false,
                        searchable: false
                    },
                @endif
            ],

            order: [
                [3, 'desc']
            ],

            pagingType: "full_numbers",

            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],

            responsive: false,

            language: getLanguageConfig('Logs'),

            initComplete: function (settings, json) {
                setupInitComplete(
                    table,
                    dtName,
                    3,
                    'desc'
                );
            }
        });

        setupKeyUpColumnSearch(
            table,
            dtName
        );
    });


    $('#datatable-logs').on('draw.dt', function () {
        refreshToolTip();
    });


    // Show change history.
    function showHistory(mySystemLogId) {
        $('#loader').fadeIn('slow');

        setTimeout(function () {

            $("#json-container").empty();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN':
                        $('meta[name="csrf-token"]')
                            .attr('content')
                }
            });

            $.ajax({
                data: {
                    systemlog_id: mySystemLogId
                },

                url: "{{ route('gethistory') }}",

                type: "POST",

                dataType: 'json',

                success: function (data) {
                    $("#divHistories")
                        .attr("hidden", false);

                    var jsonObj =
                        JSON.parse(data.data);

                    var prettyJson =
                        JSON.stringify(
                            jsonObj,
                            null,
                            2
                        );

                    $('#json-container')
                        .text(prettyJson);

                    $('#loader')
                        .fadeOut('slow');
                },

                error: function (xhr) {
                    $("#divHistories")
                        .attr("hidden", true);

                    var message =
                        xhr.responseJSON &&
                        xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : 'Unable to load history.';

                    showToast(
                        "warning",
                        message
                    );

                    $('#loader')
                        .fadeOut('slow');
                }
            });

        }, 500);

        $("#historyModal")
            .attr("hidden", false);

        $("#historyModal")
            .modal("show");
    }
</script>

@endpush
