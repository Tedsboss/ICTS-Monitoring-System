@extends('layouts.app')

@section('content')
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Procurements based on WFP'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>

<div class="container-fluid mt-4 px-4">

    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2
                    bg-white shadow-sm rounded-3 px-3 py-3 border">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('procurements.create') }}" class="btn btn-sm btn-primary px-3">
                    <i class="fa fa-plus me-1"></i> Add Procurement
                </a>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">

                <div class="text-center">
                    <div class="fw-bold text-primary fs-5" id="totalRecords">—</div>
                    <small class="text-muted">Entries</small>
                </div>

                <div class="vr d-none d-md-block"></div>

                <div class="text-center">
                    <div class="fw-bold text-dark fs-5" id="sumAmount">—</div>
                    <small class="text-muted">Amount</small>
                </div>

                <div class="text-center">
                    <div class="fw-bold text-success fs-5" id="countProcured">—</div>
                    <small class="text-muted">Procured</small>
                </div>

                <div class="text-center">
                    <div class="fw-bold text-success fs-5" id="countPaid">—</div>
                    <small class="text-muted">Paid</small>
                </div>

                <div class="vr d-none d-md-block"></div>

                <div class="text-end">
                    <div class="fw-semibold">Procurements</div>
                    <small class="text-muted">Based on Work and Financial Plan</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0">Procurement Entries</h5>
                <small class="text-muted">Procurement, payment and retention tracking per WFP item</small>
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
                    <label class="form-label mb-1">Expense Class</label>
                    <select id="filterExpenseClass" class="form-select form-select-sm">
                        <option value="">All Classes</option>
                        @foreach($expenseClasses as $ec)
                            <option value="{{ $ec }}">{{ $ec }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-auto">
                    <label class="form-label mb-1">Search</label>
                    <input type="text" id="filterSearch" class="form-control form-control-sm" placeholder="Procurement title...">
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
                <table id="procurementTable" class="table table-bordered table-hover" style="font-size:0.82rem;">
                    <thead class="table-light">
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
    </div>
</div>

@include('layouts.footers.auth.footer')
@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.4.0/exceljs.min.js"></script>

<style>
    #procurementTable th.text-start,
    #procurementTable td.text-start {
        text-align: left !important;
    }
</style>

<script>
$(document).ready(function () {

    const REPORT_LABEL = 'Procurements based on WFP';

    let table    = null;
    let lastData = [];

    function money(v) {
        return Number(v ?? 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function statusBadge(status) {
        return status === 'OK'
            ? '<span class="badge bg-success">OK</span>'
            : '<span class="text-muted">—</span>';
    }

    function updateSummary(json) {
        $('#totalRecords').text(json.length.toLocaleString());
        $('#sumAmount').text(money(json.reduce((s, r) => s + Number(r.amount || 0), 0)));
        $('#countProcured').text(json.filter(r => r.procurement_status === 'OK').length.toLocaleString());
        $('#countPaid').text(json.filter(r => r.payment_status === 'OK').length.toLocaleString());
    }

    function loadTable() {
        const fundingSource = $('#filterFundingSource').val();
        const expenseClass  = $('#filterExpenseClass').val();
        const search        = $('#filterSearch').val();

        if (table) {
            table.destroy();
            $('#procurementTable tbody').empty();
            table = null;
        }

        lastData = [];
        $('#totalRecords, #sumAmount, #countProcured, #countPaid').text('...');

        table = $('#procurementTable').DataTable({
            processing: true,
            ajax: {
                url: '{{ route("procurements.data") }}',
                data: function (d) {
                    d.funding_source = fundingSource;
                    d.expense_class  = expenseClass;
                },
                dataSrc: function (json) {
                    if (search) {
                        json = json.filter(r => (r.procurement_title || '').toLowerCase().includes(search.toLowerCase()));
                    }
                    lastData = json;
                    updateSummary(json);
                    return json;
                },
            },
            columns: [
                { data: null,                   render: (d, t, r, m) => m.row + 1, orderable: false },
                { data: 'funding_source',       defaultContent: '—' },
                {
                    data: 'procurement_title',
                    defaultContent: '—',
                    className: 'text-start',
                    render: (data) => data ? `<div style="white-space:normal; min-width:220px;">${data}</div>` : '—',
                },
                { data: 'expense_class',        defaultContent: '—' },
                { data: 'division_assigned',    defaultContent: '—' },
                { data: 'amount',               render: money },
                { data: 'quarter',              defaultContent: '—' },
                { data: 'procurement_status',   render: statusBadge },
                { data: 'payment_status',       render: statusBadge },
                { data: 'retention_status',     render: statusBadge },
                {
                    data: null,
                    orderable: false,
                    render: function (r) {
                        const base = `{{ url('administrator/procurements') }}`;
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
                { targets: [2], className: 'text-start' },
            ],
        });
    }

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
            const fundingSource = $('#filterFundingSource').val() || 'all';
            const expenseClass  = $('#filterExpenseClass').val() || 'all';

            const COLS = [
                { key: 'funding_source',     label: 'Funding Source',     width: 18 },
                { key: 'procurement_title',  label: 'Procurement Title',  width: 45 },
                { key: 'expense_class',      label: 'Expense Class',      width: 14 },
                { key: 'division_assigned',  label: 'Division',           width: 14 },
                { key: 'amount',             label: 'Amount',             width: 16 },
                { key: 'quarter',            label: 'Quarter',            width: 10 },
                { key: 'procurement_status', label: 'Procurement',        width: 14 },
                { key: 'payment_status',     label: 'Payment',            width: 14 },
                { key: 'retention_status',   label: 'Retention',          width: 14 },
            ];

            const HEADER_BG   = 'FF1a3c5e';
            const HEADER_FONT = 'FFFFFFFF';
            const STATUS_ARGB = 'FF2dce89';

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
                const isStatus = ['procurement_status', 'payment_status', 'retention_status'].includes(key);
                const isMoney  = key === 'amount';
                if (isMoney && typeof cell.value === 'number') cell.numFmt = '#,##0.00';

                cell.font = {
                    size : 9,
                    name : 'Calibri',
                    bold : isStatus && cell.value === 'OK',
                    color: (isStatus && cell.value === 'OK') ? { argb: 'FFFFFFFF' } : { argb: 'FF333333' },
                };
                cell.fill = {
                    type: 'pattern', pattern: 'solid',
                    fgColor: { argb: (isStatus && cell.value === 'OK') ? STATUS_ARGB : 'FFFFFFFF' },
                };
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
                value: `Funding Source: ${fundingSource === 'all' ? 'All' : fundingSource}   ·   Expense Class: ${expenseClass === 'all' ? 'All' : expenseClass}   ·   Generated: ${new Date().toLocaleString('en-PH')}`,
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
                    cell.value = row[col.key] ?? (col.key === 'amount' ? 0 : '—');
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
            a.download = `procurements_${fundingSource}_${expenseClass}.xlsx`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);

        } catch (err) {
            console.error('[Procurement Export]', err);
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