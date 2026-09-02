@extends('layouts.app')

@section('content')

<nav
    class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
    id="navbarBlur"
    data-scroll="false"
>
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', [
            'title' => 'SAEB – Statement of Allotment, Expenditures and Balances'
        ])

        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>


<div class="px-4 pb-8 pt-4">

    {{-- Page Header / Summary --}}
    <section
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
    >
        <div
            class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"
        >

            {{-- Actions --}}
            <div class="flex flex-wrap items-center gap-3">

                @can('create', App\Models\Saeb::class)

                    <a
                        href="{{ route('saebs.create') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-sky-600
                               px-4 py-2.5 text-sm font-semibold text-white
                               shadow-sm transition hover:bg-sky-700"
                    >
                        <i class="fa fa-plus"></i>

                        <span>Add Entry</span>
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


                <div class="text-center lg:min-w-[120px]">
                    <div
                        id="sumAllotment"
                        class="text-lg font-bold text-slate-900"
                    >
                        —
                    </div>

                    <div
                        class="mt-1 text-xs font-medium uppercase
                               tracking-wide text-slate-500"
                    >
                        Allotment
                    </div>
                </div>


                <div class="text-center lg:min-w-[120px]">
                    <div
                        id="sumObligated"
                        class="text-lg font-bold text-amber-600"
                    >
                        —
                    </div>

                    <div
                        class="mt-1 text-xs font-medium uppercase
                               tracking-wide text-slate-500"
                    >
                        Obligated
                    </div>
                </div>


                <div class="text-center lg:min-w-[120px]">
                    <div
                        id="sumBalances"
                        class="text-lg font-bold text-emerald-600"
                    >
                        —
                    </div>

                    <div
                        class="mt-1 text-xs font-medium uppercase
                               tracking-wide text-slate-500"
                    >
                        Balances
                    </div>
                </div>

            </div>

        </div>
    </section>


    {{-- SAEB Table Card --}}
    <section
        class="mt-6 overflow-hidden rounded-2xl border
               border-slate-200 bg-white shadow-sm"
    >

        {{-- Card Header --}}
        <div
            class="border-b border-slate-200 px-5 py-4
                   lg:flex lg:items-center lg:justify-between"
        >
            <div>
                <h1 class="text-lg font-bold text-slate-900">
                    SAEB Entries
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Allotment, obligations, and balances by funding source.
                </p>
            </div>

            <div
                class="mt-3 inline-flex items-center rounded-full
                       bg-sky-50 px-3 py-1 text-xs font-semibold
                       text-sky-700 lg:mt-0"
            >
                Statement of Allotment, Expenditures &amp; Balances
            </div>
        </div>


        {{-- Filters --}}
        <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-4">

            <div class="flex flex-wrap items-end gap-4">

                {{-- Funding Source --}}
                <div class="w-full sm:w-auto sm:min-w-[220px]">

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


                {{-- Allotment Class --}}
                <div class="w-full sm:w-auto sm:min-w-[200px]">

                    <label
                        for="filterAllotmentClass"
                        class="mb-1.5 block text-xs font-semibold
                               uppercase tracking-wide text-slate-600"
                    >
                        Allotment Class
                    </label>

                    <select
                        id="filterAllotmentClass"
                        class="block w-full rounded-lg border border-slate-300
                               bg-white px-3 py-2 text-sm text-slate-700
                               shadow-sm outline-none transition
                               focus:border-sky-500 focus:ring-2
                               focus:ring-sky-100"
                    >
                        <option value="">All Classes</option>

                        @foreach ($allotmentClasses as $ac)
                            <option value="{{ $ac }}">
                                {{ $ac }}
                            </option>
                        @endforeach

                    </select>

                </div>


                {{-- Filter Button --}}
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


                {{-- Export Button --}}
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

                    <span class="export-label">Export Excel</span>
                </button>

            </div>

        </div>


        {{-- DataTable --}}
        <div class="p-5">

            <div class="overflow-x-auto">

                <table
                    id="saebTable"
                    class="w-full"
                >
                    <thead>
                        <tr>

                            <th>#</th>

                            <th>Funding Source</th>

                            <th>Allotment Class</th>

                            <th>Expense Class</th>

                            <th>Allotment</th>

                            <th>Obligated</th>

                            <th>AA</th>

                            <th>Balances</th>

                            <th>% Obligated</th>

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
    #saebTable {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        width: 100% !important;
        font-size: 0.82rem;
    }

    #saebTable thead th {
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

    #saebTable tbody td {
        color: #475569;
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 0.8rem 0.7rem !important;
        vertical-align: middle;
        background: #ffffff;
    }

    #saebTable tbody tr:hover td {
        background: #f8fafc;
    }

    #saebTable th,
    #saebTable td {
        text-align: center !important;
    }

    #saebTable th.text-start,
    #saebTable td.text-start {
        text-align: left !important;
    }

    #saebTable td:nth-child(5),
    #saebTable td:nth-child(6),
    #saebTable td:nth-child(7),
    #saebTable td:nth-child(8) {
        text-align: right !important;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }

    #saebTable_wrapper .dataTables_length,
    #saebTable_wrapper .dataTables_filter {
        margin-bottom: 1rem;
        color: #64748b;
        font-size: 0.8rem;
    }

    #saebTable_wrapper .dataTables_filter input,
    #saebTable_wrapper .dataTables_length select {
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        background: #ffffff;
        padding: 0.4rem 0.6rem;
        color: #475569;
        outline: none;
    }

    #saebTable_wrapper .dataTables_filter input:focus,
    #saebTable_wrapper .dataTables_length select:focus {
        border-color: #0ea5e9;
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
    }

    #saebTable_wrapper .dataTables_info {
        color: #64748b;
        font-size: 0.8rem;
        padding-top: 1rem;
    }

    #saebTable_wrapper .dataTables_paginate {
        padding-top: 0.75rem;
    }

    #saebTable_wrapper .dataTables_paginate .paginate_button {
        border: 0 !important;
        border-radius: 0.5rem !important;
        background: transparent !important;
        color: #475569 !important;
        margin: 0 2px;
    }

    #saebTable_wrapper .dataTables_paginate .paginate_button.current {
        background: #0284c7 !important;
        color: #ffffff !important;
    }

    #saebTable_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f1f5f9 !important;
        color: #0f172a !important;
    }

    .saeb-action {
        display: inline-flex;
        width: 32px;
        height: 32px;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        text-decoration: none !important;
        transition: all 0.15s ease;
    }

    .saeb-action-view {
        background: #f0f9ff;
        color: #0284c7 !important;
    }

    .saeb-action-view:hover {
        background: #e0f2fe;
    }

    .saeb-action-edit {
        background: #fffbeb;
        color: #d97706 !important;
    }

    .saeb-action-edit:hover {
        background: #fef3c7;
    }
