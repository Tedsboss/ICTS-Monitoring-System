{{-- resources/views/financial-plans/index.blade.php --}}
@extends('layouts.app')

@section('content')
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'FY ' . $fiscalYear . ' Financial Plan'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>

<div class="container-fluid mt-4 px-4">

    <div class="mb-3">
        <a href="{{ route('financial-plans.plans') }}" class="btn btn-sm btn-outline-dark px-3">
            <i class="fa fa-arrow-left me-1"></i> Back to All Plans
        </a>
    </div>

    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2
                    bg-white shadow-sm rounded-3 px-3 py-3 border">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="javascript:void(0)" id="btnEditPlan" class="btn btn-sm btn-primary px-3">
                    <i class="fa fa-pencil me-1"></i> Edit This Plan
                </a>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0 me-1">Fiscal Year</label>
                <input type="number" id="filterFiscalYear" class="form-control form-control-sm" value="{{ $fiscalYear }}" style="width:100px;">

                <label class="form-label mb-0 ms-2 me-1">Office/Staff</label>
                <input type="text" id="filterOffice" class="form-control form-control-sm" style="width:260px;"
                       list="filterOfficeSuggestions" value="{{ $officeName }}" placeholder="Type an office name">
                <datalist id="filterOfficeSuggestions">
                    @foreach($offices as $office)
                        <option value="{{ $office }}">
                    @endforeach
                </datalist>

                <a href="javascript:void(0)" id="btnLoad" class="text-primary ms-1" data-bs-toggle="tooltip" title="Filter">
                    <i class="bi bi-funnel fs-5"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-3">

            {{-- Title block, mirrors the GAA worksheet header --}}
            <div class="mb-3 px-1" style="font-family: Calibri, Arial, sans-serif;">
                <div class="fw-bold" style="font-size:0.95rem;">FY {{ $fiscalYear }} FINANCIAL PLAN</div>
                <div class="fst-italic text-muted" style="font-size:0.85rem;">
                    (FY {{ $fiscalYear }} Internal Allocation per approved {{ $fiscalYear }} GAA)
                </div>
                <div style="font-size:0.85rem;">
                    Name of Office/Staff: <span class="fw-bold text-decoration-underline">{{ $officeName }}</span>
                </div>
            </div>

            <style>
                #fpTable {
                    table-layout: fixed;
                    width: 2450px;
                }
                #fpTable .wrap-cell {
                    white-space: normal;
                    word-wrap: break-word;
                    overflow-wrap: break-word;
                    word-break: break-word;
                }
                #fpTable .prexc-cell {
                    white-space: normal;
                    word-break: break-all;
                }
                /* Rowspan'd classification/PREXC cells should align to the
                   top of their merged block, not vertically center against
                   the full height of every item row underneath them. */
                #fpTable .align-top {
                    vertical-align: top;
                }
                #fpTable thead th {
                    color: #055160;
                }
            </style>

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle mb-0" style="font-size:0.78rem;" id="fpTable">
                    <colgroup>
                        <col style="width:220px;"> {{-- Program Classification --}}
                        <col style="width:130px;"> {{-- PREXC Code (widened: long digit codes) --}}
                        <col style="width:130px;"> {{-- Staff/Unit/Project --}}
                        <col style="width:200px;"> {{-- Specific Activity --}}
                        <col style="width:110px;"> {{-- Procurement Status --}}
                        <col style="width:110px;"> {{-- SAEB Balance --}}
                        <col style="width:120px;"> {{-- Expense Item --}}
                        <col style="width:120px;"> {{-- Assigned Personnel --}}
                        <col style="width:110px;"> {{-- MOOE --}}
                        <col style="width:110px;"> {{-- Capital Outlay --}}
                        @for($i = 1; $i <= 12; $i++)
                            <col style="width:90px;"> {{-- Month {{ $i }} (widened: peso amounts up to 7 figures) --}}
                        @endfor
                        <col style="width:120px;"> {{-- TOTAL --}}
                    </colgroup>
                    <thead class="text-center" style="background:#eaf6fb;">
                        <tr>
                            <th rowspan="2" class="wrap-cell">DEPDev Program of Expenditure Classification (a)</th>
                            <th rowspan="2" class="prexc-cell">PREXC Code (b)</th>
                            <th rowspan="2" class="wrap-cell">Staffs/Units/Projects Concerned (c)</th>
                            <th rowspan="2" class="wrap-cell">Specific Activity/Project of Staffs/Unit/Project (d)</th>
                            <th rowspan="2" class="wrap-cell">Status of Procurement (as of {{ now()->format('F j, Y') }}) (e)</th>
                            <th rowspan="2" class="wrap-cell">SAEB Balance</th>
                            <th rowspan="2" class="wrap-cell">Expense Item</th>
                            <th rowspan="2" class="wrap-cell">Assigned Personnel</th>
                            <th rowspan="2">MOOE</th>
                            <th rowspan="2">Capital Outlay</th>
                            <th colspan="12">Financial Target/Output (₱) (f)</th>
                            <th rowspan="2">TOTAL</th>
                        </tr>
                        <tr>
                            @foreach($months as $label)
                                <th style="background:#eaf6fb;">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody id="fpBody"></tbody>
                    <tfoot>
                        <tr class="fw-bold" style="background:#e9ecef;">
                            <td colspan="8" class="text-end">GRAND TOTAL</td>
                            <td id="totMooe" class="text-end">0.00</td>
                            <td id="totCo" class="text-end">0.00</td>
                            @for($i = 1; $i <= 12; $i++)
                                <td id="totM{{ $i }}" class="text-end">0.00</td>
                            @endfor
                            <td id="totGrand" class="text-end">0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

