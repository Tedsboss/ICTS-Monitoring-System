@extends('layouts.app')
@section('content')
@php
    $selectedStaff = $staffs->firstWhere('id', $staffId);
@endphp
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Work Plan Builder'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>
<div class="px-4 pb-8 pt-4">
    <div class="mx-auto max-w-[1900px]">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('work-plans.index', ['fiscal_year' => $fiscalYear, 'staff_id' => $staffId]) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                <i class="fa fa-arrow-left"></i> Back to Work Plan
            </a>
            <span id="builderStatusBadge" class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">{{ $plan ? ucfirst($plan->status) : 'Draft' }}</span>
        </div>
        <div id="builderMessage" class="mb-3"></div>
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <section class="border-b border-slate-200 p-4">
                <div class="mb-4 flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="m-0 text-base font-bold text-slate-900">Plan Setup</h2>
                        <p class="mb-0 mt-1 text-xs text-slate-500">Select the fiscal year and Office/Staff before loading or editing the Work Plan.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span id="builderLoadingNote" class="hidden text-xs text-slate-500"><i class="fa fa-spinner fa-spin mr-1"></i> Loading plan...</span>
                        <button type="button" id="btnLoadPlan" class="inline-flex items-center gap-2 rounded-lg border border-sky-300 bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-700 transition hover:bg-sky-100">
                            <i class="fa fa-folder-open"></i> Load Plan
                        </button>
                    </div>
                </div>
                <div class="grid gap-3 md:grid-cols-[140px_minmax(280px,560px)]">
                    <div>
                        <label for="fiscalYear" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Fiscal Year</label>
                        <input type="number" id="fiscalYear" value="{{ $fiscalYear }}" min="2000" max="2100" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                    </div>
                    <div>
                        <label for="staffId" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Name of Office/Staff</label>
                        <select id="staffId" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                            <option value="">Select Office/Staff</option>
                            @foreach($staffs as $staff)
                                <option value="{{ $staff->id }}" {{ (int) $staffId === (int) $staff->id ? 'selected' : '' }}>{{ $staff->abbreviation ?: $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>
            <div id="lockedNotice" class="hidden border-b border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                <i class="fa fa-lock mr-1"></i> <span id="lockedNoticeText">This Work Plan is read-only.</span>
            </div>
            <section class="border-b border-slate-200 p-4">
                <div class="mb-3">
                    <h2 class="m-0 text-base font-bold text-slate-900">Signatories</h2>
                    <p class="mb-0 mt-1 text-xs text-slate-500">Enter the names and positions/designations that will appear on the Work Plan.</p>
                </div>
                <div class="grid gap-4 xl:grid-cols-2">
                    <div class="signatory-card">
                        <div class="signatory-title">Prepared by</div>
                        <div class="grid gap-2 md:grid-cols-2">
                            <input type="text" id="preparedBy" class="signatory-input builder-write-input" maxlength="150" placeholder="Name">
                            <input type="text" id="preparedByPosition" class="signatory-input builder-write-input" maxlength="150" placeholder="Position / Designation">
                        </div>
                    </div>
                    <div class="signatory-card">
                        <div class="signatory-title">Reviewed by</div>
                        <div class="grid gap-2 md:grid-cols-2">
                            <input type="text" id="reviewedBy" class="signatory-input builder-write-input" maxlength="150" placeholder="Name">
                            <input type="text" id="reviewedByPosition" class="signatory-input builder-write-input" maxlength="150" placeholder="Position / Designation">
                        </div>
                    </div>
                    <div class="signatory-card">
                        <div class="signatory-title">Recommended by</div>
                        <div class="grid gap-2 md:grid-cols-2">
                            <input type="text" id="recommendedBy" class="signatory-input builder-write-input" maxlength="150" placeholder="Name">
                            <input type="text" id="recommendedByPosition" class="signatory-input builder-write-input" maxlength="150" placeholder="Position / Designation">
                        </div>
                    </div>
                    <div class="signatory-card">
                        <div class="signatory-title">Approved by</div>
                        <div class="grid gap-2 md:grid-cols-2">
                            <input type="text" id="approvedBy" class="signatory-input builder-write-input" maxlength="150" placeholder="Name">
                            <input type="text" id="approvedByPosition" class="signatory-input builder-write-input" maxlength="150" placeholder="Position / Designation">
                        </div>
                    </div>
                </div>
            </section>
            <section class="p-4">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="m-0 text-base font-bold text-slate-900">Work Plan Rows</h2>
                            <span id="unsavedBadge" class="hidden rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-bold text-amber-800">Unsaved changes</span>
                        </div>
                        <p class="mb-0 mt-1 text-xs text-slate-500">Use Section Header and Sub Header for structure. Use Budget Line for classifications, activities, and monthly Target Outputs.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" class="builder-write-control add-row-button" data-row-type="header"><i class="fa fa-plus"></i> Section Header</button>
                        <button type="button" class="builder-write-control add-row-button" data-row-type="subheader"><i class="fa fa-plus"></i> Sub Header</button>
                        <button type="button" class="builder-write-control add-row-button" data-row-type="item"><i class="fa fa-plus"></i> Budget Line</button>
                        <button type="button" id="btnSavePlan" class="builder-write-control save-button"><i class="fa fa-save"></i> Save Entire Plan</button>
                    </div>
                </div>
                <div class="work-plan-builder-wrap rounded-xl border border-slate-200 bg-white">
                    <table id="builderTable" class="work-plan-builder-table mb-0 w-full border-collapse text-xs">
                        <thead>
                            <tr>
                                <th class="drag-column"></th>
                                <th class="classification-column">Program Classification (a)</th>
                                <th class="activity-column">Specific Activity/ies (b)</th>
                                <th class="targets-column">Target Output/s and Applicable Months</th>
                                <th class="actions-column">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="builderBody"></tbody>
                    </table>
                </div>
                <div id="emptyBuilderState" class="hidden border-x border-b border-slate-200 bg-slate-50 px-6 py-10 text-center">
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm"><i class="fa fa-list"></i></div>
                    <div class="mt-3 text-sm font-semibold text-slate-600">No Work Plan rows yet.</div>
                    <div class="mt-1 text-xs text-slate-500">Add a Section Header, Sub Header, or Budget Line to begin.</div>
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <button type="button" class="builder-write-control add-row-button" data-row-type="header"><i class="fa fa-plus"></i> Section Header</button>
                    <button type="button" class="builder-write-control add-row-button" data-row-type="subheader"><i class="fa fa-plus"></i> Sub Header</button>
                    <button type="button" class="builder-write-control add-row-button" data-row-type="item"><i class="fa fa-plus"></i> Budget Line</button>
                    <button type="button" id="btnSavePlanBottom" class="builder-write-control save-button ml-auto"><i class="fa fa-save"></i> Save Entire Plan</button>
                </div>
            </section>
        </div>
        <div class="mt-6">@include('layouts.footers.auth.footer')</div>
    </div>
</div>
<style>
.work-plan-builder-wrap {
    overflow-x: auto;
}
.work-plan-builder-table {
    min-width: 1400px;
    table-layout: fixed;
}
.work-plan-builder-table th,
.work-plan-builder-table td {
    border: 1px solid #cbd5e1;
    vertical-align: top;
}
.work-plan-builder-table th {
    background: #f8fafc;
    padding: 9px 7px;
    text-align: center;
    font-weight: 700;
    color: #334155;
}
.work-plan-builder-table td {
    padding: 7px;
    background: #fff;
}
.work-plan-builder-table .drag-column {
    width: 38px;
}
.work-plan-builder-table .classification-column {
    width: 300px;
}
.work-plan-builder-table .activity-column {
    width: 320px;
}
.work-plan-builder-table .targets-column {
    width: 620px;
}
.work-plan-builder-table .actions-column {
    width: 70px;
}
.builder-input,
.signatory-input {
    display: block;
    width: 100%;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    background: #fff;
    padding: 7px 8px;
    color: #334155;
    font-size: 12px;
    line-height: 1.4;
    outline: none;
}
.builder-input:focus,
.signatory-input:focus {
    border-color: #0ea5e9;
    box-shadow: 0 0 0 2px #e0f2fe;
}
.builder-input:disabled,
.signatory-input:disabled {
    background: #f1f5f9;
    color: #64748b;
    cursor: not-allowed;
}
.activity-input {
    min-height: 72px;
    resize: vertical;
    white-space: pre-wrap;
}
.target-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.target-entry {
    position: relative;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    background: #f8fafc;
    padding: 9px;
}
.target-output-input {
    min-height: 90px;
    height: auto;
    padding-right: 30px;
    resize: vertical;
    white-space: pre-wrap;
    overflow-wrap: break-word;
    line-height: 1.45;
}
.target-delete {
    position: absolute;
    top: 12px;
    right: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 19px;
    height: 19px;
    border: 0;
    border-radius: 50%;
    background: #fff1f2;
    color: #e11d48;
    font-size: 9px;
}
.target-delete:hover:not(:disabled) {
    background: #ffe4e6;
}
.target-month-heading {
    margin-top: 8px;
    font-size: 10px;
    font-weight: 700;
    color: #475569;
}
.target-month-actions {
    display: flex;
    gap: 6px;
    margin-top: 5px;
}
.target-month-action {
    border: 0;
    background: transparent;
    padding: 0;
    color: #0284c7;
    font-size: 10px;
    font-weight: 600;
}
.target-month-action:hover:not(:disabled) {
    text-decoration: underline;
}
.target-month-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 5px;
    margin-top: 7px;
}
.target-month-option {
    display: flex;
    align-items: center;
    gap: 4px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: #fff;
    padding: 5px 6px;
    font-size: 10px;
    color: #475569;
    cursor: pointer;
}
.target-month-option input {
    margin: 0;
}
.add-target {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    width: 100%;
    margin-top: 8px;
    border: 1px dashed #94a3b8;
    border-radius: 7px;
    background: #fff;
    padding: 7px;
    color: #64748b;
    font-size: 10px;
    font-weight: 600;
}
.add-target:hover:not(:disabled) {
    border-color: #38bdf8;
    background: #f0f9ff;
    color: #0369a1;
}
.delete-row {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 31px;
    height: 31px;
    border: 1px solid #fecdd3;
    border-radius: 8px;
    background: #fff1f2;
    color: #e11d48;
}
.delete-row:hover:not(:disabled) {
    background: #ffe4e6;
}
.drag-handle {
    cursor: grab;
    text-align: center;
    color: #94a3b8;
}
.drag-handle.locked {
    cursor: default;
    opacity: .5;
}
.drag-handle:not(.locked):active{cursor:grabbing}
#builderBody>tr.work-plan-row.dragging{opacity:.45}
#builderBody>tr.work-plan-row.drag-over{box-shadow:inset 0 2px 0 #0ea5e9}
.target-delete:disabled,
.add-target:disabled,
.target-month-action:disabled,
.delete-row:disabled {
    cursor: not-allowed;
    opacity: .45;
}
.signatory-card {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #f8fafc;
    padding: 12px;
}
.signatory-title {
    margin-bottom: 8px;
    font-size: 12px;
    font-weight: 700;
    color: #334155;
}
.add-row-button {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #fff;
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 600;
    color: #334155;
}
.add-row-button:hover:not(:disabled) {
    background: #f8fafc;
}
.save-button {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 0;
    border-radius: 8px;
    background: #059669;
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 700;
    color: #fff;
    box-shadow: 0 1px 2px rgba(0,0,0,.05);
}
.save-button:hover:not(:disabled) {
    background: #047857;
}
.structural-row td {
    background: #f8fafc;
}
.structural-row.header-row td {
    background: #e2e8f0;
}
.structural-title {
    min-height: 42px;
    font-weight: 700;
}
.header-row .structural-title {
    font-size: 13px;
    text-transform: uppercase;
}
.subheader-row .structural-title {
    font-size: 12px;
}
.row-type-label {
    display: inline-flex;
    margin-bottom: 6px;
    border-radius: 999px;
    padding: 2px 7px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.header-row .row-type-label {
    background: #cbd5e1;
    color: #334155;
}
.subheader-row .row-type-label {
    background: #e0f2fe;
    color: #0369a1;
}
</style>
@endsection
@push('js')
<script>
$(document).ready(function () {
    const MONTHS = @json($months);
    const CLASSIFICATIONS = @json($classifications);
    const INITIAL_PLAN = @json($plan);
    const csrfToken = '{{ csrf_token() }}';
    let currentPlan = INITIAL_PLAN;
    let isLocked = currentPlan ? !Boolean(@json($plan?->isEditable() ?? true)) : false;
    let isLoading = false;
    let hasUnsavedChanges = false;
    let rowCounter = 0;
    function esc(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    function newRowKey() {
        rowCounter += 1;
        return 'row-' + Date.now() + '-' + rowCounter;
    }
    function showMessage(message, type = 'success') {
        $('#builderMessage').html(`
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${esc(message)}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        window.scrollTo({top:0,behavior:'smooth'});
    }
    function getErrorMessage(xhr, fallback = 'Something went wrong.') {
        if (xhr?.responseJSON?.message) return xhr.responseJSON.message;
        if (xhr?.responseJSON?.errors) {
            const messages = Object.values(xhr.responseJSON.errors).flat();
            if (messages.length) return messages.join('\n');
        }
        return fallback;
    }
    function setDirty(value = true) {
        hasUnsavedChanges = value;
        $('#unsavedBadge').toggleClass('hidden', !value);
    }
    function setLoading(value) {
        isLoading = value;
        $('#builderLoadingNote').toggleClass('hidden', !value);
        $('#btnLoadPlan').prop('disabled', value);
        $('.builder-write-control').prop('disabled', value || isLocked);
    }
    function statusLabel(status) {
        if (!status) return 'Draft';
        return status.charAt(0).toUpperCase() + status.slice(1);
    }
    function applyLockState() {
        const status = currentPlan?.status || 'draft';
        const finalized = currentPlan?.finalized === 'yes';
        isLocked = Boolean(currentPlan && (finalized || !['draft','returned'].includes(status)));
        $('#builderStatusBadge').removeClass('bg-slate-100 text-slate-700 bg-cyan-100 text-cyan-700 bg-amber-100 text-amber-700 bg-sky-100 text-sky-700 bg-emerald-100 text-emerald-700');
        let badgeClass = 'bg-slate-100 text-slate-700';
        if (status === 'submitted') badgeClass = 'bg-cyan-100 text-cyan-700';
        else if (status === 'returned') badgeClass = 'bg-amber-100 text-amber-700';
        else if (status === 'approved') badgeClass = 'bg-sky-100 text-sky-700';
        else if (status === 'finalized' || finalized) badgeClass = 'bg-emerald-100 text-emerald-700';
        $('#builderStatusBadge').addClass(badgeClass).text(finalized ? 'Finalized / Locked' : statusLabel(status));
        $('#lockedNotice').toggleClass('hidden', !isLocked);
        if (isLocked) {
            $('#lockedNoticeText').text(finalized ? 'This Work Plan is finalized and locked. Reopen it from the Work Plan page before editing.' : 'This Work Plan is currently read-only.');
        }
        $('.builder-write-control').prop('disabled', isLocked || isLoading);
        $('.builder-write-input, #builderBody .builder-input, #builderBody .add-target, #builderBody .target-delete, #builderBody .target-month-checkbox, #builderBody .target-month-action, #builderBody .delete-row').prop('disabled', isLocked);
        $('#builderBody .drag-handle').toggleClass('locked', isLocked);
    }
    function classificationOptions(selectedValue = '') {
        const selected = Number(selectedValue || 0);
        const byParent = new Map();
        CLASSIFICATIONS.forEach(function (classification) {
            const parentId = classification.parent_id === null ? 0 : Number(classification.parent_id);
            if (!byParent.has(parentId)) byParent.set(parentId, []);
            byParent.get(parentId).push(classification);
        });
        const html = ['<option value="">Select Classification</option>'];
        function appendChildren(parentId, depth) {
            const children = byParent.get(parentId) || [];
            children.forEach(function (classification) {
                const id = Number(classification.id);
                const prefix = depth > 0 ? '— '.repeat(depth) : '';
                const code = classification.code ? `${esc(classification.code)} - ` : '';
                html.push(`<option value="${id}" ${id === selected ? 'selected' : ''}>${prefix}${code}${esc(classification.name)}</option>`);
                appendChildren(id, depth + 1);
            });
        }
        appendChildren(0, 0);
        return html.join('');
    }
    function autoResizeTextarea(element) {
        if (!element) return;
        element.style.height = 'auto';
        element.style.height = Math.max(element.scrollHeight, 110) + 'px';
    }
    function targetMonths(target = {}) {
        if (Array.isArray(target.months) && target.months.length) {
            return target.months
                .map(month => Number(month.month ?? month))
                .filter(month => month >= 1 && month <= 12);
        }
        const legacyMonth = Number(target.month || 0);
        return legacyMonth >= 1 && legacyMonth <= 12 ? [legacyMonth] : [];
    }
    function targetEntry(target = {}) {
        const selectedMonths = targetMonths(target);
        const monthOptions = Object.entries(MONTHS).map(([monthNumber, monthName]) => {
            const month = Number(monthNumber);
            const checked = selectedMonths.includes(month) ? 'checked' : '';
            const shortName = String(monthName).substring(0, 3);
            return `
                <label class="target-month-option" title="${esc(monthName)}">
                    <input type="checkbox" class="target-month-checkbox" value="${month}" ${checked} ${isLocked ? 'disabled' : ''}>
                    <span>${esc(shortName)}</span>
                </label>
            `;
        }).join('');
        return `
            <div class="target-entry" data-target-id="${esc(target.id ?? '')}">
                <textarea class="builder-input target-output-input" rows="4" placeholder="Enter Target Output..." ${isLocked ? 'disabled' : ''}>${esc(target.target_output ?? '')}</textarea>
                <button type="button" class="target-delete" title="Remove Target Output" ${isLocked ? 'disabled' : ''}><i class="fa fa-times"></i></button>
                <div class="target-month-heading">Applicable Months</div>
                <div class="target-month-actions">
                    <button type="button" class="target-month-action select-all-months" ${isLocked ? 'disabled' : ''}>Select All</button>
                    <button type="button" class="target-month-action clear-months" ${isLocked ? 'disabled' : ''}>Clear</button>
                </div>
                <div class="target-month-grid">${monthOptions}</div>
            </div>
        `;
    }
    function itemRow(item = {}) {
        const rowKey = item.row_key || newRowKey();
        const targets = Array.isArray(item.targets)
            ? item.targets.slice().sort((a,b) => Number(a.sort_order || 0) - Number(b.sort_order || 0) || Number(a.id || 0) - Number(b.id || 0))
            : [];
        return `
            <tr class="work-plan-row item-row" data-item-id="${esc(item.id ?? '')}" data-row-key="${esc(rowKey)}" data-row-type="item">
                <td class="drag-handle align-middle" title="Row order"><i class="fa fa-grip-vertical"></i></td>
                <td><select class="builder-input classification-input" ${isLocked ? 'disabled' : ''}>${classificationOptions(item.classification_id ?? '')}</select></td>
                <td><textarea class="builder-input activity-input" rows="3" placeholder="Specific Activity/ies" ${isLocked ? 'disabled' : ''}>${esc(item.specific_activity ?? '')}</textarea></td>
                <td class="targets-column">
                    <div class="target-list">${targets.map(target => targetEntry(target)).join('')}</div>
                    <button type="button" class="add-target" ${isLocked ? 'disabled' : ''}><i class="fa fa-plus"></i> Add Target Output</button>
                </td>
                <td class="text-center align-middle"><button type="button" class="delete-row" title="Delete Budget Line" ${isLocked ? 'disabled' : ''}><i class="fa fa-trash"></i></button></td>
            </tr>
        `;
    }
    function structuralRow(item = {}, type = 'header') {
        const rowKey = item.row_key || newRowKey();
        const isHeader = type === 'header';
        const label = isHeader ? 'Section Header' : 'Sub Header';
        return `
            <tr class="work-plan-row structural-row ${isHeader ? 'header-row' : 'subheader-row'}" data-item-id="${esc(item.id ?? '')}" data-row-key="${esc(rowKey)}" data-row-type="${type}">
                <td class="drag-handle align-middle" title="Row order"><i class="fa fa-grip-vertical"></i></td>
                <td colspan="3">
                    <span class="row-type-label">${label}</span>
                    <textarea class="builder-input structural-title" rows="2" placeholder="${label} title" ${isLocked ? 'disabled' : ''}>${esc(item.title ?? '')}</textarea>
                </td>
                <td class="text-center align-middle"><button type="button" class="delete-row" title="Delete ${label}" ${isLocked ? 'disabled' : ''}><i class="fa fa-trash"></i></button></td>
            </tr>
        `;
    }
    function renderRows(items = []) {
        const $body = $('#builderBody').empty();
        items.slice().sort(function (a,b) {
            const orderA = Number(a.sort_order || 0);
            const orderB = Number(b.sort_order || 0);
            return orderA !== orderB ? orderA - orderB : Number(a.id || 0) - Number(b.id || 0);
        }).forEach(function (item) {
            const type = item.row_type || 'item';
            $body.append(type === 'item' ? itemRow(item) : structuralRow(item, type));
        });
        $('#emptyBuilderState').toggleClass('hidden', items.length > 0);
        $body.find('.target-output-input').each(function () {
            autoResizeTextarea(this);
        });
        applyLockState();
    }
    function addRow(type) {
        if (isLocked || isLoading) return;
        if (type === 'item') $('#builderBody').append(itemRow({row_type:'item',targets:[]}));
        else $('#builderBody').append(structuralRow({row_type:type,title:''}, type));
        $('#emptyBuilderState').addClass('hidden');
        setDirty(true);
        $('#builderBody > tr').last().find('.builder-input').first().trigger('focus');
    }
    function enableRowDragging() {
        let dragSource = null;
        $('#builderBody').on('mousedown', '.drag-handle', function () {
            if (isLocked || isLoading) return;
            $(this).closest('tr.work-plan-row').attr('draggable', 'true');
        });
        $('#builderBody').on('dragstart', 'tr.work-plan-row', function (event) {
            if (isLocked || isLoading) {
                event.preventDefault();
                return;
            }
            dragSource = this;
            const transfer = event.originalEvent.dataTransfer;
            if (transfer) {
                transfer.effectAllowed = 'move';
                transfer.setData('text/plain', $(this).attr('data-row-key') || 'row');
            }
            $(this).addClass('dragging');
        });
        $('#builderBody').on('dragover', 'tr.work-plan-row', function (event) {
            if (isLocked || isLoading || !dragSource || dragSource === this) return;
            event.preventDefault();
            if (event.originalEvent.dataTransfer) event.originalEvent.dataTransfer.dropEffect = 'move';
            $('#builderBody>tr.work-plan-row').removeClass('drag-over');
            $(this).addClass('drag-over');
        });
        $('#builderBody').on('dragleave', 'tr.work-plan-row', function () {
            $(this).removeClass('drag-over');
        });
        $('#builderBody').on('drop', 'tr.work-plan-row', function (event) {
            if (isLocked || isLoading || !dragSource || dragSource === this) return;
            event.preventDefault();
            const $source = $(dragSource);
            const $target = $(this);
            if ($source.index() < $target.index()) $target.after($source);
            else $target.before($source);
            $('#builderBody>tr.work-plan-row').removeClass('drag-over');
            setDirty(true);
        });
        $('#builderBody').on('dragend', 'tr.work-plan-row', function () {
            $(this).removeClass('dragging').removeAttr('draggable');
            $('#builderBody>tr.work-plan-row').removeClass('drag-over');
            dragSource = null;
        });
        $('#builderBody').on('mouseup', '.drag-handle', function () {
            const $row = $(this).closest('tr.work-plan-row');
            if (!$row.hasClass('dragging')) $row.removeAttr('draggable');
        });
    }
    function collectSignatory() {
        return {
            prepared_by: String($('#preparedBy').val() || '').trim(),
            prepared_by_position: String($('#preparedByPosition').val() || '').trim(),
            reviewed_by: String($('#reviewedBy').val() || '').trim(),
            reviewed_by_position: String($('#reviewedByPosition').val() || '').trim(),
            recommended_by: String($('#recommendedBy').val() || '').trim(),
            recommended_by_position: String($('#recommendedByPosition').val() || '').trim(),
            approved_by: String($('#approvedBy').val() || '').trim(),
            approved_by_position: String($('#approvedByPosition').val() || '').trim()
        };
    }
    function renderSignatory(signatory = null) {
        const data = signatory || {};
        $('#preparedBy').val(data.prepared_by || '');
        $('#preparedByPosition').val(data.prepared_by_position || '');
        $('#reviewedBy').val(data.reviewed_by || '');
        $('#reviewedByPosition').val(data.reviewed_by_position || '');
        $('#recommendedBy').val(data.recommended_by || '');
        $('#recommendedByPosition').val(data.recommended_by_position || '');
        $('#approvedBy').val(data.approved_by || '');
        $('#approvedByPosition').val(data.approved_by_position || '');
    }
    function collectItems() {
        const items = [];
        let lastHeaderKey = null;
        let lastSubheaderKey = null;
        $('#builderBody > tr.work-plan-row').each(function (itemIndex) {
            const $row = $(this);
            const rowType = String($row.attr('data-row-type') || 'item');
            const rowKey = String($row.attr('data-row-key') || newRowKey());
            $row.attr('data-row-key', rowKey);
            let parentKey = null;
            if (rowType === 'header') {
                lastHeaderKey = rowKey;
                lastSubheaderKey = null;
            } else if (rowType === 'subheader') {
                parentKey = lastHeaderKey;
                lastSubheaderKey = rowKey;
            } else {
                parentKey = lastSubheaderKey || lastHeaderKey;
            }
            const data = {
                id: $row.attr('data-item-id') || null,
                row_key: rowKey,
                parent_key: parentKey,
                row_type: rowType,
                title: null,
                classification_id: null,
                specific_activity: null,
                sort_order: (itemIndex + 1) * 10,
                targets: []
            };
            if (rowType === 'item') {
                data.classification_id = $row.find('.classification-input').val() || null;
                data.specific_activity = String($row.find('.activity-input').val() || '').trim();
                $row.find('.target-entry').each(function (targetIndex) {
                    const $target = $(this);
                    const output = String($target.find('.target-output-input').val() || '').trim();
                    if (!output) return;
                    const months = [...new Set(
                        $target.find('.target-month-checkbox:checked').map(function () {
                            return Number($(this).val());
                        }).get()
                    )].sort((a, b) => a - b);
                    data.targets.push({
                        id: $target.attr('data-target-id') || null,
                        target_output: output,
                        months: months,
                        sort_order: (targetIndex + 1) * 10
                    });
                });
            } else {
                data.title = String($row.find('.structural-title').val() || '').trim();
            }
            items.push(data);
        });
        return items;
    }
    function validateBeforeSave(items) {
        if (!$('#staffId').val()) {
            showMessage('Select an Office/Staff first.', 'danger');
            return false;
        }
        for (let index = 0; index < items.length; index++) {
            const item = items[index];
            if (item.row_type === 'header' || item.row_type === 'subheader') {
                if (!item.title) {
                    showMessage(`${item.row_type === 'header' ? 'Section Header' : 'Sub Header'} ${index + 1} requires a title.`, 'danger');
                    return false;
                }
                continue;
            }
            if (!item.classification_id) {
                showMessage(`Select a classification for Budget Line ${index + 1}.`, 'danger');
                return false;
            }
            if (!item.specific_activity) {
                showMessage(`Enter the Specific Activity for Budget Line ${index + 1}.`, 'danger');
                return false;
            }
            for (let targetIndex = 0; targetIndex < item.targets.length; targetIndex++) {
                const target = item.targets[targetIndex];
                if (!Array.isArray(target.months) || target.months.length === 0) {
                    showMessage(`Select at least one month for Target Output ${targetIndex + 1} in Budget Line ${index + 1}.`, 'danger');
                    return false;
                }
            }
        }
        return true;
    }
    function savePlan() {
        if (isLocked || isLoading) return;
        const items = collectItems();
        if (!validateBeforeSave(items)) return;
        setLoading(true);
        $.ajax({
            url: '{{ route("work-plans.save") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                fiscal_year: Number($('#fiscalYear').val()),
                staff_id: Number($('#staffId').val()),
                signatory: collectSignatory(),
                items: items
            }
        }).done(function (response) {
            currentPlan = response.data || null;
            if (currentPlan) {
                renderSignatory(currentPlan.signatory || null);
                renderRows(currentPlan.items || []);
            }
            setDirty(false);
            applyLockState();
            showMessage(response.message || 'Work Plan saved successfully.');
            const url = new URL(window.location.href);
            url.searchParams.set('fiscal_year', $('#fiscalYear').val());
            url.searchParams.set('staff_id', $('#staffId').val());
            window.history.replaceState({}, '', url.toString());
        }).fail(function (xhr) {
            showMessage(getErrorMessage(xhr, 'Unable to save the Work Plan.'), 'danger');
        }).always(function () {
            setLoading(false);
        });
    }
    function loadPlan() {
        const fiscalYear = Number($('#fiscalYear').val());
        const staffId = Number($('#staffId').val());
        if (!staffId) {
            showMessage('Select an Office/Staff first.', 'danger');
            return;
        }
        if (hasUnsavedChanges && !window.confirm('You have unsaved changes. Load another Work Plan anyway?')) return;
        setLoading(true);
        $.getJSON('{{ route("work-plans.data") }}', {fiscal_year:fiscalYear,staff_id:staffId})
            .done(function (response) {
                currentPlan = response.data || null;
                if (currentPlan) {
                    renderSignatory(currentPlan.signatory || null);
                    renderRows(currentPlan.items || []);
                    showMessage('Work Plan loaded successfully.');
                } else {
                    currentPlan = null;
                    isLocked = false;
                    renderSignatory(null);
                    renderRows([]);
                    applyLockState();
                    showMessage('No existing Work Plan was found. You can create a new one.', 'info');
                }
                setDirty(false);
                const url = new URL(window.location.href);
                url.searchParams.set('fiscal_year', fiscalYear);
                url.searchParams.set('staff_id', staffId);
                window.history.replaceState({}, '', url.toString());
            })
            .fail(function (xhr) {
                showMessage(getErrorMessage(xhr, 'Unable to load the Work Plan.'), 'danger');
            })
            .always(function () {
                setLoading(false);
            });
    }
    $('.add-row-button').on('click', function () {
        addRow(String($(this).data('row-type') || 'item'));
    });
    $('#btnSavePlan, #btnSavePlanBottom').on('click', savePlan);
    $('#btnLoadPlan').on('click', loadPlan);
    $('#builderBody').on('click', '.add-target', function () {
        if (isLocked) return;
        const $cell = $(this).closest('.targets-column');
        $cell.find('.target-list').append(targetEntry({}));
        setDirty(true);
        const $input = $cell.find('.target-output-input').last();
        autoResizeTextarea($input[0]);
        $input.trigger('focus');
    });
    $('#builderBody').on('click', '.select-all-months', function () {
        if (isLocked) return;
        $(this).closest('.target-entry').find('.target-month-checkbox').prop('checked', true);
        setDirty(true);
    });
    $('#builderBody').on('click', '.clear-months', function () {
        if (isLocked) return;
        $(this).closest('.target-entry').find('.target-month-checkbox').prop('checked', false);
        setDirty(true);
    });
    $('#builderBody').on('click', '.target-delete', function () {
        if (isLocked) return;
        $(this).closest('.target-entry').remove();
        setDirty(true);
    });
    $('#builderBody').on('click', '.delete-row', function () {
        if (isLocked) return;
        const $row = $(this).closest('tr');
        const type = $row.attr('data-row-type') || 'item';
        const label = type === 'header' ? 'Section Header' : type === 'subheader' ? 'Sub Header' : 'Budget Line';
        if (!window.confirm(`Delete this ${label}?`)) return;
        $row.remove();
        $('#emptyBuilderState').toggleClass('hidden', $('#builderBody > tr').length > 0);
        setDirty(true);
    });
    $('#builderBody').on('input', '.target-output-input', function () {
        autoResizeTextarea(this);
    });
    $('#builderBody').on('input change', '.builder-input, .target-month-checkbox', function () {
        setDirty(true);
    });
    $('.builder-write-input').on('input change', function () {
        setDirty(true);
    });
    $('#fiscalYear, #staffId').on('change', function () {
        if (currentPlan) setDirty(true);
    });
    window.addEventListener('beforeunload', function (event) {
        if (!hasUnsavedChanges) return;
        event.preventDefault();
        event.returnValue = '';
    });
    enableRowDragging();
    renderSignatory(currentPlan?.signatory || null);
    renderRows(currentPlan?.items || []);
    applyLockState();
});
</script>
@endpush
