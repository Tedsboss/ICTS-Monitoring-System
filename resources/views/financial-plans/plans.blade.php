{{-- resources/views/financial-plans/plans.blade.php --}}
@extends('layouts.app')

@section('content')
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Work & Financial Plans'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>

<div class="container-fluid mt-4 px-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stat strip --}}
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2
                    bg-white shadow-sm rounded-3 px-3 py-3 border">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="fw-semibold">Work & Financial Plans</div>
                <small class="text-muted">All fiscal years / offices</small>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="text-center">
                    <div class="fw-bold text-primary fs-5" id="totalRecords">—</div>
                    <small class="text-muted">Total</small>
                </div>

                <div class="vr d-none d-md-block"></div>

                <div class="text-center">
                    <div class="fw-bold text-secondary fs-5" id="countDraft">—</div>
                    <small class="text-muted">Draft</small>
                </div>

                <div class="text-center">
                    <div class="fw-bold text-success fs-5" id="countFinalized">—</div>
                    <small class="text-muted">Finalized</small>
                </div>
            </div>
        </div>
    </div>

    {{-- File a new WFP --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h6 class="mb-0"><i class="fa fa-plus-circle me-1 text-success"></i> File a New Work & Financial Plan</h6>
        </div>
        <div class="card-body p-3">
            <div class="row g-3 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-1">Fiscal Year</label>
                    <input type="number" id="newFiscalYear" class="form-control form-control-sm" style="width:120px;" value="{{ now()->year }}">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1">Name of Office/Staff</label>
                    <input type="text" id="newOfficeName" class="form-control form-control-sm" style="width:340px;"
                        list="officeSuggestions" placeholder="Type an existing office, or a brand-new one">
                    <datalist id="officeSuggestions">
                        @foreach($offices as $office)
                            <option value="{{ $office }}">
                        @endforeach
                    </datalist>
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1 invisible">Action</label>
                    <button type="button" id="btnStartPlan" class="btn btn-sm btn-success d-flex align-items-center justify-content-center"
                            style="width:31px; height:31px;" data-bs-toggle="tooltip" title="Start / Open in Builder">
                        <i class="fa fa-arrow-right"></i>
                    </button>
                </div>
            </div>
            <p class="text-muted small mb-0 mt-2">
                If that fiscal year + office combination already has rows on file, the builder loads them for editing.
                Otherwise it opens a blank plan ready to fill in.
            </p>
        </div>
    </div>

    {{-- All filed plans --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h6 class="mb-0">Filed Work & Financial Plans</h6>
                <small class="text-muted">{{ $plans->count() }} on file</small>
            </div>
        </div>

        <div class="card-body p-3">

            {{-- Filters --}}
            <div class="row gx-3 gy-2 mb-3 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-1">Fiscal Year</label>
                    <select id="filterFiscalYear" class="form-select form-select-sm">
                        <option value="">All Years</option>
                        @foreach($fiscalYears as $fy)
                            <option value="{{ $fy }}">{{ $fy }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-auto">
                    <label class="form-label mb-1">Office/Staff</label>
                    <select id="filterOffice" class="form-select form-select-sm" style="min-width:220px;">
                        <option value="">All Offices</option>
                        @foreach($offices as $office)
                            <option value="{{ $office }}">{{ $office }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-auto d-flex align-items-center gap-2">
                    <a href="javascript:void(0)" id="exportBtn" class="text-success"
                        data-bs-toggle="tooltip" title="Export Excel">
                        <i class="bi bi-download fs-5"></i>
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="plansTable" class="table table-bordered table-hover" style="font-size:0.82rem;">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Fiscal Year</th>
                            <th>Office/Staff</th>
                            <th class="text-end">Rows on File</th>
                            <th class="text-end">MOOE + CO Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plans as $plan)
                            <tr>
                                <td></td>
                                <td>{{ $plan->fiscal_year }}</td>
                                <td>{{ $plan->office_name }}</td>
                                <td class="text-end" data-order="{{ $plan->row_count }}">{{ $plan->row_count }}</td>
                                <td class="text-end" data-order="{{ $plan->budget_sum }}">{{ number_format($plan->budget_sum, 2) }}</td>
                                <td data-status="{{ $plan->finalized === 'yes' ? 'Finalized' : 'Draft' }}">
                                    @if($plan->finalized === 'yes')
                                        <span class="badge bg-success">Finalized</span>
                                    @else
                                        <span class="badge bg-secondary">Draft</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('financial-plans.index', ['fiscal_year' => $plan->fiscal_year, 'office_name' => $plan->office_name]) }}"
                                       class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="View">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="{{ route('financial-plans.builder', ['fiscal_year' => $plan->fiscal_year, 'office_name' => $plan->office_name]) }}"
                                       class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Edit">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('layouts.footers.auth.footer')
@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.4.0/exceljs.min.js"></script>

<script>
$(document).ready(function () {

    const STATUS_COLORS_ARGB = {
        'Finalized': 'FF2dce89',
        'Draft':     'FFadb5bd',
    };

    let table = null;

    function updateStats() {
        const rows = table.rows({ search: 'applied' }).nodes();
        const total = rows.length;
        let draft = 0, finalized = 0;

        $(rows).each(function () {
            const status = $(this).find('td[data-status]').data('status');
            if (status === 'Finalized') finalized++;
            else draft++;
        });

        $('#totalRecords').text(total.toLocaleString());
        $('#countDraft').text(draft.toLocaleString());
        $('#countFinalized').text(finalized.toLocaleString());
    }

    function initTable() {
        table = $('#plansTable').DataTable({
            columnDefs: [
                { targets: 0, orderable: false, render: (d, t, r, m) => m.row + 1 },
                { targets: [3, 4], className: 'text-end' },
                { targets: [5, 6], orderable: false },
            ],
            order: [[1, 'desc'], [2, 'asc']],
            pageLength: 15,
        });

        table.on('draw', updateStats);
        // Run once manually for the very first render.
        updateStats();
    }
    // Custom exact-match filter — avoids regex entirely, so office names
    // with parentheses, periods, plus signs, etc. can't break the search.
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'plansTable') {
            return true;
        }

        const fy     = $('#filterFiscalYear').val();
        const office = $('#filterOffice').val();

        if (fy && data[1].trim() !== fy.trim()) {
            return false;
        }

        if (office && data[2].trim() !== office.trim()) {
            return false;
        }

        return true;
    });

    function applyFilters() {
        console.log('applyFilters fired', $('#filterFiscalYear').val(), $('#filterOffice').val());
        table.draw();
    }

    // ── Excel Export ─
    $('#exportBtn').on('click', async function () {
        const exportRows = table.rows({ search: 'applied' }).nodes();

        if (!exportRows.length) {
            alert('No data to export.');
            return;
        }

        const $btn = $(this);
        $btn.html('<i class="bi bi-hourglass-split fs-5"></i>').addClass('disabled');

        try {
            const COLS = [
                { key: 'fiscal_year', label: 'Fiscal Year', width: 14 },
                { key: 'office_name', label: 'Office/Staff', width: 40 },
                { key: 'row_count',   label: 'Rows on File', width: 16 },
                { key: 'budget_sum',  label: 'MOOE + CO Total', width: 20 },
                { key: 'status',      label: 'Status', width: 16 },
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
                const isStatus = key === 'status';
                const argb     = isStatus
                    ? (STATUS_COLORS_ARGB[cell.value] ?? STATUS_COLORS_ARGB['Draft'])
                    : 'FFFFFFFF';

                cell.font = {
                    size : 9,
                    name : 'Calibri',
                    bold : isStatus,
                    color: isStatus ? { argb: 'FFFFFFFF' } : { argb: 'FF333333' },
                };
                cell.fill      = { type: 'pattern', pattern: 'solid', fgColor: { argb } };
                cell.alignment = { horizontal: key === 'budget_sum' || key === 'row_count' ? 'right' : 'left', vertical: 'middle', wrapText: true };
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

            const ws = workbook.addWorksheet('Financial Plans'.substring(0, 31), {
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
                value: 'Filed Work & Financial Plans',
                font:      { bold: true, size: 11, name: 'Calibri', color: { argb: HEADER_FONT } },
                fill:      { type: 'pattern', pattern: 'solid', fgColor: { argb: HEADER_BG } },
                alignment: { horizontal: 'center', vertical: 'middle' },
            });
            ws.getRow(2).height = 18;

            const fy = $('#filterFiscalYear').val() || 'All';
            const office = $('#filterOffice').val() || 'All';

            ws.mergeCells(3, 1, 3, colCount);
            Object.assign(ws.getCell(3, 1), {
                value: `Fiscal Year: ${fy}   ·   Office: ${office}   ·   Generated: ${new Date().toLocaleString('en-PH')}`,
                font:      { italic: true, size: 9, name: 'Calibri', color: { argb: 'FF555555' } },
                fill:      { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFf0f4ff' } },
                alignment: { horizontal: 'center', vertical: 'middle' },
            });
            ws.getRow(3).height = 14;

            const hRow = ws.getRow(4);
            hRow.height = 24;
            COLS.forEach((col, i) => {
                const cell = hRow.getCell(i + 1);
                cell.value = col.label;
                headerStyle(cell);
            });

            $(exportRows).each(function (ri) {
                const $tr = $(this);
                const rowData = {
                    fiscal_year: $tr.find('td').eq(1).text().trim(),
                    office_name: $tr.find('td').eq(2).text().trim(),
                    row_count:   $tr.find('td').eq(3).text().trim(),
                    budget_sum:  $tr.find('td').eq(4).text().trim(),
                    status:      $tr.find('td[data-status]').data('status'),
                };

                const eRow = ws.getRow(5 + ri);
                eRow.height = 18;
                COLS.forEach((col, ci) => {
                    const cell = eRow.getCell(ci + 1);
                    cell.value = rowData[col.key] ?? '—';
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
            a.download = `financial_plans_${fy}_${office}.xlsx`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);

        } catch (err) {
            console.error('[Financial Plans Export]', err);
            alert('Export failed:\n' + err.message);
        } finally {
            $btn.html('<i class="bi bi-download fs-5"></i>').removeClass('disabled');
        }
    });

    $('#btnStartPlan').on('click', function () {
        const fiscalYear = $('#newFiscalYear').val();
        const officeName = $('#newOfficeName').val().trim();

        if (!fiscalYear) {
            alert('Fiscal Year is required.');
            return;
        }
        if (!officeName) {
            alert('Name of Office/Staff is required.');
            return;
        }

        window.location.href =
            `{{ route('financial-plans.builder') }}?fiscal_year=${fiscalYear}&office_name=${encodeURIComponent(officeName)}`;
    });

    // Auto-apply as soon as either filter changes — no need to click the funnel.
    $('#filterFiscalYear, #filterOffice').on('change', applyFilters);

    initTable();
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush
