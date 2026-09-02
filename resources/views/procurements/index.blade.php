@extends('layouts.app')

@section('content')

<nav
    class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
    id="navbarBlur"
    data-scroll="false"
>
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Procurements based on WFP'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>

<div class="px-4 pb-8 pt-4">

    {{-- Page Summary --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

            {{-- Actions --}}
            <div class="flex flex-wrap items-center gap-3">

                @can('create', App\Models\Procurement::class)

                    <a
                        href="{{ route('procurements.create') }}"
                        class="inline-flex items-center gap-2 rounded-lg
                               bg-sky-600 px-4 py-2.5 text-sm font-semibold
                               text-white shadow-sm transition hover:bg-sky-700"
                    >
                        <i class="fa fa-plus"></i>
                        <span>Add Procurement</span>
                    </a>

                @endcan

            </div>


            {{-- Summary --}}
            <div
                class="grid grid-cols-2 gap-x-8 gap-y-4
                       sm:grid-cols-4 lg:flex lg:items-center"
            >

                <div class="text-center lg:min-w-[90px]">

                    <div
                        id="totalRecords"
                        class="text-xl font-bold text-sky-600"
                    >
                        —
                    </div>

                    <div
                        class="mt-1 text-xs font-medium uppercase
                               tracking-wide text-slate-500"
                    >
                        Entries
                    </div>

                </div>


                <div class="hidden h-10 w-px bg-slate-200 lg:block"></div>


                <div class="text-center lg:min-w-[130px]">

                    <div
                        id="sumAmount"
                        class="text-lg font-bold text-slate-900"
                    >
                        —
                    </div>

                    <div
                        class="mt-1 text-xs font-medium uppercase
                               tracking-wide text-slate-500"
                    >
                        Amount
                    </div>

                </div>


                <div class="text-center lg:min-w-[100px]">

                    <div
                        id="countProcured"
                        class="text-lg font-bold text-emerald-600"
                    >
                        —
                    </div>

                    <div
                        class="mt-1 text-xs font-medium uppercase
                               tracking-wide text-slate-500"
                    >
                        Procured
                    </div>

                </div>


                <div class="text-center lg:min-w-[100px]">

                    <div
                        id="countPaid"
                        class="text-lg font-bold text-emerald-600"
                    >
                        —
                    </div>

                    <div
                        class="mt-1 text-xs font-medium uppercase
                               tracking-wide text-slate-500"
                    >
                        Paid
                    </div>

                </div>

            </div>

        </div>

    </section>


    {{-- Procurement Table --}}
    <section
        class="mt-6 overflow-hidden rounded-2xl border
               border-slate-200 bg-white shadow-sm"
    >

        {{-- Header --}}
        <div
            class="border-b border-slate-200 px-5 py-4
                   lg:flex lg:items-center lg:justify-between"
        >

            <div>

                <h1 class="text-lg font-bold text-slate-900">
                    Procurement Entries
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Procurement, payment and retention tracking per WFP item.
                </p>

            </div>


            <div
                class="mt-3 inline-flex items-center rounded-full
                       bg-sky-50 px-3 py-1 text-xs font-semibold
                       text-sky-700 lg:mt-0"
            >
                Based on Work and Financial Plan
            </div>

        </div>


        {{-- Filters --}}
        <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-4">

            <div class="flex flex-wrap items-end gap-4">

                {{-- Funding Source --}}
                <div class="w-full sm:w-auto sm:min-w-[210px]">

                    <label
                        for="filterFundingSource"
                        class="mb-1.5 block text-xs font-semibold
                               uppercase tracking-wide text-slate-600"
                    >
                        Funding Source
                    </label>

                    <select
                        id="filterFundingSource"
                        class="block w-full rounded-lg border border-slate-300
                               bg-white px-3 py-2 text-sm text-slate-700
                               shadow-sm outline-none transition
                               focus:border-sky-500 focus:ring-2
                               focus:ring-sky-100"
                    >
                        <option value="">All Funding Sources</option>

                        @foreach ($fundingSources as $fs)
                            <option value="{{ $fs }}">
                                {{ $fs }}
                            </option>
                        @endforeach

                    </select>

                </div>


                {{-- Expense Class --}}
                <div class="w-full sm:w-auto sm:min-w-[190px]">

                    <label
                        for="filterExpenseClass"
                        class="mb-1.5 block text-xs font-semibold
                               uppercase tracking-wide text-slate-600"
                    >
                        Expense Class
                    </label>

                    <select
                        id="filterExpenseClass"
                        class="block w-full rounded-lg border border-slate-300
                               bg-white px-3 py-2 text-sm text-slate-700
                               shadow-sm outline-none transition
                               focus:border-sky-500 focus:ring-2
                               focus:ring-sky-100"
                    >
                        <option value="">All Classes</option>

                        @foreach ($expenseClasses as $ec)
                            <option value="{{ $ec }}">
                                {{ $ec }}
                            </option>
                        @endforeach

                    </select>

                </div>


                {{-- Search --}}
                <div class="w-full sm:w-auto sm:min-w-[240px]">

                    <label
                        for="filterSearch"
                        class="mb-1.5 block text-xs font-semibold
                               uppercase tracking-wide text-slate-600"
                    >
                        Search
                    </label>

                    <input
                        type="text"
                        id="filterSearch"
                        placeholder="Procurement title..."
                        class="block w-full rounded-lg border border-slate-300
                               bg-white px-3 py-2 text-sm text-slate-700
                               shadow-sm outline-none transition
                               placeholder:text-slate-400
                               focus:border-sky-500 focus:ring-2
                               focus:ring-sky-100"
                    >

                </div>


                {{-- Filter --}}
                <button
                    type="button"
                    id="btnLoad"
                    class="inline-flex h-[38px] items-center justify-center
                           gap-2 rounded-lg bg-slate-800 px-4 text-sm
                           font-semibold text-white shadow-sm transition
                           hover:bg-slate-900"
                    data-bs-toggle="tooltip"
                    title="Apply Filter"
                >
                    <i class="fa fa-filter"></i>
                    <span>Filter</span>
                </button>


                {{-- Export --}}
                <button
                    type="button"
                    id="exportBtn"
                    class="inline-flex h-[38px] items-center justify-center
                           gap-2 rounded-lg border border-emerald-200
                           bg-emerald-50 px-4 text-sm font-semibold
                           text-emerald-700 transition hover:bg-emerald-100"
                    data-bs-toggle="tooltip"
                    title="Export Excel"
                >
                    <i class="fa fa-download"></i>
                    <span>Export Excel</span>
                </button>

            </div>

        </div>


        {{-- DataTable --}}
        <div class="p-5">

            <div class="overflow-x-auto">

                <table
                    id="procurementTable"
                    class="w-full"
                >

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Funding Source</th>
                            <th>Procurement Title</th>
                            <th>Expense Class</th>
                            <th>Division</th>
                            <th>Amount</th>
                            <th>Quarter</th>
                            <th>Procurement</th>
                            <th>Payment</th>
                            <th>Retention</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody></tbody>

                </table>

            </div>

        </div>

    </section>


    <div class="mt-6">
        @include('layouts.footers.auth.footer')
    </div>

</div>

@endsection


@push('css')

<style>
    #procurementTable {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        width: 100% !important;
        font-size: 0.82rem;
    }

    #procurementTable thead th {
        background: #f8fafc !important;
        color: #64748b !important;
        font-size: 0.70rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        border-top: 1px solid #e2e8f0 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 0.8rem 0.7rem !important;
        white-space: nowrap;
        vertical-align: middle;
    }

    #procurementTable tbody td {
        color: #475569;
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 0.8rem 0.7rem !important;
        vertical-align: middle;
        background: #ffffff;
    }

    #procurementTable tbody tr:hover td {
        background: #f8fafc;
    }

    #procurementTable th,
    #procurementTable td {
        text-align: center !important;
    }

    #procurementTable th.text-start,
    #procurementTable td.text-start {
        text-align: left !important;
    }

    #procurementTable td:nth-child(6) {
        text-align: right !important;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }

    #procurementTable_wrapper .dataTables_length,
    #procurementTable_wrapper .dataTables_filter {
        margin-bottom: 1rem;
        color: #64748b;
        font-size: 0.8rem;
    }

    #procurementTable_wrapper .dataTables_filter input,
    #procurementTable_wrapper .dataTables_length select {
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        background: #ffffff;
        padding: 0.4rem 0.6rem;
        color: #475569;
        outline: none;
    }

    #procurementTable_wrapper .dataTables_filter input:focus,
    #procurementTable_wrapper .dataTables_length select:focus {
        border-color: #0ea5e9;
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
    }

    #procurementTable_wrapper .dataTables_info {
        color: #64748b;
        font-size: 0.8rem;
        padding-top: 1rem;
    }

    #procurementTable_wrapper .dataTables_paginate {
        padding-top: 0.75rem;
    }

    #procurementTable_wrapper .dataTables_paginate .paginate_button {
        border: 0 !important;
        border-radius: 0.5rem !important;
        background: transparent !important;
        color: #475569 !important;
        margin: 0 2px;
    }

    #procurementTable_wrapper .dataTables_paginate .paginate_button.current {
        background: #0284c7 !important;
        color: #ffffff !important;
    }

    #procurementTable_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f1f5f9 !important;
        color: #0f172a !important;
    }

    .procurement-status-ok {
        display: inline-flex;
        min-width: 52px;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        background: #d1fae5;
        padding: 4px 10px;
        color: #047857;
        font-size: 11px;
        font-weight: 700;
    }

    .procurement-status-empty {
        color: #94a3b8;
    }

    .procurement-action {
        display: inline-flex;
        width: 32px;
        height: 32px;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        text-decoration: none !important;
        transition: all 0.15s ease;
    }

    .procurement-action-view {
        background: #f0f9ff;
        color: #0284c7 !important;
    }

    .procurement-action-view:hover {
        background: #e0f2fe;
    }

    .procurement-action-edit {
        background: #fffbeb;
        color: #d97706 !important;
    }

    .procurement-action-edit:hover {
        background: #fef3c7;
    }
