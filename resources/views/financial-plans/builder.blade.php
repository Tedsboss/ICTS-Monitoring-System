@extends('layouts.app')
@section('content')
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Financial Plan Builder'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>
<div class="px-4 pb-8 pt-4">
    <div class="mx-auto max-w-[1800px]">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('financial-plans.index', ['fiscal_year' => $fiscalYear, 'office_name' => $officeName]) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                <i class="fa fa-arrow-left"></i>
                Back to Financial Plan
            </a>
            <span id="builderStatusBadge"
                  class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">
                Draft
            </span>
        </div>
        <div id="builderMessage" class="mb-3"></div>
        <div id="submissionReadinessPanel"
             class="mb-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="m-0 text-sm font-bold text-slate-900">Submission Readiness</h2>
                        <span id="submissionReadinessBadge"
                              class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-700">
                            Checking...
                        </span>
                    </div>
                    <p class="mb-0 mt-1 text-xs text-slate-500">
                        Drafts may still be saved. These checks show what must be completed before submission.
                    </p>
                </div>
                <div class="text-right">
                    <div id="submissionReadinessSummary" class="text-xs font-semibold text-slate-700">
                        Checking Financial Plan...
                    </div>
                    <div id="submissionReadinessHint" class="mt-1 text-[11px] text-slate-500"></div>
                </div>
            </div>
            <div id="submissionReadinessIssues"
                 class="mt-3 grid gap-2 text-xs sm:grid-cols-2 xl:grid-cols-3"></div>
        </div>
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <section class="border-b border-slate-200 p-4">
                <div class="mb-4 flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="m-0 text-base font-bold text-slate-900">Plan Setup</h2>
                        <p class="mb-0 mt-1 text-xs text-slate-500">Select the fiscal year and office/staff before loading or editing the plan.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span id="builderLoadingNote" class="hidden text-xs text-slate-500">
                            <i class="fa fa-spinner fa-spin mr-1"></i> Loading plan...
                        </span>
                        <button type="button" id="btnLoadPlan"
                                class="inline-flex items-center gap-2 rounded-lg border border-sky-300 bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-700 transition hover:bg-sky-100">
                            <i class="fa fa-folder-open"></i>
                            Load Plan
                        </button>
                    </div>
                </div>
                <div class="grid gap-3 md:grid-cols-[140px_minmax(280px,560px)]">
                    <div>
                        <label for="fiscalYear" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Fiscal Year</label>
                        <input type="number" id="fiscalYear" value="{{ $fiscalYear }}" min="2000" max="2100"
                               class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                    </div>
                    <div>
                        <label for="officeName" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Name of Office/Staff</label>
                        <input type="text" id="officeName" value="{{ $officeName }}" maxlength="150" placeholder="Name of Office/Staff"
                               class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                    </div>
                </div>
            </section>
            <div id="lockedNotice" class="hidden border-b border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                <i class="fa fa-lock mr-1"></i>
                <span id="lockedNoticeText">This plan is read-only.</span>
            </div>
            <section class="border-b border-slate-200 p-4">
                <div class="mb-3">
                    <h2 class="m-0 text-base font-bold text-slate-900">Signatories</h2>
                    <p class="mb-0 mt-1 text-xs text-slate-500">Names and designations that will appear on the Financial Plan.</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        ['Prepared by', 'sigPreparedBy', 'sigPreparedByPosition'],
                        ['Reviewed by', 'sigReviewedBy', 'sigReviewedByPosition'],
                        ['Recommended by', 'sigRecommendedBy', 'sigRecommendedByPosition'],
                        ['Approved by', 'sigApprovedBy', 'sigApprovedByPosition'],
                    ] as [$label, $nameId, $positionId])
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-600">{{ $label }}</label>
                            <input type="text" id="{{ $nameId }}" maxlength="150" placeholder="Name"
                                   class="builder-write-field mb-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                            <input type="text" id="{{ $positionId }}" maxlength="150" placeholder="Position/Designation"
                                   class="builder-write-field w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                        </div>
                    @endforeach
                </div>
            </section>
            <section class="border-b border-slate-200 p-4">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="m-0 text-base font-bold text-slate-900">Allocation vs Programmed</h2>
                        <p class="mb-0 mt-1 text-xs text-slate-500">Allocation balances are based on the Allocation Types configured for this Staff/Office.</p>
                    </div>
                    <button type="button" id="btnSaveAllocation"
                            class="builder-write-control inline-flex items-center gap-2 rounded-lg border border-sky-300 bg-white px-3 py-2 text-xs font-semibold text-sky-700 transition hover:bg-sky-50">
                        <i class="fa fa-save"></i>
                        Save Allocation
                    </button>
                </div>
                <div id="allocationCards" class="grid gap-3 lg:grid-cols-3">
                    @forelse(($allocationTypeOptions ?? []) as $allocationType)
                        @foreach(['mooe' => 'MOOE', 'capital_outlay' => 'Capital Outlay'] as $category => $categoryLabel)
                            @if(($category === 'mooe' && $allocationType->allows_mooe) || ($category === 'capital_outlay' && $allocationType->allows_capital_outlay))
                                <div class="allocation-card rounded-xl border border-slate-200 bg-slate-50 p-3"
                                     data-allocation-type-id="{{ $allocationType->id }}"
                                     data-allocation-code="{{ strtolower($allocationType->code) }}"
                                     data-expense-category="{{ $category }}">
                                    <div class="mb-2">
                                        <div class="text-xs font-bold uppercase tracking-wide text-slate-700">{{ $categoryLabel }}</div>
                                        <div class="mt-0.5 text-[11px] text-slate-500">{{ $allocationType->name }}</div>
                                    </div>
                                    <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Allocation</label>
                                    <input type="number" min="0" step="0.01" value="0"
                                           class="allocation-amount-input builder-write-field w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                                    <div class="mt-3 grid grid-cols-2 gap-2">
                                        <div class="rounded-lg border border-slate-200 bg-white px-3 py-2">
                                            <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Original Programmed</div>
                                            <div class="mt-1 text-sm font-bold text-slate-700" data-role="original-programmed">0.00</div>
                                        </div>
                                        <div class="rounded-lg border border-slate-200 bg-white px-3 py-2">
                                            <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Effective Programmed</div>
                                            <div class="mt-1 text-sm font-bold text-slate-800" data-role="programmed">0.00</div>
                                        </div>
                                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2">
                                            <div class="text-[10px] font-semibold uppercase tracking-wide text-emerald-700">Savings</div>
                                            <div class="mt-1 text-sm font-bold text-emerald-800" data-role="savings">0.00</div>
                                        </div>
                                        <div class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-2">
                                            <div class="text-[10px] font-semibold uppercase tracking-wide text-sky-700">Remaining Balance</div>
                                            <div class="mt-1 text-sm font-bold text-sky-900" data-role="balance">0.00</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @empty
                        <div class="lg:col-span-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            <i class="fa fa-exclamation-triangle mr-1"></i>
                            No active Allocation Types are configured for this Staff/Office.
                        </div>
                    @endforelse
                </div>
            </section>
            <section class="p-4">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="m-0 text-base font-bold text-slate-900">Work and Financial Plan Rows</h2>
                            <span id="unsavedBadge" class="hidden rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-bold text-amber-800">Unsaved changes</span>
                        </div>
                        <p class="mb-0 mt-1 text-xs text-slate-500">Drag rows using the grip icon to change their order.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" id="btnAddHeader" class="builder-write-control inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                            <i class="fa fa-plus"></i> Section Header
                        </button>
                        <button type="button" id="btnAddSubHeader" class="builder-write-control inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                            <i class="fa fa-plus"></i> Sub Header
                        </button>
                        <button type="button" id="btnAddItem" class="builder-write-control inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                            <i class="fa fa-plus"></i> Budget Line
                        </button>
                        <button type="button" id="btnSavePlan" class="builder-write-control inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700">
                            <i class="fa fa-save"></i> Save Entire Plan
                        </button>
                    </div>
                </div>
                <div class="builder-table-wrap rounded-xl border border-slate-200 bg-white">
                    <table id="builderTable" class="mb-0 w-full border-collapse text-xs">
                        <thead class="text-center">
                            <tr>
                                <th style="width:32px;"></th>
                                <th style="width:44px;"></th>
                                <th style="min-width:210px;">Program Classification (a)</th>
                                <th style="min-width:120px;">PREXC Code (b)</th>
                                <th style="min-width:125px;">Allocation Type</th>
                                <th style="min-width:140px;">Staff/Unit (c)</th>
                                <th style="min-width:260px;">Specific Activity (d)</th>
                                <th style="min-width:130px;">Expense Item</th>
                                <th style="min-width:130px;">Assigned Personnel</th>
                                <th style="min-width:110px;">MOOE</th>
                                <th style="min-width:110px;">Capital Outlay</th>
                                <th style="min-width:120px;">Contract Amount</th>
                                <th style="min-width:125px;">Financial Target</th>
                                @foreach($months as $label)
                                    <th style="min-width:90px;">{{ $label }}</th>
                                @endforeach
                                <th style="min-width:90px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="builderBody"></tbody>
                    </table>
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <button type="button" id="btnAddHeader2" class="builder-write-control inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        <i class="fa fa-plus"></i> Section Header
                    </button>
                    <button type="button" id="btnAddSubHeader2" class="builder-write-control inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        <i class="fa fa-plus"></i> Sub Header
                    </button>
                    <button type="button" id="btnAddItem2" class="builder-write-control inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        <i class="fa fa-plus"></i> Budget Line
                    </button>
                    <button type="button" id="btnSavePlan2" class="builder-write-control ml-auto inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700">
                        <i class="fa fa-save"></i> Save Entire Plan
                    </button>
                </div>
            </section>
        </div>
    </div>
</div>
<style>
    .builder-insert {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        color: #475569;
    }
    .builder-insert:hover:not(:disabled) {
        background: #f0f9ff;
        border-color: #7dd3fc;
        color: #0369a1;
    }
    .builder-insert:disabled {
        opacity: .45;
        cursor: not-allowed;
    }