</style>

@endpush


@push('js')

<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.4.0/exceljs.min.js"></script>

<script>
$(document).ready(function () {

    const REPORT_LABEL =
        'SAEB – Statement of Allotment, Expenditures and Balances';

    let table = null;
    let lastData = [];


    function money(value) {
        return Number(value ?? 0).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }


    function updateSummary(json) {

        $('#totalRecords').text(
            json.length.toLocaleString()
        );

        $('#sumAllotment').text(
            money(
                json.reduce(
                    (sum, row) => sum + Number(row.allotment || 0),
                    0
                )
            )
        );

        $('#sumObligated').text(
            money(
                json.reduce(
                    (sum, row) => sum + Number(row.obligated || 0),
                    0
                )
            )
        );

        $('#sumBalances').text(
            money(
                json.reduce(
                    (sum, row) => sum + Number(row.balances || 0),
                    0
                )
            )
        );

    }


    function loadTable() {

        const fundingSource =
            $('#filterFundingSource').val();

        const allotmentClass =
            $('#filterAllotmentClass').val();


        if (table) {

            table.destroy();

            $('#saebTable tbody').empty();

            table = null;

        }


        lastData = [];


        $('#totalRecords, #sumAllotment, #sumObligated, #sumBalances')
            .text('...');


        table = $('#saebTable').DataTable({

            processing: true,

            ajax: {

                url: '{{ route("saebs.data") }}',

                data: function (data) {

                    data.funding_source = fundingSource;

                    data.allotment_class = allotmentClass;

                },

                dataSrc: function (json) {

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
                    data: 'allotment_class',
                    defaultContent: '—'
                },

                {
                    data: 'expense_class',
                    defaultContent: '—',
                    className: 'text-start'
                },

                {
                    data: 'allotment',
                    render: money
                },

                {
                    data: 'obligated',
                    render: money
                },

                {
                    data: 'aa',
                    render: money
                },

                {
                    data: 'balances',
                    render: money
                },

                {
                    data: 'percent_obligated',
                    render: function (value) {

                        const percentage =
                            Number(value ?? 0);

                        let background = '#f1f5f9';
                        let color = '#475569';

                        if (percentage >= 80) {
                            background = '#d1fae5';
                            color = '#047857';
                        } else if (percentage >= 50) {
                            background = '#fef3c7';
                            color = '#b45309';
                        }

                        return `
                            <span
                                style="
                                    display:inline-flex;
                                    min-width:68px;
                                    justify-content:center;
                                    padding:4px 8px;
                                    border-radius:9999px;
                                    background:${background};
                                    color:${color};
                                    font-size:11px;
                                    font-weight:600;
                                "
                            >
                                ${percentage.toFixed(2)}%
                            </span>
                        `;

                    }
                },

                {
                    data: null,
                    orderable: false,
                    searchable: false,

                    render: function (row) {

                        const base =
                            `{{ url('administrator/saebs') }}`;

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
                                    class="saeb-action saeb-action-view"
                                    title="View"
                                >
                                    <i class="fa fa-eye"></i>
                                </a>

                                <a
                                    href="${base}/${row.id}/edit"
                                    class="saeb-action saeb-action-edit"
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
                    targets: [3],
                    className: 'text-start'
                }

            ]

        });

    }


    // Excel Export
    $('#exportBtn').on('click', async function () {

        const exportData = table
            ? table.rows({ search: 'applied' }).data().toArray()
            : lastData;


        if (!exportData.length) {

            alert('No data to export. Load the table first.');

            return;

        }


        const $btn = $(this);

        const originalButtonHtml = $btn.html();


        $btn
            .prop('disabled', true)
            .html(
                '<i class="fa fa-spinner fa-spin"></i>' +
                '<span class="export-label"> Exporting...</span>'
            );


        try {

            const fundingSource =
                $('#filterFundingSource').val() || 'all';

            const allotmentClass =
                $('#filterAllotmentClass').val() || 'all';


            const COLS = [

                {
                    key: 'funding_source',
                    label: 'Funding Source',
                    width: 22
                },

                {
                    key: 'allotment_class',
                    label: 'Allotment Class',
                    width: 14
                },

                {
                    key: 'expense_class',
                    label: 'Expense Class',
                    width: 32
                },

                {
                    key: 'allotment',
                    label: 'Allotment',
                    width: 16
                },

                {
                    key: 'obligated',
                    label: 'Obligated',
                    width: 16
                },

                {
                    key: 'aa',
                    label: 'AA',
                    width: 16
                },

                {
                    key: 'balances',
                    label: 'Balances',
                    width: 16
                },

                {
                    key: 'percent_obligated',
                    label: '% Obligated',
                    width: 14
                }

            ];


            const HEADER_BG =
                'FF1A3C5E';

            const HEADER_FONT =
                'FFFFFFFF';


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

                const isMoney = [
                    'allotment',
                    'obligated',
                    'aa',
                    'balances'
                ].includes(key);


                if (
                    isMoney &&
                    typeof cell.value === 'number'
                ) {
                    cell.numFmt = '#,##0.00';
                }


                if (
                    key === 'percent_obligated' &&
                    typeof cell.value === 'number'
                ) {
                    cell.numFmt = '0.00';
                }


                cell.font = {
                    size: 9,
                    name: 'Calibri',
                    color: {
                        argb: 'FF333333'
                    }
                };

                cell.fill = {
                    type: 'pattern',
                    pattern: 'solid',
                    fgColor: {
                        argb: 'FFFFFFFF'
                    }
                };

                cell.alignment = {
                    horizontal: isMoney ? 'right' : 'left',
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


            const ws = workbook.addWorksheet(
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


            // Title
            ws.mergeCells(
                1,
                1,
                1,
                colCount
            );


            Object.assign(
                ws.getCell(1, 1),
                {
                    value: 'PPMS – ERPMES',

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


            ws.getRow(1).height = 22;


            // Report Label
            ws.mergeCells(
                2,
                1,
                2,
                colCount
            );


            Object.assign(
                ws.getCell(2, 1),
                {
                    value: REPORT_LABEL,

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


            ws.getRow(2).height = 18;


            // Filter Information
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
                        } · Allotment Class: ${
                            allotmentClass === 'all'
                                ? 'All'
                                : allotmentClass
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


            ws.getRow(3).height = 14;


            // Column Headers
            const headerRow =
                ws.getRow(4);


            headerRow.height = 30;


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

                excelRow.height = 20;


                COLS.forEach(function (column, columnIndex) {

                    const cell =
                        excelRow.getCell(columnIndex + 1);


                    const numericColumns = [
                        'allotment',
                        'obligated',
                        'aa',
                        'balances',
                        'percent_obligated'
                    ];


                    cell.value =
                        row[column.key]
                        ?? (
                            numericColumns.includes(column.key)
                                ? 0
                                : '—'
                        );


                    dataStyle(
                        cell,
                        column.key
                    );

                });

            });


            // Column Widths
            COLS.forEach(function (column, index) {

                ws.getColumn(index + 1).width =
                    column.width;

            });


            // Freeze Header
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
                `saeb_${fundingSource}_${allotmentClass}.xlsx`;


            document.body.appendChild(anchor);

            anchor.click();

            document.body.removeChild(anchor);

            URL.revokeObjectURL(url);

        } catch (err) {

            console.error(
                '[SAEB Export]',
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


    $('#btnLoad').on(
        'click',
        loadTable
    );


    loadTable();


    if (
        typeof $.fn.tooltip === 'function'
    ) {
        $('[data-bs-toggle="tooltip"]').tooltip();
    }

});
</script>

@endpush