</style>

@endpush


@push('js')

<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.4.0/exceljs.min.js"></script>

<script>
$(document).ready(function () {

    const REPORT_LABEL = 'Procurements based on WFP';

    let table = null;
    let lastData = [];


    function money(value) {

        return Number(value ?? 0).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

    }


    function statusBadge(status) {

        return status === 'OK'
            ? '<span class="procurement-status-ok">OK</span>'
            : '<span class="procurement-status-empty">—</span>';

    }


    function updateSummary(json) {

        $('#totalRecords').text(
            json.length.toLocaleString()
        );


        $('#sumAmount').text(
            money(
                json.reduce(
                    (sum, row) => sum + Number(row.amount || 0),
                    0
                )
            )
        );


        $('#countProcured').text(
            json
                .filter(row => row.procurement_status === 'OK')
                .length
                .toLocaleString()
        );


        $('#countPaid').text(
            json
                .filter(row => row.payment_status === 'OK')
                .length
                .toLocaleString()
        );

    }


    function loadTable() {

        const fundingSource =
            $('#filterFundingSource').val();

        const expenseClass =
            $('#filterExpenseClass').val();

        const search =
            $('#filterSearch').val();


        if (table) {

            table.destroy();

            $('#procurementTable tbody').empty();

            table = null;

        }


        lastData = [];


        $('#totalRecords, #sumAmount, #countProcured, #countPaid')
            .text('...');


        table = $('#procurementTable').DataTable({

            processing: true,

            ajax: {

                url: '{{ route("procurements.data") }}',

                data: function (data) {

                    data.funding_source =
                        fundingSource;

                    data.expense_class =
                        expenseClass;

                },


                dataSrc: function (json) {

                    if (search) {

                        const normalizedSearch =
                            search.toLowerCase();

                        json = json.filter(function (row) {

                            return (
                                row.procurement_title || ''
                            )
                                .toLowerCase()
                                .includes(normalizedSearch);

                        });

                    }


                    lastData = json;

                    updateSummary(json);

                    return json;

                }

            },


            columns: [

                {
                    data: null,

                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    },

                    orderable: false
                },


                {
                    data: 'funding_source',
                    defaultContent: '—'
                },


                {
                    data: 'procurement_title',
                    defaultContent: '—',
                    className: 'text-start',

                    render: function (data) {

                        if (!data) {
                            return '—';
                        }

                        return `
                            <div
                                style="
                                    white-space:normal;
                                    min-width:220px;
                                "
                            >
                                ${data}
                            </div>
                        `;

                    }
                },


                {
                    data: 'expense_class',
                    defaultContent: '—'
                },


                {
                    data: 'division_assigned',
                    defaultContent: '—'
                },


                {
                    data: 'amount',
                    render: money
                },


                {
                    data: 'quarter',
                    defaultContent: '—'
                },


                {
                    data: 'procurement_status',
                    render: statusBadge
                },


                {
                    data: 'payment_status',
                    render: statusBadge
                },


                {
                    data: 'retention_status',
                    render: statusBadge
                },


                {
                    data: null,
                    orderable: false,
                    searchable: false,

                    render: function (row) {

                        const base =
                            `{{ url('administrator/procurements') }}`;

                        return `
                            <div
                                style="
                                    display:flex;
                                    align-items:center;
                                    justify-content:center;
                                    gap:6px;
                                "
                            >
                                <a
                                    href="${base}/${row.id}"
                                    class="procurement-action procurement-action-view"
                                    title="View"
                                >
                                    <i class="fa fa-eye"></i>
                                </a>

                                <a
                                    href="${base}/${row.id}/edit"
                                    class="procurement-action procurement-action-edit"
                                    title="Edit"
                                >
                                    <i class="fa fa-pencil"></i>
                                </a>
                            </div>
                        `;

                    }
                }

            ],


            pageLength: 15,


            columnDefs: [

                {
                    targets: '_all',
                    className: 'text-center'
                },

                {
                    targets: [2],
                    className: 'text-start'
                }

            ]

        });

    }


    // Export Excel
    $('#exportBtn').on('click', async function () {

        const exportData = table
            ? table.rows({ search: 'applied' }).data().toArray()
            : lastData;


        if (!exportData.length) {

            alert('No data to export. Load the table first.');

            return;

        }


        const $btn = $(this);

        const originalButtonHtml =
            $btn.html();


        $btn
            .prop('disabled', true)
            .html(
                '<i class="fa fa-spinner fa-spin"></i>' +
                '<span> Exporting...</span>'
            );


        try {

            const fundingSource =
                $('#filterFundingSource').val() || 'all';

            const expenseClass =
                $('#filterExpenseClass').val() || 'all';


            const COLS = [

                {
                    key: 'funding_source',
                    label: 'Funding Source',
                    width: 18
                },

                {
                    key: 'procurement_title',
                    label: 'Procurement Title',
                    width: 45
                },

                {
                    key: 'expense_class',
                    label: 'Expense Class',
                    width: 14
                },

                {
                    key: 'division_assigned',
                    label: 'Division',
                    width: 14
                },

                {
                    key: 'amount',
                    label: 'Amount',
                    width: 16
                },

                {
                    key: 'quarter',
                    label: 'Quarter',
                    width: 10
                },

                {
                    key: 'procurement_status',
                    label: 'Procurement',
                    width: 14
                },

                {
                    key: 'payment_status',
                    label: 'Payment',
                    width: 14
                },

                {
                    key: 'retention_status',
                    label: 'Retention',
                    width: 14
                }

            ];


            const HEADER_BG =
                'FF1A3C5E';

            const HEADER_FONT =
                'FFFFFFFF';

            const STATUS_ARGB =
                'FF2DCE89';


            const headerStyle = function (cell) {

                cell.font = {
                    bold: true,
                    color: {
                        argb: HEADER_FONT
                    },
                    size: 10,
                    name: 'Calibri'
                };


                cell.fill = {
                    type: 'pattern',
                    pattern: 'solid',
                    fgColor: {
                        argb: HEADER_BG
                    }
                };


                cell.alignment = {
                    horizontal: 'center',
                    vertical: 'middle',
                    wrapText: true
                };


                cell.border = {

                    top: {
                        style: 'thin',
                        color: {
                            argb: 'FF2A5A8A'
                        }
                    },

                    left: {
                        style: 'thin',
                        color: {
                            argb: 'FF2A5A8A'
                        }
                    },

                    bottom: {
                        style: 'thin',
                        color: {
                            argb: 'FF2A5A8A'
                        }
                    },

                    right: {
                        style: 'thin',
                        color: {
                            argb: 'FF2A5A8A'
                        }
                    }

                };

            };


            const dataStyle = function (cell, key) {

                const isStatus = [
                    'procurement_status',
                    'payment_status',
                    'retention_status'
                ].includes(key);


                const isMoney =
                    key === 'amount';


                if (
                    isMoney &&
                    typeof cell.value === 'number'
                ) {
                    cell.numFmt = '#,##0.00';
                }


                cell.font = {

                    size: 9,

                    name: 'Calibri',

                    bold:
                        isStatus &&
                        cell.value === 'OK',

                    color:
                        isStatus &&
                        cell.value === 'OK'
                            ? {
                                argb: 'FFFFFFFF'
                            }
                            : {
                                argb: 'FF333333'
                            }

                };


                cell.fill = {

                    type: 'pattern',

                    pattern: 'solid',

                    fgColor: {
                        argb:
                            isStatus &&
                            cell.value === 'OK'
                                ? STATUS_ARGB
                                : 'FFFFFFFF'
                    }

                };


                cell.alignment = {

                    horizontal:
                        isMoney
                            ? 'right'
                            : 'left',

                    vertical: 'middle',

                    wrapText: true

                };


                cell.border = {

                    top: {
                        style: 'thin',
                        color: {
                            argb: 'FFD0D0D0'
                        }
                    },

                    left: {
                        style: 'thin',
                        color: {
                            argb: 'FFD0D0D0'
                        }
                    },

                    bottom: {
                        style: 'thin',
                        color: {
                            argb: 'FFD0D0D0'
                        }
                    },

                    right: {
                        style: 'thin',
                        color: {
                            argb: 'FFD0D0D0'
                        }
                    }

                };

            };


            const workbook =
                new ExcelJS.Workbook();


            workbook.creator =
                'PPMS – ERPMES';

            workbook.created =
                new Date();


            const ws =
                workbook.addWorksheet(
                    REPORT_LABEL.substring(0, 31),
                    {
                        pageSetup: {
                            paperSize: 9,
                            orientation: 'landscape',
                            fitToPage: true
                        }
                    }
                );


            const colCount =
                COLS.length;


            // Main title
            ws.mergeCells(
                1,
                1,
                1,
                colCount
            );


            Object.assign(
                ws.getCell(1, 1),
                {

                    value:
                        'PPMS – ERPMES',

                    font: {
                        bold: true,
                        size: 13,
                        name: 'Calibri',
                        color: {
                            argb: HEADER_FONT
                        }
                    },

                    fill: {
                        type: 'pattern',
                        pattern: 'solid',
                        fgColor: {
                            argb: HEADER_BG
                        }
                    },

                    alignment: {
                        horizontal: 'center',
                        vertical: 'middle'
                    }

                }
            );


            ws.getRow(1).height =
                22;


            // Report title
            ws.mergeCells(
                2,
                1,
                2,
                colCount
            );


            Object.assign(
                ws.getCell(2, 1),
                {

                    value:
                        REPORT_LABEL,

                    font: {
                        bold: true,
                        size: 11,
                        name: 'Calibri',
                        color: {
                            argb: HEADER_FONT
                        }
                    },

                    fill: {
                        type: 'pattern',
                        pattern: 'solid',
                        fgColor: {
                            argb: HEADER_BG
                        }
                    },

                    alignment: {
                        horizontal: 'center',
                        vertical: 'middle'
                    }

                }
            );


            ws.getRow(2).height =
                18;


            // Filters / Generated date
            ws.mergeCells(
                3,
                1,
                3,
                colCount
            );


            Object.assign(
                ws.getCell(3, 1),
                {

                    value:
                        `Funding Source: ${
                            fundingSource === 'all'
                                ? 'All'
                                : fundingSource
                        } · Expense Class: ${
                            expenseClass === 'all'
                                ? 'All'
                                : expenseClass
                        } · Generated: ${
                            new Date().toLocaleString('en-PH')
                        }`,

                    font: {
                        italic: true,
                        size: 9,
                        name: 'Calibri',
                        color: {
                            argb: 'FF555555'
                        }
                    },

                    fill: {
                        type: 'pattern',
                        pattern: 'solid',
                        fgColor: {
                            argb: 'FFF0F4FF'
                        }
                    },

                    alignment: {
                        horizontal: 'center',
                        vertical: 'middle'
                    }

                }
            );


            ws.getRow(3).height =
                14;


            // Headers
            const headerRow =
                ws.getRow(4);


            headerRow.height =
                30;


            COLS.forEach(function (column, index) {

                const cell =
                    headerRow.getCell(index + 1);

                cell.value =
                    column.label;

                headerStyle(cell);

            });


            // Data
            exportData.forEach(function (row, rowIndex) {

                const excelRow =
                    ws.getRow(5 + rowIndex);


                excelRow.height =
                    20;


                COLS.forEach(function (column, columnIndex) {

                    const cell =
                        excelRow.getCell(columnIndex + 1);


                    cell.value =
                        row[column.key]
                        ?? (
                            column.key === 'amount'
                                ? 0
                                : '—'
                        );


                    dataStyle(
                        cell,
                        column.key
                    );

                });

            });


            // Widths
            COLS.forEach(function (column, index) {

                ws.getColumn(index + 1).width =
                    column.width;

            });


            // Freeze title/header rows
            ws.views = [
                {
                    state: 'frozen',
                    ySplit: 4
                }
            ];


            const buffer =
                await workbook.xlsx.writeBuffer();


            const blob =
                new Blob(
                    [buffer],
                    {
                        type:
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    }
                );


            const url =
                URL.createObjectURL(blob);


            const anchor =
                document.createElement('a');


            anchor.href =
                url;


            anchor.download =
                `procurements_${fundingSource}_${expenseClass}.xlsx`;


            document.body.appendChild(anchor);

            anchor.click();

            document.body.removeChild(anchor);

            URL.revokeObjectURL(url);

        } catch (err) {

            console.error(
                '[Procurement Export]',
                err
            );


            alert(
                'Export failed:\n' +
                err.message
            );

        } finally {

            $btn
                .prop('disabled', false)
                .html(originalButtonHtml);

        }

    });


    // Apply filters
    $('#btnLoad').on(
        'click',
        loadTable
    );


    // Search with Enter key
    $('#filterSearch').on(
        'keydown',
        function (event) {

            if (event.key === 'Enter') {

                event.preventDefault();

                loadTable();

            }

        }
    );


    // Initial table load
    loadTable();


    if (
        typeof $.fn.tooltip === 'function'
    ) {
        $('[data-bs-toggle="tooltip"]').tooltip();
    }

});
</script>

@endpush
