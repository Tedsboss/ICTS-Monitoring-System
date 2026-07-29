@extends('layouts.app')

@section('content')
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'SAEB – Statement of Allotment, Expenditures and Balances'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>

<div class="container-fluid mt-4 px-4">

    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2
                    bg-white shadow-sm rounded-3 px-3 py-3 border">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('saebs.create') }}" class="btn btn-sm btn-primary px-3">
                    <i class="fa fa-plus me-1"></i> Add Entry
                </a>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">

                <div class="text-center">
                    <div class="fw-bold text-primary fs-5" id="totalRecords">—</div>
                    <small class="text-muted">Entries</small>
                </div>

                <div class="vr d-none d-md-block"></div>

                <div class="text-center">
                    <div class="fw-bold text-dark fs-5" id="sumAllotment">—</div>
                    <small class="text-muted">Allotment</small>
                </div>

                <div class="text-center">
                    <div class="fw-bold text-warning fs-5" id="sumObligated">—</div>
                    <small class="text-muted">Obligated</small>
                </div>

                <div class="text-center">
                    <div class="fw-bold text-success fs-5" id="sumBalances">—</div>
                    <small class="text-muted">Balances</small>
                </div>

                <div class="vr d-none d-md-block"></div>

                <div class="text-end">
                    <div class="fw-semibold">SAEB</div>
                    <small class="text-muted">Statement of Allotment, Expenditures &amp; Balances</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0">SAEB Entries</h5>
                <small class="text-muted">Allotment, obligations, and balances by funding source</small>
            </div>
        </div>

        <div class="card-body p-3">

            {{-- Filters --}}
            <div class="row gx-3 gy-2 mb-3 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-1">Funding Source</label>
                    <select id="filterFundingSource" class="form-select form-select-sm">
                        <option value="">All Funding Sources</option>
                        @foreach($fundingSources as $fs)
                            <option value="{{ $fs }}">{{ $fs }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-auto">
                    <label class="form-label mb-1">Allotment Class</label>
                    <select id="filterAllotmentClass" class="form-select form-select-sm">
                        <option value="">All Classes</option>
                        @foreach($allotmentClasses as $ac)
                            <option value="{{ $ac }}">{{ $ac }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-auto d-flex align-items-center gap-2">
                    <a href="javascript:void(0)" id="btnLoad" class="text-primary"
                        data-bs-toggle="tooltip" title="Filter">
                        <i class="bi bi-funnel fs-5"></i>
                    </a>
                    <a href="javascript:void(0)" id="exportBtn" class="text-success"
                        data-bs-toggle="tooltip" title="Export Excel">
                        <i class="bi bi-download fs-5"></i>
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="saebTable" class="table table-bordered table-hover" style="font-size:0.82rem;">
                    <thead class="table-light">
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
    </div>
</div>

@include('layouts.footers.auth.footer')
@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.4.0/exceljs.min.js"></script>

<style>
    #saebTable th.text-start,
    #saebTable td.text-start {
        text-align: left !important;
    }
</style>

<script>
$(document).ready(function () {

    const REPORT_LABEL = 'SAEB – Statement of Allotment, Expenditures and Balances';

    let table    = null;
    let lastData = [];

    function money(v) {
        return Number(v ?? 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updateSummary(json) {
        $('#totalRecords').text(json.length.toLocaleString());
        $('#sumAllotment').text(money(json.reduce((s, r) => s + Number(r.allotment || 0), 0)));
        $('#sumObligated').text(money(json.reduce((s, r) => s + Number(r.obligated || 0), 0)));
        $('#sumBalances').text(money(json.reduce((s, r) => s + Number(r.balances || 0), 0)));
    }

    function loadTable() {
        const fundingSource  = $('#filterFundingSource').val();
        const allotmentClass = $('#filterAllotmentClass').val();

        if (table) {
            table.destroy();
            $('#saebTable tbody').empty();
            table = null;
        }

        lastData = [];
        $('#totalRecords, #sumAllotment, #sumObligated, #sumBalances').text('...');

        table = $('#saebTable').DataTable({
            processing: true,
            ajax: {
                url: '{{ route("saebs.data") }}',
                data: function (d) {
                    d.funding_source  = fundingSource;
                    d.allotment_class = allotmentClass;
                },
                dataSrc: function (json) {
                    lastData = json;
                    updateSummary(json);
                    return json;
                },
            },
            columns: [
                { data: null,              render: (d, t, r, m) => m.row + 1, orderable: false },
                { data: 'funding_source',  defaultContent: '—' },
                { data: 'allotment_class', defaultContent: '—' },
                { data: 'expense_class',   defaultContent: '—', className: 'text-start' },
                { data: 'allotment',       render: money },
                { data: 'obligated',       render: money },
                { data: 'aa',              render: money },
                { data: 'balances',        render: money },
                { data: 'percent_obligated', render: (v) => `${Number(v ?? 0).toFixed(2)}%` },
                {
                    data: null,
                    orderable: false,
                    render: function (r) {
                        const base = `{{ url('administrator/saebs') }}`;
                        return `
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="${base}/${r.id}" class="text-info" title="View">
                                    <i class="fa fa-eye fs-5"></i>
                                </a>
                                <a href="${base}/${r.id}/edit" class="text-warning" title="Edit">
                                    <i class="fa fa-pencil fs-5"></i>
                                </a>
                            </div>`;
                    }
                },
            ],
            pageLength: 15,
            columnDefs: [
                { targets: '_all', className: 'text-center' },
                { targets: [3], className: 'text-start' },
            ],
        });
    }

    // ── Excel Export ─

    $('#exportBtn').on('click', async function () {

        const exportData = table
            ? table.rows({ search: 'applied' }).data().toArray()
            : lastData;

        if (!exportData.length) {
            alert('No data to export. Load the table first.');
            return;
        }

        const $btn = $(this);
        $btn.html('<i class="bi bi-hourglass-split fs-5"></i>').addClass('disabled');

        try {
            const fundingSource  = $('#filterFundingSource').val() || 'all';
            const allotmentClass = $('#filterAllotmentClass').val() || 'all';

            const COLS = [
                { key: 'funding_source',    label: 'Funding Source',   width: 22 },
                { key: 'allotment_class',   label: 'Allotment Class',  width: 14 },
                { key: 'expense_class',     label: 'Expense Class',    width: 32 },
                { key: 'allotment',         label: 'Allotment',        width: 16 },
                { key: 'obligated',         label: 'Obligated',        width: 16 },
                { key: 'aa',                label: 'AA',               width: 16 },
                { key: 'balances',          label: 'Balances',         width: 16 },
                { key: 'percent_obligated', label: '% Obligated',      width: 14 },
            ];

            const HEADER_BG   = 'FF1a3c5e';
            const HEADER_FONT = 'FFFFFFFF';

            const headerStyle = (cell) => {
                cell.font      = { bold: true, color: { argb: HEADER_FONT }, size: 10, name: 'Calibri' };
                cell.fill      = { type: 'pattern', pattern: 'solid', fgColor: { argb: HEADER_BG } };
                cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
                cell.border    = {
                    top: { style: 'thin', color: { argb: 'FF2a5a8a' } },
                    left: { style: 'thin', color: { argb: 'FF2a5a8a' } },
                    bottom: { style: 'thin', color: { argb: 'FF2a5a8a' } },
                    right: { style: 'thin', color: { argb: 'FF2a5a8a' } },
                };
            };

            const dataStyle = (cell, key) => {
                const isMoney = ['allotment', 'obligated', 'aa', 'balances'].includes(key);
                if (isMoney && typeof cell.value === 'number') {
                    cell.numFmt = '#,##0.00';
                }
                cell.font      = { size: 9, name: 'Calibri', color: { argb: 'FF333333' } };
                cell.fill      = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFFFFFF' } };
                cell.alignment = { horizontal: isMoney ? 'right' : 'left', vertical: 'middle', wrapText: true };
                cell.border    = {
                    top:    { style: 'thin', color: { argb: 'FFD0D0D0' } },
                    left:   { style: 'thin', color: { argb: 'FFD0D0D0' } },
                    bottom: { style: 'thin', color: { argb: 'FFD0D0D0' } },
                    right:  { style: 'thin', color: { argb: 'FFD0D0D0' } },
                };
            };

            const workbook = new ExcelJS.Workbook();
            workbook.creator = 'PPMS – ERPMES';
            workbook.created = new Date();

            const ws = workbook.addWorksheet(REPORT_LABEL.substring(0, 31), {
                pageSetup: { paperSize: 9, orientation: 'landscape', fitToPage: true },
            });

            const colCount = COLS.length;

            ws.mergeCells(1, 1, 1, colCount);
            Object.assign(ws.getCell(1, 1), {
                value: 'PPMS – ERPMES',
                font:      { bold: true, size: 13, name: 'Calibri', color: { argb: HEADER_FONT } },
                fill:      { type: 'pattern', pattern: 'solid', fgColor: { argb: HEADER_BG } },
                alignment: { horizontal: 'center', vertical: 'middle' },
            });
            ws.getRow(1).height = 22;

            ws.mergeCells(2, 1, 2, colCount);
            Object.assign(ws.getCell(2, 1), {
                value: REPORT_LABEL,
                font:      { bold: true, size: 11, name: 'Calibri', color: { argb: HEADER_FONT } },
                fill:      { type: 'pattern', pattern: 'solid', fgColor: { argb: HEADER_BG } },
                alignment: { horizontal: 'center', vertical: 'middle' },
            });
            ws.getRow(2).height = 18;

            ws.mergeCells(3, 1, 3, colCount);
            Object.assign(ws.getCell(3, 1), {
                value: `Funding Source: ${fundingSource === 'all' ? 'All' : fundingSource}   ·   Allotment Class: ${allotmentClass === 'all' ? 'All' : allotmentClass}   ·   Generated: ${new Date().toLocaleString('en-PH')}`,
                font:      { italic: true, size: 9, name: 'Calibri', color: { argb: 'FF555555' } },
                fill:      { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFf0f4ff' } },
                alignment: { horizontal: 'center', vertical: 'middle' },
            });
            ws.getRow(3).height = 14;

            const hRow = ws.getRow(4);
            hRow.height = 30;
            COLS.forEach((col, i) => {
                const cell = hRow.getCell(i + 1);
                cell.value = col.label;
                headerStyle(cell);
            });

            exportData.forEach((row, ri) => {
                const eRow = ws.getRow(5 + ri);
                eRow.height = 20;
                COLS.forEach((col, ci) => {
                    const cell = eRow.getCell(ci + 1);
                    cell.value = row[col.key] ?? (['allotment','obligated','aa','balances','percent_obligated'].includes(col.key) ? 0 : '—');
                    dataStyle(cell, col.key);
                });
            });

            COLS.forEach((col, i) => {
                ws.getColumn(i + 1).width = col.width;
            });

            ws.views = [{ state: 'frozen', ySplit: 4 }];

            const buffer = await workbook.xlsx.writeBuffer();
            const blob   = new Blob([buffer], {
                type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            });
            const url = URL.createObjectURL(blob);
            const a   = document.createElement('a');
            a.href     = url;
            a.download = `saeb_${fundingSource}_${allotmentClass}.xlsx`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);

        } catch (err) {
            console.error('[SAEB Export]', err);
            alert('Export failed:\n' + err.message);
        } finally {
            $btn.html('<i class="bi bi-download fs-5"></i>').removeClass('disabled');
        }
    });

    $('#btnLoad').on('click', loadTable);
    loadTable();

    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush
