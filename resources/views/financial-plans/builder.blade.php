{{-- resources/views/financial-plans/builder.blade.php --}}
@extends('layouts.app')

@section('content')
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Financial Plan Builder'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>

<div class="container-fluid mt-4 px-4">

    <div class="mb-3">
        <a href="{{ route('financial-plans.index') }}" class="btn btn-sm btn-outline-dark px-3">
            <i class="fa fa-arrow-left me-1"></i> Back to Financial Plan
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <div class="row g-3 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-1">Fiscal Year</label>
                    <input type="number" id="fiscalYear" class="form-control form-control-sm" value="{{ $fiscalYear }}" style="width:110px;">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1">Name of Office/Staff</label>
                    <input type="text" id="officeName" class="form-control form-control-sm" style="width:340px;"
                           value="{{ $officeName }}" placeholder="e.g. Information and Communications Technology Staff (ICTS)">
                </div>
                <div class="col-auto ms-auto d-flex align-items-center gap-2">
                    <span id="builderLoadingNote" class="text-muted small">
                        <i class="fa fa-spinner fa-spin me-1"></i> Loading plan…
                    </span>
                    <a href="javascript:void(0)" id="btnLoadPlan" class="btn btn-sm btn-outline-primary">
                        <i class="fa fa-folder-open me-1"></i> Load Plan
                    </a>
                    <button type="button" id="btnAddHeader" class="btn btn-sm btn-outline-secondary" disabled>
                        <i class="fa fa-plus me-1"></i> Section Header
                    </button>
                    <button type="button" id="btnAddSubHeader" class="btn btn-sm btn-outline-secondary" disabled>
                        <i class="fa fa-plus me-1"></i> Sub Header
                    </button>
                    <button type="button" id="btnAddItem" class="btn btn-sm btn-outline-secondary" disabled>
                        <i class="fa fa-plus me-1"></i> Budget Line
                    </button>
                    <button type="button" id="btnSavePlan" class="btn btn-sm btn-success px-3" disabled>
                        <i class="fa fa-save me-1"></i> Save Entire Plan
                    </button>
                </div>
            </div>
        </div>

        {{-- Signatories for this WFP's printed form (Prepared/Reviewed/
             Recommended/Approved by). Saved per (fiscal_year, office_name)
             alongside the plan itself, and pre-filled from whatever was
             last saved for this office/FY. --}}
        <div class="card-body p-3 pb-0 border-bottom">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label mb-1 small text-muted">Prepared by</label>
                    <input type="text" id="sigPreparedBy" class="form-control form-control-sm mb-1" placeholder="Name">
                    <input type="text" id="sigPreparedByPosition" class="form-control form-control-sm" placeholder="Position/Designation">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1 small text-muted">Reviewed by</label>
                    <input type="text" id="sigReviewedBy" class="form-control form-control-sm mb-1" placeholder="Name">
                    <input type="text" id="sigReviewedByPosition" class="form-control form-control-sm" placeholder="Position/Designation">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1 small text-muted">Recommended by</label>
                    <input type="text" id="sigRecommendedBy" class="form-control form-control-sm mb-1" placeholder="Name">
                    <input type="text" id="sigRecommendedByPosition" class="form-control form-control-sm" placeholder="Position/Designation">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1 small text-muted">Approved by</label>
                    <input type="text" id="sigApprovedBy" class="form-control form-control-sm mb-1" placeholder="Name">
                    <input type="text" id="sigApprovedByPosition" class="form-control form-control-sm" placeholder="Position/Designation">
                </div>
            </div>
        </div>

        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle" style="font-size:0.75rem;" id="builderTable">
                    <thead class="text-center" style="background:#FFFF00;">
                        <tr>
                            <th style="min-width:200px;">Program Classification (a)</th>
                            <th style="min-width:100px;">PREXC Code (b)</th>
                            <th style="min-width:110px;">Staff/Unit (c)</th>
                            <th style="min-width:240px;">Specific Activity (d)</th>
                            <th style="min-width:90px;">Procurement Status (e)</th>
                            <th style="min-width:110px;">Expense Item</th>
                            <th style="min-width:100px;">Assigned Personnel</th>
                            <th style="min-width:100px;">MOOE</th>
                            <th style="min-width:100px;">Capital Outlay</th>
                            @foreach($months as $label)
                                <th style="min-width:80px; background:#FFFF00;">{{ $label }}</th>
                            @endforeach
                            <th style="min-width:60px;">Del</th>
                        </tr>
                    </thead>
                    <tbody id="builderBody"></tbody>
                </table>
            </div>

            <div class="d-flex gap-2 mt-2">
                <button type="button" id="btnAddHeader2" class="btn btn-sm btn-outline-secondary" disabled>
                    <i class="fa fa-plus me-1"></i> Section Header
                </button>
                <button type="button" id="btnAddSubHeader2" class="btn btn-sm btn-outline-secondary" disabled>
                    <i class="fa fa-plus me-1"></i> Sub Header
                </button>
                <button type="button" id="btnAddItem2" class="btn btn-sm btn-outline-secondary" disabled>
                    <i class="fa fa-plus me-1"></i> Budget Line
                </button>
                <button type="button" id="btnSavePlan2" class="btn btn-sm btn-success px-3 ms-auto" disabled>
                    <i class="fa fa-save me-1"></i> Save Entire Plan
                </button>
            </div>
        </div>
    </div>