@include('layouts.footers.auth.footer')
@endsection

@push('js')
<script>
$(document).ready(function ()
{
    function money(v) {
        const n = Number(v ?? 0);
        return (Number.isFinite(n) ? n : 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function statusBadge(status) {
        return status === 'OK'
            ? '<span class="badge" style="background:#2dce89;">OK</span>'
            : '<span class="text-muted">—</span>';
    }

    // rowspan > 0  -> this row is the FIRST row of its classification/PREXC
    //                 group, so it renders (and merges) those two cells.
    // rowspan === 0 -> a later row in the same group; classification/PREXC
    //                 cells are skipped entirely (covered by the rowspan
    //                 cell rendered on the group's first row).
    function renderItemRow(r, rowspan) {
        let monthCells = '';
        for (let m = 1; m <= 12; m++) {
            monthCells += `<td class="text-end">${money(r.months[m] || 0)}</td>`;
        }

        const classCells = rowspan > 0
            ? `<td class="wrap-cell align-top" rowspan="${rowspan}">${r.program_classification ?? '—'}</td>
               <td class="text-center prexc-cell align-top" rowspan="${rowspan}">${r.prexc_code ?? '—'}</td>`
            : '';

        return `
            <tr>
                ${classCells}
                <td class="text-center wrap-cell">${r.staff_unit_project ?? '—'}</td>
                <td class="wrap-cell">${r.specific_activity ?? '—'}</td>
                <td class="text-center">${statusBadge(r.procurement_status)}</td>
                <td class="text-end">${money(r.saeb_balance ?? 0)}</td>
                <td class="text-center wrap-cell">${r.expense_item ?? '—'}</td>
                <td class="text-center wrap-cell">${r.assigned_personnel ?? '—'}</td>
                <td class="text-end">${money(r.mooe)}</td>
                <td class="text-end">${money(r.capital_outlay)}</td>
                ${monthCells}
                <td class="text-end fw-semibold">${money(r.total)}</td>
            </tr>
        `;
    }

    function renderHeaderRow(r) {
        // 2 (classification + PREXC) + 8 (staff..capital_outlay, incl. SAEB
        // balance) + 12 (months) + 1 (TOTAL) = 23 columns, matching the
        // <colgroup> (Action column removed).
        return `
            <tr class="fw-semibold" style="background:#eef1f5;">
                <td class="wrap-cell">${r.program_classification ?? '—'}</td>
                <td class="text-center prexc-cell">${r.prexc_code ?? ''}</td>
                <td colspan="8"></td>
                ${Array(12).fill('<td></td>').join('')}
                <td></td>
            </tr>
        `;
    }

    function renderSubtotalRow(totals) {
        let monthCells = '';
        for (let m = 1; m <= 12; m++) {
            monthCells += `<td class="text-end">${money(totals.months[m])}</td>`;
        }
        return `
            <tr class="fw-bold" style="background:#f1f3f5;">
                <td colspan="8" class="text-end">TOTAL</td>
                <td class="text-end">${money(totals.mooe)}</td>
                <td class="text-end">${money(totals.capital_outlay)}</td>
                ${monthCells}
                <td class="text-end">${money(totals.total)}</td>
            </tr>
        `;
    }

    function emptyTotals() {
        const t = { mooe: 0, capital_outlay: 0, total: 0, months: {} };
        for (let m = 1; m <= 12; m++) t.months[m] = 0;
        return t;
    }

    function addToTotals(totals, r) {
        totals.mooe += Number(r.mooe) || 0;
        totals.capital_outlay += Number(r.capital_outlay) || 0;
        totals.total += Number(r.total) || 0;
        for (let m = 1; m <= 12; m++) totals.months[m] += (Number(r.months[m]) || 0);
    }

    // Splits the row list into display "blocks":
    //  - a header block for each row_type === 'header' row (section dividers
    //    like "A. Programs" / classification banners), rendered as-is
    //  - a group block for each run of *consecutive* item rows that share
    //    the same (program_classification, prexc_code) pair — this is the
    //    unit that gets merged via rowspan and closed with a TOTAL row
    function buildBlocks(rows) {
        const blocks = [];
        let run = null;

        rows.forEach(r => {
            if (r.row_type === 'header') {
                if (run) { blocks.push(run); run = null; }
                blocks.push({ type: 'header', row: r });
                return;
            }

            const key = (r.program_classification || '').trim() + '::' + (r.prexc_code || '').trim();

            if (!run || run.key !== key) {
                if (run) blocks.push(run);
                run = { type: 'group', key, rows: [] };
            }

            run.rows.push(r);
        });

        if (run) blocks.push(run);

        return blocks;
    }

    function renderBlocks(blocks, $body) {
        blocks.forEach(block => {
            if (block.type === 'header') {
                $body.append(renderHeaderRow(block.row));
                return;
            }

            const totals = emptyTotals();

            block.rows.forEach((r, idx) => {
                $body.append(renderItemRow(r, idx === 0 ? block.rows.length : 0));
                addToTotals(totals, r);
            });

            $body.append(renderSubtotalRow(totals));
        });
    }

    function loadTable() {
        const fiscalYear = $('#filterFiscalYear').val();
        const officeName = $('#filterOffice').val();

        $.getJSON('{{ route("financial-plans.data") }}', { fiscal_year: fiscalYear, office_name: officeName }, function (rows) {
            const $body = $('#fpBody').empty();

            renderBlocks(buildBlocks(rows), $body);

            const grand = { mooe: 0, co: 0, months: Array(13).fill(0), grand: 0 };
            rows.forEach(r => {
                if (r.row_type === 'item') {
                    grand.mooe += Number(r.mooe) || 0;
                    grand.co += Number(r.capital_outlay) || 0;
                    grand.grand += Number(r.total) || 0;
                    for (let m = 1; m <= 12; m++) grand.months[m] += (Number(r.months[m]) || 0);
                }
            });

            $('#totMooe').text(money(grand.mooe));
            $('#totCo').text(money(grand.co));
            for (let m = 1; m <= 12; m++) $(`#totM${m}`).text(money(grand.months[m]));
            $('#totGrand').text(money(grand.grand));
        });
    }

    $('#btnEditPlan').on('click', function () {
        const fiscalYear = $('#filterFiscalYear').val();
        const officeName = $('#filterOffice').val();
        window.location.href = `{{ route('financial-plans.builder') }}?fiscal_year=${fiscalYear}&office_name=${encodeURIComponent(officeName)}`;
    });

    $('#btnLoad').on('click', loadTable);
    loadTable();
    $('[data-bs-toggle="tooltip"]').tooltip();

});
</script>
@endpush