</style>
<div id="targetDistributionModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl">
        <div class="flex items-start justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h3 class="m-0 text-base font-bold text-slate-900">Distribute Financial Target</h3>
                <p class="mb-0 mt-1 text-xs text-slate-500">Schedule the effective Financial Target across all or selected months.</p>
            </div>
            <button type="button" id="btnCloseTargetDistribution" class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-slate-500 hover:bg-slate-50">
                <i class="fa fa-times"></i>
            </button>
        </div>
        <div class="space-y-4 p-5">
            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <div class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Programmed</div>
                    <div id="distributionOriginalBudget" class="mt-1 text-base font-bold text-slate-800">0.00</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <div class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Contract Amount</div>
                    <div id="distributionContractAmount" class="mt-1 text-base font-bold text-slate-800">—</div>
                </div>
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3">
                    <div class="text-[10px] font-bold uppercase tracking-wide text-emerald-700">Financial Target</div>
                    <div id="distributionEffectiveBudget" class="mt-1 text-base font-bold text-emerald-800">0.00</div>
                </div>
            </div>
            <div>
                <div class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-600">Distribution</div>
                <div class="grid gap-2 sm:grid-cols-3">
                    <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 p-3 text-sm">
                        <input type="radio" name="targetDistributionMode" value="all" checked>
                        <span>Equal — All 12 Months</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 p-3 text-sm">
                        <input type="radio" name="targetDistributionMode" value="selected">
                        <span>Equal — Selected Months</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 p-3 text-sm">
                        <input type="radio" name="targetDistributionMode" value="manual">
                        <span>Manual</span>
                    </label>
                </div>
            </div>
            <div id="distributionMonthSelector" class="hidden">
                <div class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-600">Select Months</div>
                <div class="grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-6">
                    @foreach($months as $monthNumber => $label)
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-xs">
                            <input type="checkbox" class="distribution-month-checkbox" value="{{ $monthNumber }}">
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-xs text-sky-800">
                Any centavo rounding difference is placed in the last selected month so the total always matches the Financial Target.
            </div>
        </div>
        <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
            <button type="button" id="btnCancelTargetDistribution" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700">Cancel</button>
            <button type="button" id="btnApplyTargetDistribution" class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-bold text-white">Apply Distribution</button>
        </div>
    </div>