</div>

@include('layouts.footers.auth.footer')
@endsection

@push('js')
<script>
$(document).ready(function () {

    const MONTHS = @json($months);
    let rowCounter = 0;

    let initialLoadDone = false;
    let hasUnsavedChanges = false;
    let activeLoadRequest = null;

    function esc(v) {
        return String(v ?? '').replace(/"/g, '&quot;');
    }

    function autoGrow(el) {
        el.style.height = 'auto';
        el.style.height = el.scrollHeight + 'px';
    }

    function setBuilderControlsEnabled(enabled) {
        $('#btnAddHeader, #btnAddHeader2, #btnAddSubHeader, #btnAddSubHeader2, #btnAddItem, #btnAddItem2, #btnSavePlan, #btnSavePlan2')
            .prop('disabled', !enabled);
        $('#builderLoadingNote').toggle(!enabled);
    }

    function fieldRow(r = {}) {
        rowCounter++;
        const rowId = r.id ?? '';
        const type  = r.row_type ?? 'item';
        const isItem = type === 'item';

        const rowClass = type === 'header'    ? 'table-secondary fw-semibold'
                        : type === 'subheader' ? 'table-light fw-semibold builder-subheader'
                        : '';

        let monthCells = '';
        for (let m = 1; m <= 12; m++) {
            const val = (r.months && r.months[m]) ? r.months[m] : 0;
            monthCells += `
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm month-input"
                           data-month="${m}" value="${isItem ? val : ''}" ${isItem ? '' : 'disabled'}>
                </td>`;
        }

        return `
            <tr data-row-id="${rowId}" data-row-type="${type}" class="${rowClass}">
                <td>
                    <select class="form-select form-select-sm row-type-select">
                        <option value="item" ${isItem ? 'selected' : ''}>Line</option>
                        <option value="subheader" ${type === 'subheader' ? 'selected' : ''}>Sub Header</option>
                        <option value="header" ${type === 'header' ? 'selected' : ''}>Header</option>
                    </select>
                    <input type="text" class="form-control form-control-sm mt-1 field-input" data-field="program_classification"
                           value="${r.program_classification ?? ''}" placeholder="Program classification">
                </td>
                <td><input type="text" class="form-control form-control-sm field-input" data-field="prexc_code" value="${r.prexc_code ?? ''}" ${isItem ? '' : 'disabled'}></td>
                <td><input type="text" class="form-control form-control-sm field-input" data-field="staff_unit_project" value="${r.staff_unit_project ?? ''}" ${isItem ? '' : 'disabled'}></td>
                <td>
                    <textarea class="form-control form-control-sm field-input specific-activity-input"
                              data-field="specific_activity" rows="1"
                              style="resize:vertical; overflow:hidden; min-height:31px; white-space:pre-wrap;"
                              ${isItem ? '' : 'disabled'}>${esc(r.specific_activity ?? '')}</textarea>
                </td>
                <td>
                    <select class="form-select form-select-sm field-input" data-field="procurement_status" ${isItem ? '' : 'disabled'}>
                        <option value="">--</option>
                        <option value="OK" ${r.procurement_status === 'OK' ? 'selected' : ''}>OK</option>
                    </select>
                </td>
                <td><input type="text" class="form-control form-control-sm field-input" data-field="expense_item" value="${r.expense_item ?? ''}" ${isItem ? '' : 'disabled'}></td>
                <td><input type="text" class="form-control form-control-sm field-input" data-field="assigned_personnel" value="${r.assigned_personnel ?? ''}" ${isItem ? '' : 'disabled'}></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm field-input" data-field="mooe" value="${r.mooe ?? 0}" ${isItem ? '' : 'disabled'}></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm field-input" data-field="capital_outlay" value="${r.capital_outlay ?? 0}" ${isItem ? '' : 'disabled'}></td>
                ${monthCells}
                <td class="text-center">
                    <a href="javascript:void(0)" class="text-danger btn-delete-row"><i class="fa fa-trash"></i></a>
                </td>
            </tr>
        `;
    }

    function addRow(type) {
        const $body = $('#builderBody');
        const $rows = $body.find('tr');
        let prefill = {};

        // Auto-carry PREXC / classification from the row above for new item rows
        if (type === 'item' && $rows.length > 0) {
            const $last = $rows.last();
            prefill.prexc_code = $last.find('[data-field="prexc_code"]').val() || '';
            prefill.program_classification = $last.find('[data-field="program_classification"]').val() || '';
        }

        prefill.row_type = type;
        const $newRow = $(fieldRow(prefill));
        $body.append($newRow);
        bindRowTypeChange();
        growSpecificActivityCells($newRow);
        hasUnsavedChanges = true;
    }

    function bindRowTypeChange() {
        $('.row-type-select').off('change').on('change', function () {
            const $tr = $(this).closest('tr');
            const type = $(this).val();
            const isItem = type === 'item';

            $tr.attr('data-row-type', type);
            $tr.removeClass('table-secondary fw-semibold table-light builder-subheader');
            if (type === 'header') $tr.addClass('table-secondary fw-semibold');
            if (type === 'subheader') $tr.addClass('table-light fw-semibold builder-subheader');

            $tr.find('[data-field]:not([data-field="program_classification"]), .month-input')
                .prop('disabled', !isItem);
        });
    }

    function growSpecificActivityCells($scope) {
        $scope.find('.specific-activity-input').each(function () {
            autoGrow(this);
        });
    }

    function renderRows(rows) {
        const $body = $('#builderBody').empty();
        rows.forEach(r => $body.append(fieldRow(r)));
        bindRowTypeChange();
        growSpecificActivityCells($body);
    }

    function loadSignatories() {
        const fiscalYear = $('#fiscalYear').val();
        const officeName = $('#officeName').val();

        $.getJSON(
            '{{ route("financial-plans.signatories") }}',
            { fiscal_year: fiscalYear, office_name: officeName },
            function (sig) {
                $('#sigPreparedBy').val(sig.prepared_by || '');
                $('#sigPreparedByPosition').val(sig.prepared_by_position || '');
                $('#sigReviewedBy').val(sig.reviewed_by || '');
                $('#sigReviewedByPosition').val(sig.reviewed_by_position || '');
                $('#sigRecommendedBy').val(sig.recommended_by || '');
                $('#sigRecommendedByPosition').val(sig.recommended_by_position || '');
                $('#sigApprovedBy').val(sig.approved_by || '');
                $('#sigApprovedByPosition').val(sig.approved_by_position || '');
            }
        );
    }

    function saveSignatories() {
        return $.ajax({
            url: '{{ route("financial-plans.signatories.save") }}',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                fiscal_year: $('#fiscalYear').val(),
                office_name: $('#officeName').val(),
                prepared_by: $('#sigPreparedBy').val(),
                prepared_by_position: $('#sigPreparedByPosition').val(),
                reviewed_by: $('#sigReviewedBy').val(),
                reviewed_by_position: $('#sigReviewedByPosition').val(),
                recommended_by: $('#sigRecommendedBy').val(),
                recommended_by_position: $('#sigRecommendedByPosition').val(),
                approved_by: $('#sigApprovedBy').val(),
                approved_by_position: $('#sigApprovedByPosition').val(),
            }),
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
    }

    function loadPlan(isManualReload = false) {
        if (isManualReload && hasUnsavedChanges) {
            const proceed = confirm(
                'You have unsaved changes in this builder. Loading will discard them. Continue?'
            );
            if (!proceed) {
                return;
            }
        }

        if (activeLoadRequest && activeLoadRequest.readyState !== 4) {
            activeLoadRequest.abort();
        }

        setBuilderControlsEnabled(false);

        const fiscalYear = $('#fiscalYear').val();
        const officeName = $('#officeName').val();

        loadSignatories();

        activeLoadRequest = $.getJSON(
            '{{ route("financial-plans.data") }}',
            { fiscal_year: fiscalYear, office_name: officeName },
            function (rows) {
                renderRows(rows);
                if (rows.length === 0) {
                    addRow('header');
                    addRow('item');
                }
                hasUnsavedChanges = false;
            }
        ).always(function () {
            initialLoadDone = true;
            setBuilderControlsEnabled(true);
        });
    }

    function collectPayload() {
        const rows = [];

        $('#builderBody tr').each(function () {
            const $tr = $(this);
            const rowType = $tr.find('.row-type-select').val();
            const row = {
                id: $tr.data('row-id') || null,
                row_type: rowType,
                months: {},
            };

            $tr.find('[data-field]').each(function () {
                const field = $(this).data('field');
                const val = $(this).val();

                if (field === 'mooe' || field === 'capital_outlay') {
                    row[field] = (val === '' || val === null) ? 0 : val;
                } else {
                    row[field] = val;
                }
            });

            $tr.find('.month-input').each(function () {
                row.months[$(this).data('month')] = $(this).val() || 0;
            });

            rows.push(row);
        });

        return {
            fiscal_year: $('#fiscalYear').val(),
            office_name: $('#officeName').val(),
            rows: rows,
        };
    }

    function savePlan() {

        const payload = collectPayload();

        if (!payload.office_name.trim()) {
            alert('Name of Office/Staff is required.');
            return;
        }

        $('#btnSavePlan, #btnSavePlan2')
            .prop('disabled', true)
            .text('Saving...');

        $.ajax({
            url: '{{ route("financial-plans.save") }}',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),

            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },

            success: function (response) {
                if (response.success) {
                    hasUnsavedChanges = false;
                    saveSignatories().always(function () {
                        window.location.href = response.redirect;
                    });
                } else {
                    alert(response.message || 'Failed to save.');
                    $('#btnSavePlan, #btnSavePlan2')
                        .prop('disabled', false)
                        .html('<i class="fa fa-save me-1"></i> Save Entire Plan');
                }
            },

            error: function (xhr) {
                console.log(xhr.responseText);
                alert('Failed to save.');
                $('#btnSavePlan, #btnSavePlan2')
                    .prop('disabled', false)
                    .html('<i class="fa fa-save me-1"></i> Save Entire Plan');
            }
        });

    }

    $('#builderBody').on('click', '.btn-delete-row', function () {
        $(this).closest('tr').remove();
        hasUnsavedChanges = true;
    });

    $('#builderBody').on('input change', '.field-input, .month-input', function () {
        hasUnsavedChanges = true;

        if ($(this).hasClass('specific-activity-input')) {
            autoGrow(this);
        }
    });

    $('#sigPreparedBy, #sigPreparedByPosition, #sigReviewedBy, #sigReviewedByPosition, #sigRecommendedBy, #sigRecommendedByPosition, #sigApprovedBy, #sigApprovedByPosition').on('input', function () {
        hasUnsavedChanges = true;
    });

    $('#btnAddHeader, #btnAddHeader2').on('click', () => addRow('header'));
    $('#btnAddSubHeader, #btnAddSubHeader2').on('click', () => addRow('subheader'));
    $('#btnAddItem, #btnAddItem2').on('click', () => addRow('item'));
    $('#btnSavePlan, #btnSavePlan2').on('click', savePlan);
    $('#btnLoadPlan').on('click', () => loadPlan(true));

    window.addEventListener('beforeunload', function (e) {
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    loadPlan(false);
});
</script>
@endpush

<style>
    /* Sub Header rows nest visually under their parent Header row. */
    #builderTable .builder-subheader td:first-child {
        padding-left: 24px;
    }
</style>