</div>
@include('layouts.footers.auth.footer')
@endsection
@push('js')
<script>
$(document).ready(function () {
    // Assigned Personnel comes from the staff personnel master list.
    // Personnel do not need a DIREK user account to appear here.
    const PERSONNEL_OPTIONS = @json($personnelOptions ?? []);
    // Controlled Program Classification / PREXC master list.
    const PREXC_CLASSIFICATIONS = @json($prexcClassifications ?? []);
    // Allocation Types are configured for the current Staff/Office.
    // The Builder must not assume that every office uses MITHI/NINP.
    const ALLOCATION_TYPE_OPTIONS = @json($allocationTypeOptions ?? []);
    function classificationOptions(selectedValue = '') {
        const selected = String(selectedValue ?? '').trim();
        const groups = new Map();
        let selectedFound = false;
        PREXC_CLASSIFICATIONS.forEach(item => {
            const classification = String(item.classification_name ?? '').trim();
            const code = String(item.prexc_code ?? '').trim();
            if (!classification || !code) return;
            const groupParts = [
                String(item.classification_group ?? '').trim(),
                String(item.program_name ?? '').trim()
            ].filter(Boolean);
            const groupLabel = groupParts.join(' — ') || 'Program Classification';
            if (!groups.has(groupLabel)) groups.set(groupLabel, []);
            groups.get(groupLabel).push({ classification, code });
            if (classification === selected) selectedFound = true;
        });
        const html = ['<option value="">Select Program Classification</option>'];
        // Preserve a legacy classification even when it is no longer active in the master list.
        if (selected && !selectedFound) {
            html.push(`<option value="${esc(selected)}" data-prexc="" selected>${esc(selected)} (Legacy)</option>`);
        }
        groups.forEach((items, groupLabel) => {
            html.push(`<optgroup label="${esc(groupLabel)}">`);
            items.forEach(item => {
                html.push(`<option value="${esc(item.classification)}" data-prexc="${esc(item.code)}" ${item.classification === selected ? 'selected' : ''}>${esc(item.classification)}</option>`);
            });
            html.push('</optgroup>');
        });
        return html.join('');
    }
    function programClassificationControl(row = {}, isItem = true, disabled = false) {
        const classification = String(row.program_classification ?? '');
        if (!isItem) {
            return `
                <input type="text"
                       class="builder-input field-input program-classification-input"
                       data-field="program_classification"
                       maxlength="500"
                       value="${esc(classification)}"
                       placeholder="Section / sub header"
                       ${disabled ? 'disabled' : ''}>
            `;
        }
        return `
            <select class="builder-input field-input program-classification-input"
                    data-field="program_classification"
                    ${disabled ? 'disabled' : ''}>
                ${classificationOptions(classification)}
            </select>
        `;
    }
    // Allocation source is independent from Program Classification / PREXC.
    // Draft rows may remain unclassified until strict Submit validation is enabled.
    function allocationTypeOptions(selectedValue = '') {
        const selected = String(selectedValue ?? '').trim().toLowerCase();
        const html = ['<option value="">Select Allocation Type</option>'];
        let selectedFound = false;
        ALLOCATION_TYPE_OPTIONS.forEach(item => {
            const code = String(item.code ?? '').trim().toLowerCase();
            const name = String(item.name ?? '').trim();
            if (!code || !name) return;
            const isSelected = code === selected;
            if (isSelected) {
                selectedFound = true;
            }
            html.push(
                `<option value="${esc(code)}"
                         data-allows-mooe="${item.allows_mooe ? '1' : '0'}"
                         data-allows-capital-outlay="${item.allows_capital_outlay ? '1' : '0'}"
                         ${isSelected ? 'selected' : ''}>${esc(name)}</option>`
            );
        });
        // Preserve an existing legacy value so opening an older plan does not
        // silently erase its stored Allocation Type.
        if (selected && !selectedFound) {
            html.push(
                `<option value="${esc(selected)}" selected>${esc(selected)} (Legacy / Unavailable)</option>`
            );
        }
        return html.join('');
    }
    function allocationTypeControl(row = {}, isItem = true, disabled = false) {
        const hasConfiguredTypes = ALLOCATION_TYPE_OPTIONS.length > 0;
        const title = hasConfiguredTypes
            ? 'Select which allocation source funds this budget line'
            : 'No active Allocation Types are configured for this Staff/Office';
        return `
            <select class="builder-input field-input allocation-type-input"
                    data-field="allocation_type"
                    title="${esc(title)}"
                    ${isItem && !disabled && hasConfiguredTypes ? '' : 'disabled'}>
                ${allocationTypeOptions(row.allocation_type ?? '')}
            </select>
        `;
    }
    // Convert the existing comma-separated value into selected personnel names.
    function selectedPersonnelNames(value = '') {
        return String(value ?? '')
            .split(',')
            .map(name => name.trim())
            .filter(Boolean);
    }
    // Build the Assigned Personnel multi-select and preserve legacy names
    // that may no longer exist in the active personnel master list.
    function assignedPersonnelControl(value = '', disabled = false) {
        const selected = selectedPersonnelNames(value);
        const options = PERSONNEL_OPTIONS.map(personnel => ({
            name: String(personnel.name ?? '').trim(),
            position: String(personnel.position ?? '').trim(),
        })).filter(personnel => personnel.name !== '');
        selected.forEach(name => {
            if (!options.some(personnel => personnel.name === name)) {
                options.push({ name, position: '' });
            }
        });
        options.sort((a, b) => a.name.localeCompare(b.name));
        const selectedText = selected.length
            ? selected.map(name => `<span class="personnel-chip">${esc(name)}</span>`).join('')
            : '<span class="personnel-placeholder">Select personnel...</span>';
        const optionHtml = options.length
            ? options.map(personnel => {
                const checked = selected.includes(personnel.name) ? 'checked' : '';
                const position = personnel.position
                    ? `<span class="personnel-option-position">${esc(personnel.position)}</span>`
                    : '';
                return `
                    <label class="personnel-option" data-personnel-search="${esc((personnel.name + ' ' + personnel.position).toLowerCase())}">
                        <input type="checkbox" class="personnel-checkbox" value="${esc(personnel.name)}" ${checked} ${disabled ? 'disabled' : ''}>
                        <span>
                            <span class="personnel-option-name">${esc(personnel.name)}</span>
                            ${position}
                        </span>
                    </label>
                `;
            }).join('')
            : '<div class="personnel-empty">No active personnel found for this staff.</div>';
        return `
            <div class="personnel-multiselect">
                <input type="hidden" class="field-input assigned-personnel-value" data-field="assigned_personnel"
                       value="${esc(selected.join(', '))}" ${disabled ? 'disabled' : ''}>
                <button type="button" class="personnel-toggle" ${disabled ? 'disabled' : ''}>
                    <span class="personnel-selected">${selectedText}</span>
                    <i class="fa fa-chevron-down personnel-chevron"></i>
                </button>
                <div class="personnel-menu hidden">
                    <div class="personnel-search-wrap">
                        <i class="fa fa-search"></i>
                        <input type="text" class="personnel-search" placeholder="Search personnel..." ${disabled ? 'disabled' : ''}>
                    </div>
                    <div class="personnel-options">${optionHtml}</div>
                </div>
            </div>
        `;
    }
    function refreshAssignedPersonnel($control) {
        const names = $control.find('.personnel-checkbox:checked').map(function () {
            return $(this).val();
        }).get();
        $control.find('.assigned-personnel-value').val(names.join(', ')).trigger('change');
        const selectedHtml = names.length
            ? names.map(name => `<span class="personnel-chip">${esc(name)}</span>`).join('')
            : '<span class="personnel-placeholder">Select personnel...</span>';
        $control.find('.personnel-selected').html(selectedHtml);
    }
    // Expense Item dropdown options
    const EXPENSE_ITEMS = [
        'ICT Equipment',
        'ICT Software Subscription',
        'ICT Supplies',
        'ICT Training Expense',
        'Internet Subscription',
        'Local Travel',
        'Other Professional Services',
        'Rent/Lease Expense',
        'Repair and Maintenance of ICT Equipment',
        'Representation Expenses',
        'Training Expenses',
    ];
    // Build Expense Item options and preserve any existing legacy value
    function expenseItemOptions(selectedValue = '') {
        const selected = String(selectedValue ?? '').trim();
        const options = [...EXPENSE_ITEMS];
        if (selected && !options.includes(selected)) {
            options.push(selected);
            options.sort((a, b) => a.localeCompare(b));
        }
        return [
            '<option value="">Select Expense Item</option>',
            ...options.map(item => `
                <option value="${esc(item)}" ${item === selected ? 'selected' : ''}>${esc(item)}</option>
            `)
        ].join('');
    }
    let activeLoadRequest = null;
    let hasUnsavedChanges = false;
    let allocationDirty = false;
    let isLocked = false;
    let currentWorkflowStatus = 'draft';
    let currentFinalized = false;
    let isLoading = false;
    let activeDistributionRow = null;
    // Escape values placed inside HTML attributes
    function esc(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    // Show a Bootstrap message
    function showMessage(message, type = 'success') {
        $('#builderMessage').html(`
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${esc(message)}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
    }
    // Read the best available server error message
    function getErrorMessage(xhr, fallback = 'Something went wrong.') {
        if (xhr?.responseJSON?.message) {
            return xhr.responseJSON.message;
        }
        if (xhr?.responseJSON?.errors) {
            const messages = Object.values(xhr.responseJSON.errors).flat();
            if (messages.length) {
                return messages.join('\n');
            }
        }
        return fallback;
    }
    // Mark the page as changed
    function refreshUnsavedBadge() {
        $('#unsavedBadge').toggleClass('hidden', !(hasUnsavedChanges || allocationDirty));
    }
    function setDirty(value = true) {
        hasUnsavedChanges = value;
        refreshUnsavedBadge();
    }
    // Format money
    function fmtNum(number) {
        return Number(number || 0).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    // Resize long activity text automatically
    function autoGrow(element) {
        element.style.height = 'auto';
        element.style.height = `${element.scrollHeight}px`;
    }
    // Apply contract amount proportionally to MOOE and CO
    function effectiveAmounts(mooe, co, contractAmount) {
        if (contractAmount === null || Number.isNaN(contractAmount)) {
            return [mooe, co];
        }
        const total = mooe + co;
        if (total <= 0) {
            return [0, 0];
        }
        return [
            contractAmount * (mooe / total),
            contractAmount * (co / total)
        ];
    }
    // Financial Target rule:
    // No contract = MOOE + CO.
    // With contract = Contract Amount.
    function getEffectiveFinancialTarget($tr) {
        const mooe = parseFloat($tr.find('[data-field="mooe"]').val()) || 0;
        const co = parseFloat($tr.find('[data-field="capital_outlay"]').val()) || 0;
        const contractRaw = $tr.find('[data-field="contract_amount"]').val();
        const contractAmount = contractRaw === '' ? null : parseFloat(contractRaw);
        const [effectiveMooe, effectiveCo] = effectiveAmounts(mooe, co, contractAmount);
        return {
            originalBudget: mooe + co,
            effectiveBudget: effectiveMooe + effectiveCo,
            effectiveMooe,
            effectiveCo,
            contractAmount
        };
    }
    // Rescale existing monthly Financial Targets proportionally when the
    // effective budget changes. If all months are zero, do not guess a month.
    function syncFinancialTargetsToEffectiveBudget($tr, previousEffectiveBudget = null) {
        if ($tr.attr('data-row-type') !== 'item') {
            return;
        }
        const info = getEffectiveFinancialTarget($tr);
        const $months = $tr.find('.month-input');
        const values = [];
        let currentTotal = 0;
        $months.each(function () {
            const value = parseFloat($(this).val()) || 0;
            values.push(value);
            currentTotal += value;
        });
        if (currentTotal <= 0) {
            $tr.attr('data-effective-budget', info.effectiveBudget.toFixed(2));
            return;
        }
        const baseline = previousEffectiveBudget === null
            ? parseFloat($tr.attr('data-effective-budget'))
            : previousEffectiveBudget;
        // Do not overwrite an intentionally incomplete/custom target schedule.
        if (!Number.isFinite(baseline) || Math.abs(currentTotal - baseline) > 0.01) {
            $tr.attr('data-effective-budget', info.effectiveBudget.toFixed(2));
            return;
        }
        if (info.effectiveBudget <= 0) {
            $months.val('0.00');
            $tr.attr('data-effective-budget', '0.00');
            return;
        }
        let lastPositiveIndex = -1;
        values.forEach((value, index) => {
            if (value > 0) {
                lastPositiveIndex = index;
            }
        });
        let assigned = 0;
        $months.each(function (index) {
            let newValue = 0;
            if (values[index] > 0) {
                newValue = info.effectiveBudget * (values[index] / currentTotal);
            }
            newValue = Math.round((newValue + Number.EPSILON) * 100) / 100;
            if (index === lastPositiveIndex) {
                newValue = Math.round(((info.effectiveBudget - assigned) + Number.EPSILON) * 100) / 100;
            }
            assigned += newValue;
            $(this).val(newValue.toFixed(2));
        });
        $tr.attr('data-effective-budget', info.effectiveBudget.toFixed(2));
    }
    function rememberEffectiveBudget($tr) {
        if ($tr.attr('data-row-type') !== 'item') {
            return;
        }
        const info = getEffectiveFinancialTarget($tr);
        $tr.attr('data-effective-budget', info.effectiveBudget.toFixed(2));
    }
    // Show the effective Financial Target and how much remains to be scheduled.
    function refreshFinancialTargetDisplay($tr) {
        if ($tr.attr('data-row-type') !== 'item') {
            return;
        }
        const info = getEffectiveFinancialTarget($tr);
        let monthlyTotal = 0;
        $tr.find('.month-input').each(function () {
            monthlyTotal += parseFloat($(this).val()) || 0;
        });
        const difference = info.effectiveBudget - monthlyTotal;
        $tr.find('.financial-target-total').text(fmtNum(info.effectiveBudget));
        const $difference = $tr.find('.financial-target-difference');
        if (Math.abs(difference) <= 0.01) {
            $difference.removeClass('text-amber-600 text-rose-600')
                .addClass('text-emerald-600').text('Scheduled ✓');
        } else if (difference > 0) {
            $difference.removeClass('text-emerald-600 text-rose-600')
                .addClass('text-amber-600').text(`${fmtNum(difference)} remaining`);
        } else {
            $difference.removeClass('text-emerald-600 text-amber-600')
                .addClass('text-rose-600').text(`${fmtNum(Math.abs(difference))} over`);
        }
    }
    // Equal distribution is calculated in cents so the total is always exact.
    function distributeFinancialTarget($tr, selectedMonths) {
        const info = getEffectiveFinancialTarget($tr);
        const months = [...new Set(selectedMonths)]
            .map(Number)
            .filter(month => month >= 1 && month <= 12)
            .sort((a, b) => a - b);
        if (!months.length) {
            return false;
        }
        $tr.find('.month-input').val('0.00');
        const totalCents = Math.round((info.effectiveBudget + Number.EPSILON) * 100);
        const baseCents = Math.floor(totalCents / months.length);
        let assignedCents = 0;
        months.forEach((month, index) => {
            const cents = index === months.length - 1
                ? totalCents - assignedCents
                : baseCents;
            assignedCents += cents;
            $tr.find(`.month-input[data-month="${month}"]`)
                .val((cents / 100).toFixed(2));
        });
        rememberEffectiveBudget($tr);
        refreshFinancialTargetDisplay($tr);
        setDirty(true);
        refreshSubmissionReadiness();
        return true;
    }
    function openTargetDistribution($tr) {
        if (isLocked || $tr.attr('data-row-type') !== 'item') {
            return;
        }
        activeDistributionRow = $tr;
        const info = getEffectiveFinancialTarget($tr);
        $('#distributionOriginalBudget').text(fmtNum(info.originalBudget));
        $('#distributionContractAmount').text(
            info.contractAmount === null || Number.isNaN(info.contractAmount)
                ? '—'
                : fmtNum(info.contractAmount)
        );
        $('#distributionEffectiveBudget').text(fmtNum(info.effectiveBudget));
        $('input[name="targetDistributionMode"][value="all"]').prop('checked', true);
        $('.distribution-month-checkbox').prop('checked', false);
        $('#distributionMonthSelector').addClass('hidden');
        $('#targetDistributionModal').removeClass('hidden').addClass('flex');
    }
    function closeTargetDistribution() {
        activeDistributionRow = null;
        $('#targetDistributionModal').addClass('hidden').removeClass('flex');
    }
    // Update the status badge and editing lock
    function applyLockState(locked, status = 'draft', finalized = false) {
        isLocked = locked;
        currentWorkflowStatus = status || 'draft';
        currentFinalized = finalized === true;
        const $badge = $('#builderStatusBadge');
        const label = currentWorkflowStatus.charAt(0).toUpperCase() + currentWorkflowStatus.slice(1);
        $badge
            .removeClass('bg-secondary bg-success bg-warning bg-info bg-danger')
            .addClass(currentFinalized ? 'bg-success' : 'bg-secondary')
            .text(currentFinalized ? 'Finalized / Locked' : (locked ? `${label} / Read Only` : label));
        $('#lockedNotice').toggleClass('hidden', !locked);
        $('#lockedNoticeText').text(
            currentFinalized
                ? 'This plan is finalized and locked. Reopen it from the Financial Plan page before editing.'
                : 'You can review this Financial Plan, but your role does not have permission to edit it.'
        );
        $('.builder-write-control').prop('disabled', locked || isLoading);
        $('.builder-write-field').prop('disabled', locked);
        $('#builderBody .row-type-select, #builderBody .field-input, #builderBody .month-input')
            .prop('disabled', function () {
                const $tr = $(this).closest('tr');
                const isItem = $tr.attr('data-row-type') === 'item';
                if (locked) return true;
                if ($(this).hasClass('row-type-select')) return false;
                if ($(this).data('field') === 'program_classification') return false;
                return !isItem;
            });
        $('#builderBody .btn-delete-row, #builderBody .btn-distribute-target, #builderBody .btn-insert-row')
            .prop('disabled', locked)
            .toggleClass('disabled', locked);
        $('#builderBody .personnel-toggle, #builderBody .personnel-checkbox, #builderBody .personnel-search')
            .prop('disabled', function () {
                const $tr = $(this).closest('tr');
                return locked || $tr.attr('data-row-type') !== 'item';
            });
        if (locked) $('#builderBody .personnel-menu').addClass('hidden');
        $('#builderBody .program-classification-same').prop('disabled', locked);
        $('#builderBody .drag-handle').toggleClass('locked-handle', locked);
        refreshProgramClassificationDisplay();
    }
    // Toggle loading state
    function setLoading(loading) {
        isLoading = loading;
        $('#builderLoadingNote').toggleClass('hidden', !loading);
        $('#btnLoadPlan').prop('disabled', loading);
        $('.builder-write-control').prop('disabled', loading || isLocked);
    }
    // Build one WFP row
    function fieldRow(row = {}) {
        const rowId = row.id ?? '';
        const type = row.row_type ?? 'item';
        const isItem = type === 'item';
        const rowClass = type === 'header'
            ? 'table-secondary fw-semibold'
            : type === 'subheader'
                ? 'table-light fw-semibold builder-subheader'
                : '';
        let monthCells = '';
        for (let month = 1; month <= 12; month++) {
            const value = row.months && row.months[month] !== undefined
                ? row.months[month]
                : 0;
            monthCells += `
                <td>
                    <input type="number"
                           min="0"
                           step="0.01"
                           class="builder-input month-input"
                           data-month="${month}"
                           value="${isItem ? esc(value) : ''}"
                           ${isItem && !isLocked ? '' : 'disabled'}>
                </td>
            `;
        }
        return `
            <tr data-row-id="${esc(rowId)}"
                data-row-type="${esc(type)}"
                data-original-program-classification="${esc(row.program_classification ?? '')}"
                data-original-prexc-code="${esc(row.prexc_code ?? '')}"
                class="${rowClass}">
                <td class="text-center drag-handle" title="Drag to reorder">
                    <i class="fa fa-grip-vertical text-muted"></i>
                </td>
                <td class="text-center align-middle">
                    <button type="button"
                            class="builder-insert btn-insert-row"
                            title="Add row below"
                            ${isLocked ? 'disabled' : ''}>
                        <i class="fa fa-plus"></i>
                    </button>
                </td>
                <td>
                    <select class="builder-input row-type-select" ${isLocked ? 'disabled' : ''}>
                        <option value="item" ${type === 'item' ? 'selected' : ''}>Line</option>
                        <option value="subheader" ${type === 'subheader' ? 'selected' : ''}>Sub Header</option>
                        <option value="header" ${type === 'header' ? 'selected' : ''}>Header</option>
                    </select>
                    <div class="program-classification-wrap mt-1">
                        ${programClassificationControl(row, isItem, isLocked)}
                        <button type="button"
                                class="program-classification-same hidden"
                                title=""
                                ${isLocked ? 'disabled' : ''}>
                            <i class="fa fa-level-down"></i>
                            <span>Same as above</span>
                        </button>
                    </div>
                </td>
                <td>
                    <input type="text" class="builder-input field-input prexc-code-input" data-field="prexc_code"
                           maxlength="50" value="${esc(row.prexc_code ?? '')}" readonly
                           title="Automatically filled from Program Classification"
                           ${isItem && !isLocked ? '' : 'disabled'}>
                </td>
                <td>
                    ${allocationTypeControl(row, isItem, isLocked)}
                </td>
                <td>
                    <input type="text" class="builder-input field-input" data-field="staff_unit_project"
                           maxlength="150" value="${esc(row.staff_unit_project ?? '')}" ${isItem && !isLocked ? '' : 'disabled'}>
                </td>
                <td>
                    <textarea class="builder-input field-input specific-activity-input"
                              data-field="specific_activity"
                              rows="1"
                              style="resize:vertical; overflow:hidden; min-height:31px; white-space:pre-wrap;"
                              ${isItem && !isLocked ? '' : 'disabled'}>${esc(row.specific_activity ?? '')}</textarea>
                </td>
                <td>
                    <select class="builder-input field-input" data-field="expense_item"
                            ${isItem && !isLocked ? '' : 'disabled'}>
                        ${expenseItemOptions(row.expense_item ?? '')}
                    </select>
                </td>
                <td>
                    ${assignedPersonnelControl(row.assigned_personnel ?? '', !isItem || isLocked)}
                </td>
                <td>
                    <input type="number" min="0" step="0.01" class="builder-input field-input"
                           data-field="mooe" value="${esc(row.mooe ?? 0)}" ${isItem && !isLocked ? '' : 'disabled'}>
                </td>
                <td>
                    <input type="number" min="0" step="0.01" class="builder-input field-input"
                           data-field="capital_outlay" value="${esc(row.capital_outlay ?? 0)}" ${isItem && !isLocked ? '' : 'disabled'}>
                </td>
                <td>
                    <input type="number" min="0" step="0.01" class="builder-input field-input"
                           data-field="contract_amount" value="${esc(row.contract_amount ?? '')}" placeholder="—"
                           ${isItem && !isLocked ? '' : 'disabled'}>
                </td>
                <td class="text-center align-middle">
                    <div class="financial-target-total font-semibold text-slate-800">0.00</div>
                    <div class="financial-target-difference mt-1 text-[10px]"></div>
                </td>
                ${monthCells}
                <td class="text-center align-middle">
                    <div class="builder-actions">
                        <button type="button" class="builder-distribute btn-distribute-target"
                                title="Distribute Financial Target" ${isItem && !isLocked ? '' : 'disabled'}>
                            <i class="fa fa-calculator"></i>
                        </button>
                        <button type="button" class="builder-delete btn-delete-row"
                                title="Delete Row" ${isLocked ? 'disabled' : ''}>
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }
    function rebuildProgramClassificationControl($tr, type) {
        const $wrap = $tr.find('.program-classification-wrap');
        const currentValue = String($wrap.find('[data-field="program_classification"]').val() ?? '');
        const currentPrexc = String($tr.find('[data-field="prexc_code"]').val() ?? '');
        const isItem = type === 'item';
        $wrap.html(`
            ${programClassificationControl({
                program_classification: currentValue,
                prexc_code: currentPrexc
            }, isItem, isLocked)}
            <button type="button"
                    class="program-classification-same hidden"
                    title=""
                    ${isLocked ? 'disabled' : ''}>
                <i class="fa fa-level-down"></i>
                <span>Same as above</span>
            </button>
        `);
        if (isItem) {
            const $classification = $wrap.find('.program-classification-input');
            const selectedCode = String($classification.find('option:selected').data('prexc') ?? '').trim();
            if (selectedCode !== '') {
                $tr.find('[data-field="prexc_code"]').val(selectedCode);
            }
        }
    }
    // Apply correct field state after row type changes
    function refreshRowType($tr) {
        const type = $tr.find('.row-type-select').val();
        const isItem = type === 'item';
        $tr.attr('data-row-type', type);
        rebuildProgramClassificationControl($tr, type);
        $tr.removeClass('table-secondary fw-semibold table-light builder-subheader');
        if (type === 'header') {
            $tr.addClass('table-secondary fw-semibold');
        }
        if (type === 'subheader') {
            $tr.addClass('table-light fw-semibold builder-subheader');
        }
        $tr.find('[data-field]:not([data-field="program_classification"]), .month-input')
            .prop('disabled', isLocked || !isItem);
        $tr.find('.row-type-select, [data-field="program_classification"]')
            .prop('disabled', isLocked);
        $tr.find('.personnel-toggle, .personnel-checkbox, .personnel-search')
            .prop('disabled', isLocked || !isItem);
        if (!isItem) {
            $tr.find('.personnel-menu').addClass('hidden');
            $tr.find('.month-input').val('');
        }
    }
    // Reduce repeated Program Classification values in consecutive Budget Lines.
    // The real value remains in the input and is still saved for every row.
    function refreshProgramClassificationDisplay() {
        let previousItemValue = null;
        let previousWasItem = false;
        $('#builderBody tr').each(function () {
            const $tr = $(this);
            const rowType = $tr.attr('data-row-type');
            const $input = $tr.find('.program-classification-input');
            const $same = $tr.find('.program-classification-same');
            if (!$input.length || !$same.length) {
                previousItemValue = null;
                previousWasItem = false;
                return;
            }
            const value = String($input.val() ?? '').trim();
            const isItem = rowType === 'item';
            const isEditing =
                $tr.attr('data-program-classification-editing') === '1';
            const sameAsAbove =
                isItem
                && previousWasItem
                && value !== ''
                && value === previousItemValue;
            if (sameAsAbove && !isEditing) {
                $input.addClass('hidden');
                $same
                    .removeClass('hidden')
                    .prop('disabled', isLocked)
                    .attr('title', value);
            } else {
                $same
                    .addClass('hidden')
                    .attr('title', '');
                $input.removeClass('hidden');
            }
            if (isItem) {
                previousItemValue = value;
                previousWasItem = true;
            } else {
                previousItemValue = null;
                previousWasItem = false;
            }
        });
    }
    // Render all rows
    function renderRows(rows) {
        const $body = $('#builderBody').empty();
        rows.forEach(row => {
            $body.append(fieldRow(row));
        });
        $body.find('.specific-activity-input').each(function () {
            autoGrow(this);
        });
        $body.find('tr[data-row-type="item"]').each(function () {
            rememberEffectiveBudget($(this));
            refreshFinancialTargetDisplay($(this));
        });
        refreshProgramClassificationDisplay();
        applyLockState(isLocked, $('#builderStatusBadge').text());
        refreshSubmissionReadiness();
    }
    // Add a row
    function addRow(type) {
        if (isLocked) {
            return;
        }
        const $body = $('#builderBody');
        const $rows = $body.find('tr');
        const prefill = { row_type: type };
        if (type === 'item' && $rows.length > 0) {
            const $last = $rows.last();
            prefill.prexc_code = $last.find('[data-field="prexc_code"]').val() || '';
            prefill.program_classification = $last.find('[data-field="program_classification"]').val() || '';
            prefill.allocation_type = $last.find('[data-field="allocation_type"]').val() || '';
        }
        const $newRow = $(fieldRow(prefill));
        $body.append($newRow);
        if (type === 'item') {
            rememberEffectiveBudget($newRow);
            refreshFinancialTargetDisplay($newRow);
        }
        $newRow.find('.specific-activity-input').each(function () {
            autoGrow(this);
        });
        refreshProgramClassificationDisplay();
        setDirty(true);
        recalcLiveBalance();
        refreshSubmissionReadiness();
    }
    // Calculate live programmed totals by Allocation Type and expense category
    function getLiveTotals() {
        const totals = {};
        ALLOCATION_TYPE_OPTIONS.forEach(item => {
            const typeId = Number(item.id);
            if (Boolean(Number(item.allows_mooe))) {
                totals[`${typeId}|mooe`] = { original: 0, effective: 0 };
            }
            if (Boolean(Number(item.allows_capital_outlay))) {
                totals[`${typeId}|capital_outlay`] = { original: 0, effective: 0 };
            }
        });
        $('#builderBody tr[data-row-type="item"]').each(function () {
            const $tr = $(this);
            const mooe = parseFloat($tr.find('[data-field="mooe"]').val()) || 0;
            const co = parseFloat($tr.find('[data-field="capital_outlay"]').val()) || 0;
            const contractRaw = $tr.find('[data-field="contract_amount"]').val();
            const contractAmount = contractRaw === '' ? null : parseFloat(contractRaw);
            const allocationType = String(
                $tr.find('[data-field="allocation_type"]').val() || ''
            ).trim().toLowerCase();
            const allocationMaster = ALLOCATION_TYPE_OPTIONS.find(item =>
                String(item.code ?? '').trim().toLowerCase() === allocationType
            );
            if (!allocationMaster) return;
            const [effectiveMooe, effectiveCo] = effectiveAmounts(mooe, co, contractAmount);
            const typeId = Number(allocationMaster.id);
            const mooeKey = `${typeId}|mooe`;
            const coKey = `${typeId}|capital_outlay`;
            if (totals[mooeKey]) {
                totals[mooeKey].original += mooe;
                totals[mooeKey].effective += effectiveMooe;
            }
            if (totals[coKey]) {
                totals[coKey].original += co;
                totals[coKey].effective += effectiveCo;
            }
        });
        return totals;
    }
    // Recalculate all configured allocation balances
    function recalcLiveBalance() {
        const totals = getLiveTotals();
        const balances = [];
        $('.allocation-card').each(function () {
            const $card = $(this);
            const typeId = Number($card.data('allocation-type-id'));
            const category = String($card.data('expense-category'));
            const key = `${typeId}|${category}`;
            const values = totals[key] || { original: 0, effective: 0 };
            const allocation = parseFloat($card.find('.allocation-amount-input').val()) || 0;
            const savings = Math.max(0, values.original - values.effective);
            const balance = allocation - values.effective;
            $card.find('[data-role="original-programmed"]').text(fmtNum(values.original));
            $card.find('[data-role="programmed"]').text(fmtNum(values.effective));
            $card.find('[data-role="savings"]').text(fmtNum(savings));
            $card.find('[data-role="balance"]')
                .text(fmtNum(balance))
                .toggleClass('text-danger', balance < 0);
            balances.push({
                allocationTypeId: typeId,
                code: String($card.data('allocation-code') || ''),
                category,
                allocation,
                original: values.original,
                programmed: values.effective,
                savings,
                balance
            });
        });
        return balances;
    }
    // Allocation Type changes affect live allocation balances immediately.
    $('#builderBody').on('change', '.allocation-type-input', function () {
        setDirty(true);
        recalcLiveBalance();
    });
    // Load workflow status
    function loadStatus() {
        return $.getJSON(
            '{{ route("financial-plans.status") }}',
            {
                fiscal_year: $('#fiscalYear').val(),
                office_name: $('#officeName').val()
            }
        ).done(function (response) {
            const finalized = response.finalized === 'yes';
            const canEdit = response.can_edit === true;
            applyLockState(finalized || !canEdit, response.status || 'draft', finalized);
        }).fail(function () {
            // Fail closed so a status error never exposes editing controls.
            applyLockState(true, 'unavailable', false);
        });
    }
    // Load signatories
    function loadSignatories() {
        return $.getJSON(
            '{{ route("financial-plans.signatories") }}',
            {
                fiscal_year: $('#fiscalYear').val(),
                office_name: $('#officeName').val()
            }
        ).done(function (sig) {
            $('#sigPreparedBy').val(sig.prepared_by || '');
            $('#sigPreparedByPosition').val(sig.prepared_by_position || '');
            $('#sigReviewedBy').val(sig.reviewed_by || '');
            $('#sigReviewedByPosition').val(sig.reviewed_by_position || '');
            $('#sigRecommendedBy').val(sig.recommended_by || '');
            $('#sigRecommendedByPosition').val(sig.recommended_by_position || '');
            $('#sigApprovedBy').val(sig.approved_by || '');
            $('#sigApprovedByPosition').val(sig.approved_by_position || '');
        });
    }
    // Save signatories
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
                approved_by_position: $('#sigApprovedByPosition').val()
            }),
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
    }
    // Load allocation and server-calculated balance
    function loadAllocationAndBalance() {
        return $.getJSON(
            '{{ route("financial-plans.totals") }}',
            {
                fiscal_year: $('#fiscalYear').val(),
                office_name: $('#officeName').val()
            }
        ).done(function (response) {
            $('.allocation-amount-input').val('0');
            (response.allocation_totals || []).forEach(item => {
                const $card = $(`.allocation-card[data-allocation-type-id="${Number(item.allocation_type_id)}"][data-expense-category="${item.expense_category}"]`);
                $card.find('.allocation-amount-input').val(Number(item.allocation || 0).toFixed(2));
            });
            allocationDirty = false;
            refreshUnsavedBadge();
            recalcLiveBalance();
        });
    }
    // Save allocation
    function saveAllocation() {
        if (isLocked) {
            showMessage('This plan is read-only and cannot be changed.', 'warning');
            return;
        }
        const allocationItems = [];
        let hasInvalidAmount = false;
        $('.allocation-card').each(function () {
            const $card = $(this);
            const amount = parseFloat($card.find('.allocation-amount-input').val()) || 0;
            if (amount < 0) hasInvalidAmount = true;
            allocationItems.push({
                allocation_type_id: Number($card.data('allocation-type-id')),
                expense_category: String($card.data('expense-category')),
                amount
            });
        });
        if (!allocationItems.length) {
            showMessage('No active Allocation Types are configured for this Staff/Office.', 'warning');
            return;
        }
        if (hasInvalidAmount) {
            showMessage('Allocation amounts cannot be negative.', 'danger');
            return;
        }
        $('#btnSaveAllocation').prop('disabled', true);
        $.ajax({
            url: '{{ route("financial-plans.allocation.save") }}',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                fiscal_year: $('#fiscalYear').val(),
                office_name: $('#officeName').val(),
                allocation_items: allocationItems
            }),
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).done(function () {
            allocationDirty = false;
            refreshUnsavedBadge();
            loadAllocationAndBalance();
            showMessage('Allocation saved successfully.');
        }).fail(function (xhr) {
            showMessage(getErrorMessage(xhr, 'Failed to save allocation.'), 'danger');
        }).always(function () {
            $('#btnSaveAllocation').prop('disabled', isLocked);
        });
    }
    // Load the selected plan
    function loadPlan(manualReload = false) {
        if (manualReload && hasUnsavedChanges) {
            if (!confirm('You have unsaved changes. Loading another plan will discard them. Continue?')) {
                return;
            }
        }
        const fiscalYear = $('#fiscalYear').val();
        const officeName = $('#officeName').val().trim();
        if (!officeName) {
            showMessage('Name of Office/Staff is required.', 'danger');
            return;
        }
        if (activeLoadRequest && activeLoadRequest.readyState !== 4) {
            activeLoadRequest.abort();
        }
        setLoading(true);
        $.when(loadStatus(), loadSignatories(), loadAllocationAndBalance()).always(function () {
            activeLoadRequest = $.getJSON(
                '{{ route("financial-plans.data") }}',
                {
                    fiscal_year: fiscalYear,
                    office_name: officeName
                }
            ).done(function (rows) {
                renderRows(rows);
                if (rows.length === 0 && !isLocked) {
                    addRow('header');
                    addRow('item');
                }
                setDirty(false);
                recalcLiveBalance();
            }).fail(function (xhr) {
                showMessage(getErrorMessage(xhr, 'Failed to load the financial plan.'), 'danger');
            }).always(function () {
                setLoading(false);
                applyLockState(isLocked, currentWorkflowStatus, currentFinalized);
            });
        });
    }
    // Collect all builder rows
    function collectPayload() {
        const rows = [];
        $('#builderBody tr').each(function () {
            const $tr = $(this);
            const rowType = $tr.attr('data-row-type') || $tr.find('.row-type-select').val();
            const row = {
                id: $tr.attr('data-row-id') || null,
                row_type: rowType,
                months: {}
            };
            $tr.find('[data-field]').each(function () {
                const field = $(this).data('field');
                const value = $(this).val();
                if (field === 'mooe' || field === 'capital_outlay') {
                    row[field] = value === '' || value === null ? 0 : value;
                } else {
                    row[field] = value;
                }
            });
            $tr.find('.month-input').each(function () {
                row.months[$(this).data('month')] = $(this).val() || 0;
            });
            rows.push(row);
        });
        return {
            fiscal_year: $('#fiscalYear').val(),
            office_name: $('#officeName').val().trim(),
            rows: rows
        };
    }
    // Validate builder rows before sending them to Laravel
    function validatePayload(payload) {
        if (!payload.office_name) {
            return 'Name of Office/Staff is required.';
        }
        if (!payload.fiscal_year || payload.fiscal_year < 2000 || payload.fiscal_year > 2100) {
            return 'Please enter a valid fiscal year from 2000 to 2100.';
        }
        if (!payload.rows.length) {
            return 'Please add at least one row.';
        }
        for (let index = 0; index < payload.rows.length; index++) {
            const row = payload.rows[index];
            if (!['header', 'subheader', 'item'].includes(row.row_type)) {
                return `Row ${index + 1} has an invalid row type.`;
            }
            if (row.row_type !== 'item') {
                continue;
            }
            const numericFields = ['mooe', 'capital_outlay', 'contract_amount'];
            for (const field of numericFields) {
                if (row[field] === '' || row[field] === null || row[field] === undefined) {
                    continue;
                }
                const value = Number(row[field]);
                if (Number.isNaN(value) || value < 0) {
                    return `Row ${index + 1} contains an invalid negative or non-numeric amount.`;
                }
            }
            for (let month = 1; month <= 12; month++) {
                const value = Number(row.months[month] || 0);
                if (Number.isNaN(value) || value < 0) {
                    return `Row ${index + 1}, month ${month}, contains an invalid amount.`;
                }
            }
        }
        return null;
    }
    // Warn when monthly Financial Target does not match the effective budget
    function getMonthlyTargetWarnings(payload) {
        const warnings = [];
        payload.rows.forEach((row, index) => {
            if (row.row_type !== 'item') {
                return;
            }
            const mooe = Number(row.mooe || 0);
            const co = Number(row.capital_outlay || 0);
            const contract = row.contract_amount === '' || row.contract_amount === null
                ? null
                : Number(row.contract_amount);
            const [effectiveMooe, effectiveCo] = effectiveAmounts(mooe, co, contract);
            const expected = effectiveMooe + effectiveCo;
            let monthlyTotal = 0;
            for (let month = 1; month <= 12; month++) {
                monthlyTotal += Number(row.months[month] || 0);
            }
            if (Math.abs(expected - monthlyTotal) > 0.01) {
                warnings.push(
                    `Row ${index + 1}: effective budget ${fmtNum(expected)} vs Financial Target ${fmtNum(monthlyTotal)}`
                );
            }
        });
        return warnings;
    }
    // Build a client-side preview of the server Submit validation.
    // Laravel remains the authoritative validation when the plan is submitted.
    function getSubmissionReadiness() {
        const issues = [];
        const itemRows = $('#builderBody tr[data-row-type="item"]');
        if (!itemRows.length) {
            issues.push({
                type: 'error',
                message: 'Add at least one Budget Line.'
            });
        }
        itemRows.each(function (itemIndex) {
            const $tr = $(this);
            const rowNumber = itemIndex + 1;
            const classification = String(
                $tr.find('[data-field="program_classification"]').val() || ''
            ).trim();
            const prexcCode = String(
                $tr.find('[data-field="prexc_code"]').val() || ''
            ).trim();
            const allocationType = String(
                $tr.find('[data-field="allocation_type"]').val() || ''
            ).trim().toLowerCase();
            const staffUnit = String(
                $tr.find('[data-field="staff_unit_project"]').val() || ''
            ).trim();
            const specificActivity = String(
                $tr.find('[data-field="specific_activity"]').val() || ''
            ).trim();
            const expenseItem = String(
                $tr.find('[data-field="expense_item"]').val() || ''
            ).trim();
            const assignedPersonnel = String(
                $tr.find('[data-field="assigned_personnel"]').val() || ''
            ).trim();
            const mooe = parseFloat($tr.find('[data-field="mooe"]').val()) || 0;
            const co = parseFloat($tr.find('[data-field="capital_outlay"]').val()) || 0;
            const contractRaw = $tr.find('[data-field="contract_amount"]').val();
            const contractAmount = contractRaw === '' || contractRaw === null
                ? null
                : parseFloat(contractRaw);
            const originalBudget = mooe + co;
            const label = specificActivity || classification || `Budget Line ${rowNumber}`;
            if (!classification) {
                issues.push({ type: 'error', message: `${label}: Program Classification is required.` });
            }
            if (!prexcCode) {
                issues.push({ type: 'error', message: `${label}: PREXC Code is required.` });
            }
            if (classification && prexcCode) {
                const originalClassification = String(
                    $tr.attr('data-original-program-classification') || ''
                ).trim();
                const originalPrexcCode = String(
                    $tr.attr('data-original-prexc-code') || ''
                ).trim();
                const isExistingRow = Boolean(
                    String($tr.attr('data-row-id') || '').trim()
                );
                // Existing legacy Program Classification/PREXC pairs are allowed
                // while unchanged. This mirrors Laravel's validatePrexcRows().
                // Once the user intentionally changes either value, the pair must
                // match the active PREXC master list.
                const unchangedLegacyPair = isExistingRow
                    && classification === originalClassification
                    && prexcCode === originalPrexcCode;
                if (!unchangedLegacyPair) {
                    const validPrexcPair = PREXC_CLASSIFICATIONS.some(item =>
                        String(item.classification_name ?? '').trim() === classification
                        && String(item.prexc_code ?? '').trim() === prexcCode
                    );
                    if (!validPrexcPair) {
                        issues.push({
                            type: 'error',
                            message: `${label}: Program Classification and PREXC Code do not match the active master list.`
                        });
                    }
                }
            }
            if (!staffUnit) {
                issues.push({ type: 'error', message: `${label}: Staff/Unit is required.` });
            }
            if (!specificActivity) {
                issues.push({ type: 'error', message: `Budget Line ${rowNumber}: Specific Activity is required.` });
            }
            if (!expenseItem) {
                issues.push({ type: 'error', message: `${label}: Expense Item is required.` });
            }
            if (!assignedPersonnel) {
                issues.push({ type: 'error', message: `${label}: Assigned Personnel is required.` });
            }
            if (originalBudget <= 0) {
                issues.push({
                    type: 'error',
                    message: `${label}: Enter an MOOE or Capital Outlay amount greater than zero.`
                });
            }
            if (contractAmount !== null && Number.isFinite(contractAmount) && contractAmount > originalBudget) {
                issues.push({
                    type: 'error',
                    message: `${label}: Contract Amount cannot exceed the original MOOE + Capital Outlay budget.`
                });
            }
            let allocationMaster = null;
            if (originalBudget > 0 || (contractAmount !== null && contractAmount > 0)) {
                if (!allocationType) {
                    issues.push({
                        type: 'error',
                        message: `${label}: Allocation Type is required before submission.`
                    });
                } else {
                    allocationMaster = ALLOCATION_TYPE_OPTIONS.find(item =>
                        String(item.code ?? '').trim().toLowerCase() === allocationType
                    );
                    if (!allocationMaster) {
                        issues.push({
                            type: 'error',
                            message: `${label}: The selected Allocation Type is not available for this Staff/Office.`
                        });
                    }
                }
            }
            if (allocationMaster) {
                const allowsMooe = Boolean(Number(allocationMaster.allows_mooe));
                const allowsCo = Boolean(Number(allocationMaster.allows_capital_outlay));
                const allocationName = String(allocationMaster.name ?? allocationType).trim();
                if (mooe > 0 && !allowsMooe) {
                    issues.push({
                        type: 'error',
                        message: `${label}: ${allocationName} does not allow MOOE.`
                    });
                }
                if (co > 0 && !allowsCo) {
                    issues.push({
                        type: 'error',
                        message: `${label}: ${allocationName} does not allow Capital Outlay.`
                    });
                }
            }
            const info = getEffectiveFinancialTarget($tr);
            let monthlyTotal = 0;
            $tr.find('.month-input').each(function () {
                monthlyTotal += parseFloat($(this).val()) || 0;
            });
            if (Math.abs(monthlyTotal - info.effectiveBudget) > 0.01) {
                issues.push({
                    type: 'error',
                    message: `${label}: Financial Target is ${fmtNum(monthlyTotal)} but Effective Budget is ${fmtNum(info.effectiveBudget)}.`
                });
            }
        });
        getLiveBalancesForReadiness().forEach(item => {
            if (item.balance >= -0.01) return;
            const allocationMaster = ALLOCATION_TYPE_OPTIONS.find(type =>
                Number(type.id) === item.allocationTypeId
            );
            const allocationName = String(allocationMaster?.name || item.code || 'Allocation');
            const categoryName = item.category === 'capital_outlay' ? 'Capital Outlay' : 'MOOE';
            issues.push({
                type: 'error',
                message: `${allocationName} ${categoryName} exceeds the available allocation by ${fmtNum(Math.abs(item.balance))}.`
            });
        });
        return {
            ready: issues.length === 0,
            issues
        };
    }
    function getLiveBalancesForReadiness() {
        return recalcLiveBalance();
    }
    function refreshSubmissionReadiness() {
        const $panel = $('#submissionReadinessPanel');
        if (!$panel.length) {
            return;
        }
        const result = getSubmissionReadiness();
        const issueCount = result.issues.length;
        const $badge = $('#submissionReadinessBadge');
        const $summary = $('#submissionReadinessSummary');
        const $hint = $('#submissionReadinessHint');
        const $issues = $('#submissionReadinessIssues');
        $badge.removeClass(
            'bg-slate-100 text-slate-700 bg-emerald-100 text-emerald-700 bg-amber-100 text-amber-800'
        );
        if (result.ready) {
            $badge
                .addClass('bg-emerald-100 text-emerald-700')
                .text('Ready for Submission');
            $summary.text('All client-side submission checks passed.');
            $hint.text('Laravel will validate again when the plan is submitted.');
            $issues.html(`
                <div class="sm:col-span-2 xl:col-span-3 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-emerald-800">
                    <i class="fa fa-check-circle mr-1"></i>
                    No submission issues detected.
                </div>
            `);
            return;
        }
        $badge
            .addClass('bg-amber-100 text-amber-800')
            .text('Not Ready');
        $summary.text(
            `${issueCount} submission issue${issueCount === 1 ? '' : 's'} detected.`
        );
        const maxVisible = 9;
        const visibleIssues = result.issues.slice(0, maxVisible);
        $issues.html(
            visibleIssues.map(issue => `
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-amber-900">
                    <i class="fa fa-exclamation-triangle mr-1"></i>
                    ${esc(issue.message)}
                </div>
            `).join('')
        );
        const remaining = issueCount - visibleIssues.length;
        if (remaining > 0) {
            $hint.text(
                `Showing the first ${visibleIssues.length} issues. ${remaining} more issue${remaining === 1 ? '' : 's'} remain.`
            );
        } else {
            $hint.text('Complete the items below before submission.');
        }
    }
    // Save the complete plan
    function savePlan() {
        if (isLocked) {
            showMessage('This plan is finalized and cannot be changed.', 'warning');
            return;
        }
        const payload = collectPayload();
        const validationError = validatePayload(payload);
        if (validationError) {
            showMessage(validationError, 'danger');
            return;
        }
        const targetWarnings = getMonthlyTargetWarnings(payload);
        if (targetWarnings.length) {
            const preview = targetWarnings.slice(0, 10).join('\n');
            const more = targetWarnings.length > 10
                ? `\n...and ${targetWarnings.length - 10} more row(s).`
                : '';
            if (!confirm(
                `Some Financial Targets do not equal the effective budget (MOOE/CO or Contract Amount):\n\n${preview}${more}\n\nSave anyway?`
            )) {
                return;
            }
        }
        const { mooeBalance, coBalance, ninpBalance } = recalcLiveBalance();
        if (mooeBalance < 0 || coBalance < 0 || ninpBalance < 0) {
            let message = 'This plan exceeds the allocation ceiling:\n';
            if (mooeBalance < 0) {
                message += `MOOE over by ${fmtNum(Math.abs(mooeBalance))}\n`;
            }
            if (coBalance < 0) {
                message += `Capital Outlay over by ${fmtNum(Math.abs(coBalance))}\n`;
            }
            if (ninpBalance < 0) {
                message += `NINP over by ${fmtNum(Math.abs(ninpBalance))}\n`;
            }
            message += '\nSave anyway?';
            if (!confirm(message)) {
                return;
            }
        }
        const $buttons = $('#btnSavePlan, #btnSavePlan2');
        $buttons
            .prop('disabled', true)
            .html('<i class="fa fa-spinner fa-spin me-1"></i> Saving...');
        $.ajax({
            url: '{{ route("financial-plans.save") }}',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).done(function (response) {
            if (!response.success) {
                showMessage(response.message || 'Failed to save plan.', 'danger');
                return;
            }
            saveSignatories()
                .done(function () {
                    setDirty(false);
                    window.location.href = response.redirect;
                })
                .fail(function (xhr) {
                    showMessage(
                        getErrorMessage(xhr, 'The plan was saved, but signatories could not be saved.'),
                        'warning'
                    );
                    $buttons
                        .prop('disabled', false)
                        .html('<i class="fa fa-save me-1"></i> Save Entire Plan');
                });
        }).fail(function (xhr) {
            showMessage(getErrorMessage(xhr, 'Failed to save plan.'), 'danger');
        }).always(function () {
            if (!isLocked) {
                $buttons
                    .prop('disabled', false)
                    .html('<i class="fa fa-save me-1"></i> Save Entire Plan');
            }
        });
    }
    // Enable row dragging
    function enableRowDragging() {
        let dragSource = null;
        $('#builderBody').on('mousedown', '.drag-handle', function () {
            if (isLocked) {
                return;
            }
            $(this).closest('tr').attr('draggable', true);
        });
        $('#builderBody').on('dragstart', 'tr', function (event) {
            if (isLocked) {
                event.preventDefault();
                return;
            }
            dragSource = this;
            event.originalEvent.dataTransfer.effectAllowed = 'move';
            event.originalEvent.dataTransfer.setData('text/plain', '');
            $(this).addClass('dragging');
        });
        $('#builderBody').on('dragend', 'tr', function () {
            $(this).removeClass('dragging').attr('draggable', false);
            $('#builderBody tr').removeClass('drag-over');
        });
        $('#builderBody').on('dragover', 'tr', function (event) {
            if (isLocked || !dragSource) {
                return;
            }
            event.preventDefault();
            event.originalEvent.dataTransfer.dropEffect = 'move';
            if (this !== dragSource) {
                $(this).addClass('drag-over');
            }
        });
        $('#builderBody').on('dragleave', 'tr', function () {
            $(this).removeClass('drag-over');
        });
        $('#builderBody').on('drop', 'tr', function (event) {
            if (isLocked) {
                return;
            }
            event.preventDefault();
            $(this).removeClass('drag-over');
            if (!dragSource || dragSource === this) {
                return;
            }
            const $target = $(this);
            const sourceIndex = $(dragSource).index();
            const targetIndex = $target.index();
            if (sourceIndex < targetIndex) {
                $target.after(dragSource);
            } else {
                $target.before(dragSource);
            }
            refreshProgramClassificationDisplay();
            setDirty(true);
            recalcLiveBalance();
        });
    }
    // Add a normal Budget Line directly below the clicked row.
    // The existing row type selector can change it to Header or Sub Header afterward.
    $('#builderBody').on('click', '.btn-insert-row', function () {
        if (isLocked) {
            return;
        }
        const $currentRow = $(this).closest('tr');
        const prefill = { row_type: 'item' };
        // Copy nearby PREXC and Program Classification when available.
        let $sourceRow = $currentRow;
        if ($sourceRow.attr('data-row-type') !== 'item') {
            $sourceRow = $currentRow.prevAll('tr[data-row-type="item"]').first();
            if (!$sourceRow.length) {
                $sourceRow = $currentRow.nextAll('tr[data-row-type="item"]').first();
            }
        }
        if ($sourceRow.length) {
            prefill.prexc_code = $sourceRow.find('[data-field="prexc_code"]').val() || '';
            prefill.program_classification = $sourceRow.find('[data-field="program_classification"]').val() || '';
        }
        const $newRow = $(fieldRow(prefill));
        $currentRow.after($newRow);
        rememberEffectiveBudget($newRow);
        refreshFinancialTargetDisplay($newRow);
        $newRow.find('.specific-activity-input').each(function () {
            autoGrow(this);
        });
        refreshProgramClassificationDisplay();
        setDirty(true);
        recalcLiveBalance();
    });
    // Handle row type changes
    $('#builderBody').on('change', '.row-type-select', function () {
        if (isLocked) {
            return;
        }
        const $tr = $(this).closest('tr');
        refreshRowType($tr);
        $tr.removeAttr('data-program-classification-editing');
        refreshProgramClassificationDisplay();
        setDirty(true);
        recalcLiveBalance();
    });
    // Show the actual Program Classification input when "Same as above" is clicked.
    $('#builderBody').on('click', '.program-classification-same', function () {
        if (isLocked || $(this).prop('disabled')) {
            return;
        }
        const $tr = $(this).closest('tr');
        const $input = $tr.find('.program-classification-input');
        $tr.attr('data-program-classification-editing', '1');
        $(this).addClass('hidden');
        $input.removeClass('hidden').trigger('focus');
    });
    // Selecting Program Classification automatically fills the official PREXC code.
    // Header/Sub Header rows remain free-text labels and do not change PREXC.
    $('#builderBody').on('input change', '.program-classification-input', function () {
        const $input = $(this);
        const $tr = $input.closest('tr');
        if ($tr.attr('data-row-type') === 'item' && $input.is('select')) {
            const $selected = $input.find('option:selected');
            const selectedCode = String($selected.data('prexc') ?? '').trim();
            const currentCode = String($tr.find('[data-field="prexc_code"]').val() ?? '').trim();
            // Active master-list selections always control PREXC.
            // A legacy option has no master code, so keep its existing saved PREXC value.
            if (selectedCode !== '') {
                $tr.find('[data-field="prexc_code"]').val(selectedCode);
            } else if ($input.val() === '') {
                $tr.find('[data-field="prexc_code"]').val('');
            } else {
                $tr.find('[data-field="prexc_code"]').val(currentCode);
            }
            recalcLiveBalance();
        }
        refreshProgramClassificationDisplay();
    });
    $('#builderBody').on('blur', '.program-classification-input', function () {
        $(this)
            .closest('tr')
            .removeAttr('data-program-classification-editing');
        refreshProgramClassificationDisplay();
    });
    // Open or close the Assigned Personnel selector.
    $('#builderBody').on('click', '.personnel-toggle', function (event) {
        event.stopPropagation();
        if (isLocked || $(this).prop('disabled')) {
            return;
        }
        const $menu = $(this).siblings('.personnel-menu');
        $('#builderBody .personnel-menu').not($menu).addClass('hidden');
        $menu.toggleClass('hidden');
        if (!$menu.hasClass('hidden')) {
            $menu.find('.personnel-search').val('').trigger('input').focus();
        }
    });
    // Keep the hidden assigned_personnel string synchronized with checked names.
    $('#builderBody').on('change', '.personnel-checkbox', function () {
        refreshAssignedPersonnel($(this).closest('.personnel-multiselect'));
        refreshSubmissionReadiness();
    });
    // Search only inside the current row's personnel selector.
    $('#builderBody').on('input', '.personnel-search', function () {
        const search = String($(this).val() ?? '').trim().toLowerCase();
        const $control = $(this).closest('.personnel-multiselect');
        $control.find('.personnel-option').each(function () {
            const haystack = String($(this).data('personnel-search') ?? '');
            $(this).toggleClass('hidden', search !== '' && !haystack.includes(search));
        });
    });
    // Keep clicks inside the menu from closing it.
    $('#builderBody').on('click', '.personnel-menu', function (event) {
        event.stopPropagation();
    });
    // Close personnel selectors when clicking elsewhere.
    $(document).on('click', function () {
        $('#builderBody .personnel-menu').addClass('hidden');
    });
    // Track changes inside WFP rows.
    // MOOE / CO / Contract Amount changes also synchronize the Financial Target.
    $('#builderBody').on('input change', '.field-input, .month-input', function () {
        if (isLocked) {
            return;
        }
        setDirty(true);
        if ($(this).hasClass('specific-activity-input')) {
            autoGrow(this);
        }
        const field = $(this).data('field');
        const $tr = $(this).closest('tr');
        if (['mooe', 'capital_outlay', 'contract_amount'].includes(field)) {
            const previousEffectiveBudget = parseFloat($tr.attr('data-effective-budget'));
            syncFinancialTargetsToEffectiveBudget(
                $tr,
                Number.isFinite(previousEffectiveBudget) ? previousEffectiveBudget : null
            );
            rememberEffectiveBudget($tr);
            recalcLiveBalance();
        } else if ($(this).hasClass('month-input')) {
            refreshFinancialTargetDisplay($tr);
        } else if (field === 'prexc_code') {
            recalcLiveBalance();
        }
        refreshSubmissionReadiness();
    });
    // Financial Target distribution.
    $('#builderBody').on('click', '.btn-distribute-target', function () {
        openTargetDistribution($(this).closest('tr'));
    });
    $('input[name="targetDistributionMode"]').on('change', function () {
        $('#distributionMonthSelector').toggleClass('hidden', $(this).val() !== 'selected');
    });
    $('#btnCloseTargetDistribution, #btnCancelTargetDistribution').on('click', closeTargetDistribution);
    $('#targetDistributionModal').on('click', function (event) {
        if (event.target === this) {
            closeTargetDistribution();
        }
    });
    $('#btnApplyTargetDistribution').on('click', function () {
        if (!activeDistributionRow || !activeDistributionRow.length) {
            closeTargetDistribution();
            return;
        }
        const $targetRow = activeDistributionRow;
        const mode = $('input[name="targetDistributionMode"]:checked').val();
        if (mode === 'manual') {
            closeTargetDistribution();
            return;
        }
        let months = [];
        if (mode === 'all') {
            months = Array.from({ length: 12 }, (_, index) => index + 1);
        } else {
            $('.distribution-month-checkbox:checked').each(function () {
                months.push(Number($(this).val()));
            });
            if (!months.length) {
                alert('Please select at least one month.');
                return;
            }
        }
        distributeFinancialTarget($targetRow, months);
        closeTargetDistribution();
    });
    // Delete one unsaved/saved row from the builder
    $('#builderBody').on('click', '.btn-delete-row', function () {
        if (isLocked) {
            return;
        }
        if (!confirm('Remove this row from the plan? The deletion is completed when you save the entire plan.')) {
            return;
        }
        $(this).closest('tr').remove();
        refreshProgramClassificationDisplay();
        setDirty(true);
        recalcLiveBalance();
        refreshSubmissionReadiness();
    });
    // Track signatory changes
    $('#sigPreparedBy, #sigPreparedByPosition, #sigReviewedBy, #sigReviewedByPosition, #sigRecommendedBy, #sigRecommendedByPosition, #sigApprovedBy, #sigApprovedByPosition')
        .on('input', function () {
            if (!isLocked) {
                setDirty(true);
            }
        });
    // Track allocation changes
    $('#allocationCards').on('input', '.allocation-amount-input', function () {
        if (!isLocked) {
            allocationDirty = true;
            refreshUnsavedBadge();
            recalcLiveBalance();
            refreshSubmissionReadiness();
        }
    });
    $('#btnAddHeader, #btnAddHeader2').on('click', function () {
        addRow('header');
    });
    $('#btnAddSubHeader, #btnAddSubHeader2').on('click', function () {
        addRow('subheader');
    });
    $('#btnAddItem, #btnAddItem2').on('click', function () {
        addRow('item');
    });
    $('#btnSavePlan, #btnSavePlan2').on('click', savePlan);
    $('#btnSaveAllocation').on('click', saveAllocation);
    $('#btnLoadPlan').on('click', function () {
        loadPlan(true);
    });
    // Warn before leaving with unsaved changes
    window.addEventListener('beforeunload', function (event) {
        if (!hasUnsavedChanges && !allocationDirty) {
            return;
        }
        event.preventDefault();
        event.returnValue = '';
    });
    enableRowDragging();
    // Only auto-load when an office/staff was supplied by the page.
    // A blank Builder is a valid new-plan state and should not show validation immediately.
    if ($('#officeName').val().trim()) {
        loadPlan(false);
    } else {
        applyLockState(false, 'draft');
        $('#builderLoadingNote').addClass('hidden');
    }
});
</script>
@endpush
<style>
#builderTable {
    min-width: 2725px;
    font-size: 0.75rem;
}
#builderTable th,
#builderTable td {
    border-right: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
    padding: 0.4rem;
    vertical-align: top;
}
#builderTable thead th {
    position: sticky;
    top: 0;
    z-index: 5;
    background: #f8fafc;
    color: #334155;
    font-size: 0.68rem;
    font-weight: 700;
    line-height: 1.2;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    vertical-align: middle;
    white-space: normal;
}
#builderTable .builder-subheader td:nth-child(2) {
    padding-left: 24px;
}
#builderBody tr.dragging {
    opacity: 0.4;
}
#builderBody tr.drag-over {
    box-shadow: inset 0 2px 0 0 #10b981;
}
.drag-handle {
    cursor: grab;
    text-align: center;
    vertical-align: middle !important;
}
.drag-handle:active {
    cursor: grabbing;
}
.drag-handle.locked-handle {
    cursor: not-allowed;
    opacity: 0.5;
}
.builder-table-wrap {
    min-height: 360px;
    max-height: 68vh;
    overflow: auto;
}
.builder-input {
    width: 100%;
    min-height: 32px;
    border: 1px solid #cbd5e1;
    border-radius: 0.45rem;
    background: #fff;
    padding: 0.35rem 0.5rem;
    color: #334155;
    font-size: 0.72rem;
    outline: none;
}
.builder-input:focus {
    border-color: #0ea5e9;
    box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.12);
}
.builder-input:disabled {
    cursor: not-allowed;
    background: #f1f5f9;
    color: #64748b;
}
#builderTable input[type="number"] {
    min-width: 86px;
}
.program-classification-wrap {
    position: relative;
}
.prexc-code-input[readonly] {
    background: #f8fafc;
    color: #475569;
    cursor: default;
}
.program-classification-same {
    display: inline-flex;
    width: 100%;
    min-height: 32px;
    align-items: center;
    gap: 6px;
    border: 1px dashed #bae6fd;
    border-radius: 0.45rem;
    background: #f0f9ff;
    padding: 0.35rem 0.5rem;
    color: #0369a1;
    font-size: 0.7rem;
    font-weight: 600;
    text-align: left;
    transition: background-color 0.15s ease, border-color 0.15s ease;
}
.program-classification-same:hover:not(:disabled) {
    border-color: #7dd3fc;
    background: #e0f2fe;
}
.program-classification-same:disabled {
    cursor: default;
    opacity: 0.8;
}
.program-classification-same i {
    font-size: 0.65rem;
}
.personnel-multiselect {
    position: relative;
    min-width: 210px;
}
.personnel-toggle {
    display: flex;
    width: 100%;
    min-height: 34px;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    border: 1px solid #cbd5e1;
    border-radius: 0.45rem;
    background: #fff;
    padding: 0.3rem 0.45rem;
    color: #334155;
    font-size: 0.72rem;
    text-align: left;
}
.personnel-toggle:focus {
    border-color: #0ea5e9;
    box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.12);
    outline: none;
}
.personnel-toggle:disabled {
    cursor: not-allowed;
    background: #f1f5f9;
    color: #64748b;
}
.personnel-selected {
    display: flex;
    min-width: 0;
    flex: 1;
    flex-wrap: wrap;
    gap: 4px;
}
.personnel-chip {
    display: inline-flex;
    align-items: center;
    border-radius: 9999px;
    background: #e0f2fe;
    padding: 2px 7px;
    color: #0369a1;
    font-size: 0.68rem;
    font-weight: 600;
}
.personnel-placeholder {
    color: #94a3b8;
}
.personnel-chevron {
    flex: 0 0 auto;
    color: #64748b;
    font-size: 0.65rem;
}
.personnel-menu {
    position: absolute;
    z-index: 100;
    top: calc(100% + 4px);
    left: 0;
    width: 260px;
    overflow: hidden;
    border: 1px solid #cbd5e1;
    border-radius: 0.6rem;
    background: #fff;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.16);
}
.personnel-search-wrap {
    display: flex;
    align-items: center;
    gap: 7px;
    border-bottom: 1px solid #e2e8f0;
    padding: 8px 10px;
    color: #64748b;
}
.personnel-search {
    width: 100%;
    border: 0;
    background: transparent;
    color: #334155;
    font-size: 0.72rem;
    outline: none;
}
.personnel-options {
    max-height: 220px;
    overflow-y: auto;
    padding: 5px;
}
.personnel-option {
    display: flex;
    cursor: pointer;
    align-items: flex-start;
    gap: 8px;
    border-radius: 0.4rem;
    padding: 7px 8px;
    color: #334155;
    font-size: 0.72rem;
}
.personnel-option:hover {
    background: #f8fafc;
}
.personnel-option input {
    margin-top: 2px;
}
.personnel-option-name {
    display: block;
    font-weight: 600;
}
.personnel-option-position {
    display: block;
    margin-top: 1px;
    color: #94a3b8;
    font-size: 0.65rem;
}
.personnel-empty {
    padding: 10px;
    color: #94a3b8;
    font-size: 0.7rem;
    text-align: center;
}
.builder-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-width: 76px;
}
.builder-actions .builder-distribute,
.builder-actions .builder-delete {
    width: 32px;
    min-width: 32px;
    height: 30px;
    min-height: 30px;
    padding: 0;
}
.builder-distribute {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    min-height: 30px;
    border: 1px solid #bae6fd;
    border-radius: 0.45rem;
    background: #f0f9ff;
    color: #0284c7;
}
.builder-distribute:hover {
    background: #e0f2fe;
}
.builder-distribute:disabled {
    cursor: not-allowed;
    opacity: 0.45;
}
.financial-target-total {
    white-space: nowrap;
    font-size: 0.72rem;
}
.financial-target-difference {
    white-space: nowrap;
    line-height: 1.15;
}
.builder-delete {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 30px;
    min-height: 30px;
    border: 1px solid #fecdd3;
    border-radius: 0.45rem;
    background: #fff1f2;
    color: #e11d48;
}
.builder-delete:hover {
    background: #ffe4e6;
}
#builderBody tr[data-row-type="header"] td {
    background: #e2e8f0;
    font-weight: 700;
}
#builderBody tr[data-row-type="subheader"] td {
    background: #f8fafc;
    font-weight: 600;
}
@media (max-width: 767px) {
    .builder-table-wrap {
        min-height: 280px;
    }
}
</style>
