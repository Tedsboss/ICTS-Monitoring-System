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
                        <label for="officeName"
                            class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Name of Office/Staff
                        </label>

                        @if($isAdmin ?? false)

                            {{-- ADMIN: select which Staff/Office owns the Financial Plan --}}
                            <select id="officeSelector"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">

                                <option value="">Select Office/Staff</option>

                                @foreach(($staffOptions ?? []) as $staff)
                                    <option value="{{ $staff->id }}"
                                            data-office-name="{{ $staff->name }}"
                                            {{ (int)($personnelStaffId ?? 0) === (int)$staff->id ? 'selected' : '' }}>
                                        {{ $staff->name }}

                                        @if(!empty($staff->abbreviation))
                                            ({{ $staff->abbreviation }})
                                        @endif
                                    </option>
                                @endforeach

                            </select>

                            {{-- Actual value used by the Financial Plan requests --}}
                            <input type="hidden"
                                id="officeName"
                                value="{{ $officeName ?? '' }}">

                        @else

                            {{-- STAFF: allow Office/Staff name to be entered for a new plan --}}
                            <input type="text"
                                id="officeName"
                                value="{{ $officeName ?? '' }}"
                                maxlength="150"
                                placeholder="Enter Name of Office/Staff"
                                autocomplete="off"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">

                        @endif
                    </div>
                </div>
            </section>
            <div id="lockedNotice" class="hidden border-b border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                <i class="fa fa-lock mr-1"></i>
                <span id="lockedNoticeText">This plan is read-only.</span>
            </div>
            <section class="border-b border-slate-200 p-4">
                <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="m-0 text-base font-bold text-slate-900">Allocation Management</h2>
                        <p class="mb-0 mt-1 text-xs text-slate-500">
                            Select the allocation that will be used by this Financial Plan.
                        </p>
                    </div>
                    <span id="allocationManagementStatus"
                        class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-[11px] font-semibold text-slate-600">
                        Not selected
                    </span>
                </div>

                <div class="grid gap-3 lg:grid-cols-2">
                    <div>
                        <label for="allocationSelector"
                            class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Allocation
                        </label>

                        <select id="allocationSelector"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                            <option value="">Select Allocation</option>

                            @foreach(($allocations ?? []) as $allocation)
                                <option value="{{ $allocation->id }}"
                                        data-year="{{ $allocation->fiscalYear?->year }}"
                                        data-level="{{ $allocation->level?->level_code }}"
                                        data-level-description="{{ $allocation->level?->level_description }}">
                                    {{ $allocation->fiscalYear?->year }}
                                    — {{ $allocation->level?->level_code }}
                                    @if($allocation->level?->level_description)
                                        ({{ $allocation->level->level_description }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="allocationLevel"
                            class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Selected Level
                        </label>

                        <input type="text"
                            id="allocationLevel"
                            value=""
                            readonly
                            placeholder="Select an Allocation first"
                            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700 outline-none">
                    </div>
                </div>

                <div id="allocationExpenseSummary" class="mt-4 hidden">
                    <div class="mb-2 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wide text-slate-600">
                                Allocation Expenses
                            </div>
                            <div class="mt-0.5 text-[11px] text-slate-500">
                                Budget configured in Allocation Management.
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3 text-right">
                            <div>
                                <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Total Allocation</div>
                                <div id="allocationManagementTotal" class="text-base font-bold text-slate-900">0.00</div>
                            </div>
                            <div>
                                <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Programmed</div>
                                <div id="allocationManagementProgrammed" class="text-base font-bold text-sky-700">0.00</div>
                            </div>
                            <div>
                                <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Remaining</div>
                                <div id="allocationManagementRemaining" class="text-base font-bold text-emerald-700">0.00</div>
                            </div>
                        </div>
                    </div>

                    <div id="allocationExpenseCards"
                        class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    </div>
                </div>

                <div id="allocationManagementEmpty"
                    class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800">
                    <i class="fa fa-info-circle mr-1"></i>
                    Select an Allocation to view its configured expenses.
                </div>
            </section>
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
    // Controlled Program Classification hierarchy.
    const PROGRAM_CLASSIFICATION_TREE = @json($programClassificationTree ?? []);
    // Allocation Types are configured for the current Staff/Office.
    // The Builder must not assume that every office uses MITHI/NINP.
    const ALLOCATION_TYPE_OPTIONS = @json($allocationTypeOptions ?? []);
    window.PLAN_STAFF_ID = Number(@json($personnelStaffId ?? 0));
    const IS_ADMIN = @json($isAdmin ?? false);
    const RAW_ALLOCATIONS = @json($allocations ?? []);
    const ALLOCATIONS = Array.isArray(RAW_ALLOCATIONS)
        ? RAW_ALLOCATIONS.map(allocation => ({
            ...allocation,
            id: Number(allocation.id || 0),
            fiscalYear: allocation.fiscalYear || allocation.fiscal_year || {},
            level: allocation.level || {},
            expenses: Array.isArray(allocation.expenses) ? allocation.expenses : []
        }))
        : [];
    let SELECTED_ALLOCATION_ID = null;

    function syncSelectedOffice() {
        const $selector = $('#officeSelector');

        // Staff accounts do not have an officeSelector.
        if (!$selector.length) {
            return;
        }

        const $selected = $selector.find('option:selected');

        const staffId = parseInt($selected.val(), 10) || null;
        const officeName = String(
            $selected.data('office-name') || ''
        ).trim();

        window.PLAN_STAFF_ID = staffId;

        $('#officeName').val(officeName);

        console.log('Selected Office:', {
            staff_id: window.PLAN_STAFF_ID,
            office_name: officeName
        });
    }

    $('#officeSelector').on('change', function () {
        if (hasUnsavedChanges) {
            const proceed = confirm(
                'You have unsaved changes. Changing Staff/Office will discard them. Continue?'
            );
            if (!proceed) {
                return;
            }
        }

        syncSelectedOffice();

        if (!window.PLAN_STAFF_ID) {
            return;
        }

        const url = new URL('{{ route('financial-plans.builder') }}', window.location.origin);
        url.searchParams.set('fiscal_year', $('#fiscalYear').val() || '');
        url.searchParams.set('office_name', $('#officeName').val() || '');
        window.location.href = url.toString();
    });

    @if(!($isAdmin ?? false))
    $('#officeName').on('input', function () {

        const officeName = String($(this).val() || '').trim();

        if (officeName !== '') {
            setDirty(true);
        }

    });

    @endif

    function cleanHierarchyLabel(value = '') {
        return String(value ?? '')
            .trim()
            .replace(/^[A-Z]\.\s*/, '')
            .replace(/^[IVX]+\.\s*/, '');
    }

    function getPreviousHierarchyContext($tr) {
        let header = '';
        let subHeader = '';

        $tr.prevAll('tr').get().reverse().forEach(row => {
            const $row = $(row);
            const type = $row.attr('data-row-type');
            const value = String(
                $row.find('[data-field="program_classification"]').val() || ''
            ).trim();

            if (type === 'header') {
                header = cleanHierarchyLabel(value);
                subHeader = '';
            }

            if (type === 'subheader' && header && !subHeader) {
                subHeader = cleanHierarchyLabel(value);
            }
        });

        return { header, subHeader };
    }

    function findHeaderByLabel(label = '') {
        const target = cleanHierarchyLabel(label).toLowerCase();
        return PROGRAM_CLASSIFICATION_TREE.find(header =>
            cleanHierarchyLabel(header.header).toLowerCase() === target
        ) || null;
    }

    function findSubHeaderByLabel(header, label = '') {
        if (!header) return null;
        const target = cleanHierarchyLabel(label).toLowerCase();
        return (header.sub_headers || []).find(subHeader =>
            cleanHierarchyLabel(subHeader.sub_header).toLowerCase() === target
        ) || null;
    }

    function classificationOptionsFromTree(
        selectedValue = '',
        selectedPrexc = '',
        headerLabel = '',
        subHeaderLabel = ''
    ) {
        const selected = String(selectedValue ?? '').trim();
        const selectedCode = String(selectedPrexc ?? '').trim();
        const html = ['<option value="">Select Classification</option>'];
        let selectedFound = false;

        const header = findHeaderByLabel(headerLabel);

        if (!header) {
            if (selected) {
                html.push(
                    `<option value="${esc(selected)}" data-prexc="${esc(selectedCode)}" selected>${esc(selected)} (Legacy)</option>`
                );
            }
            return html.join('');
        }

        const subHeader = findSubHeaderByLabel(header, subHeaderLabel);
        const subHeaders = subHeader ? [subHeader] : (header.sub_headers || []);

        subHeaders.forEach(currentSubHeader => {
            (currentSubHeader.programs || []).forEach(program => {
                const programLabel = String(program.program ?? '').trim();
                const expenditures = program.expenditures || [];
                if (!expenditures.length) return;

                const groupParts = [];

                if (cleanHierarchyLabel(currentSubHeader.sub_header)) {
                    groupParts.push(cleanHierarchyLabel(currentSubHeader.sub_header));
                }

                if (programLabel && programLabel !== cleanHierarchyLabel(currentSubHeader.sub_header)) {
                    groupParts.push(programLabel);
                }

                const $group = $('<optgroup>', {
                    label: groupParts.join(' — ')
                });

                expenditures.forEach(expenditure => {
                    const classification = String(expenditure.expenditure ?? '').trim();
                    const prexc = String(expenditure.prexc ?? '').trim();
                    if (!classification || !prexc) return;

                    const isSelected =
                        classification === selected &&
                        (!selectedCode || prexc === selectedCode);

                    if (isSelected) selectedFound = true;

                    $group.append($('<option>', {
                        value: classification,
                        text: classification,
                        selected: isSelected,
                        'data-prexc': prexc,
                        'data-program-id': String(program.id || ''),
                        'data-expenditure-id': String(expenditure.id || '')
                    }));
                });

                if ($group.children().length) {
                    html.push($group.prop('outerHTML'));
                }
            });
        });

        if (selected && !selectedFound) {
            html.splice(
                1,
                0,
                `<option value="${esc(selected)}" data-prexc="${esc(selectedCode)}" selected>${esc(selected)} (Legacy)</option>`
            );
        }

        return html.join('');
    }

    function subHeaderOptionsForRow($tr, selectedValue = '') {
        const selected = cleanHierarchyLabel(selectedValue);
        const contextHeader = getPreviousHierarchyContext($tr).header;
        const header = findHeaderByLabel(contextHeader);
        const html = ['<option value="">Select Sub Header</option>'];

        const subHeaders = header
            ? (header.sub_headers || [])
            : PROGRAM_CLASSIFICATION_TREE.flatMap(item => item.sub_headers || []);

        const seen = new Set();

        subHeaders.forEach(subHeader => {
            const label = cleanHierarchyLabel(subHeader.sub_header);
            if (!label || seen.has(label.toLowerCase())) return;

            seen.add(label.toLowerCase());

            html.push(
                `<option value="${esc(label)}" ${label === selected ? 'selected' : ''}>${esc(label)}</option>`
            );
        });

        return html.join('');
    }

    function allHeaderOptions(selectedValue = '') {
        const selected = cleanHierarchyLabel(selectedValue);
        const html = ['<option value="">Select Header</option>'];

        PROGRAM_CLASSIFICATION_TREE.forEach(header => {
            const label = cleanHierarchyLabel(header.header);
            if (!label) return;

            html.push(
                `<option value="${esc(label)}" ${label === selected ? 'selected' : ''}>${esc(label)}</option>`
            );
        });

        return html.join('');
    }

    function programClassificationControl(row = {}, isItem = true, disabled = false, $tr = null) {
        const classification = String(row.program_classification ?? '').trim();
        const prexc = String(row.prexc_code ?? '').trim();

        if (!isItem) {
            const options = row.row_type === 'header'
                ? allHeaderOptions(classification)
                : subHeaderOptionsForRow($tr || $('<tr>'), classification);

            return `
                <select class="builder-input field-input program-structural-select program-classification-input"
                        data-field="program_classification"
                        ${disabled ? 'disabled' : ''}>
                    ${options}
                </select>
            `;
        }

        const context = $tr && $tr.length
            ? getPreviousHierarchyContext($tr)
            : { header: '', subHeader: '' };

        return `
            <select class="builder-input field-input program-classification-input"
                    data-field="program_classification"
                    ${disabled ? 'disabled' : ''}>
                ${classificationOptionsFromTree(
                    classification,
                    prexc,
                    context.header,
                    context.subHeader
                )}
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
    // Expense Items for new Financial Plans come from the selected Allocation and its Allocation Expenses. Legacy saved values are preserved below.
    window.ALLOCATION_EXPENSE_OPTIONS = @json($allocationExpenseOptions ?? []);

    function getSelectedAllocationExpenseItems() {
        const allocationId = Number(SELECTED_ALLOCATION_ID || $('#allocationSelector').val() || 0);
        const allocation = (Array.isArray(window.ALLOCATION_EXPENSE_OPTIONS)
            ? window.ALLOCATION_EXPENSE_OPTIONS
            : []
        ).find(item => Number(item.allocation_id) === allocationId);

        return Array.isArray(allocation?.items) ? allocation.items : [];
    }

    function expenseItemOptions(selectedValue = '') {
        const selected = String(selectedValue ?? '').trim();
        const items = getSelectedAllocationExpenseItems();
        const options = items
            .map(item => ({
                name: String(item.name ?? '').trim(),
                type: String(item.type ?? '').trim()
            }))
            .filter(item => item.name !== '');

        const selectedFound = options.some(item => item.name === selected);
        const html = ['<option value="">Select Expense Item</option>'];

        if (selected && !selectedFound) {
            html.push(
                `<option value="${esc(selected)}" selected>${esc(selected)} (Legacy / Unavailable)</option>`
            );
        }

        options.forEach(item => {
            html.push(
                `<option value="${esc(item.name)}" ${item.name === selected ? 'selected' : ''}>${esc(item.name)}</option>`
            );
        });

        return html.join('');
    }

    function refreshExpenseItemDropdowns() {
        $('#builderBody tr[data-row-type="item"]').each(function () {
            const $tr = $(this);
            const selected = String(
                $tr.find('[data-field="expense_item"]').val() || ''
            ).trim();

            $tr.find('[data-field="expense_item"]').html(
                expenseItemOptions(selected)
            );
        });
    }

    let activeLoadRequest = null;
    let hasUnsavedChanges = false;
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
    function refreshAllocationProgrammedSummary() {
        const allocation = ALLOCATIONS.find(item => Number(item.id) === Number(SELECTED_ALLOCATION_ID || 0));
        const $programmed = $('#allocationManagementProgrammed');
        const $remaining = $('#allocationManagementRemaining');
        if (!$programmed.length || !$remaining.length) return;
        if (!allocation) {
            $programmed.text('0.00');
            $remaining.text('0.00').removeClass('text-rose-700').addClass('text-emerald-700');
            return;
        }
        let programmed = 0;
        $('#builderBody tr[data-row-type="item"]').each(function () {
            const info = getEffectiveFinancialTarget($(this));
            programmed += Number(info.effectiveBudget) || 0;
        });
        const allocationTotal = (Array.isArray(allocation.expenses) ? allocation.expenses : [])
            .reduce((sum, expense) => sum + (Number(expense.cost) || 0), 0);
        const remaining = allocationTotal - programmed;
        $programmed.text(fmtNum(programmed));
        $remaining.text(fmtNum(remaining))
            .removeClass('text-emerald-700 text-rose-700')
            .addClass(remaining < -0.01 ? 'text-rose-700' : 'text-emerald-700');
    }
    function renderAllocationManagement(allocationId = '') {
        const selectedId = Number(allocationId || 0);
        const allocation = ALLOCATIONS.find(item => Number(item.id) === selectedId);

        SELECTED_ALLOCATION_ID = allocation ? Number(allocation.id) : null;

        const $selector = $('#allocationSelector');
        const $level = $('#allocationLevel');
        const $summary = $('#allocationExpenseSummary');
        const $empty = $('#allocationManagementEmpty');
        const $cards = $('#allocationExpenseCards');
        const $total = $('#allocationManagementTotal');
        const $status = $('#allocationManagementStatus');

        $selector.val(allocation ? String(allocation.id) : '');
        $cards.empty();
        $total.text('0.00');
        $('#allocationManagementProgrammed, #allocationManagementRemaining').text('0.00');

        if (!allocation) {
            $level
                .val('')
                .attr('placeholder', 'Select an Allocation first');

            $summary.addClass('hidden');
            $empty.removeClass('hidden');

            $status
                .removeClass('bg-emerald-100 text-emerald-700')
                .addClass('bg-slate-100 text-slate-600')
                .text('Not selected');

            return;
        }

        const levelCode = allocation.level?.level_code || '';
        const levelDescription = allocation.level?.level_description || '';

        $level
            .val(levelDescription ? `${levelCode} — ${levelDescription}` : levelCode)
            .attr('placeholder', '');

        let total = 0;

        const expenses = Array.isArray(allocation.expenses)
            ? allocation.expenses
            : [];

        expenses.forEach(expense => {
            const expenseType = expense.expense_type || expense.expenseType || {};
            const type = String(expenseType.type || '').trim();
            const description = String(expenseType.expense_description || '').trim();
            const cost = Number(expense.cost || 0);

            total += cost;

            $cards.append(`
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-wide text-slate-500">
                                ${esc(type)}
                            </div>
                            <div class="mt-1 text-xs font-semibold text-slate-800">
                                ${esc(description)}
                            </div>
                        </div>
                        <div class="text-right text-sm font-bold text-slate-900">
                            ${fmtNum(cost)}
                        </div>
                    </div>
                </div>
            `);
        });

        $total.text(fmtNum(total));
        refreshAllocationProgrammedSummary();
        $summary.removeClass('hidden');
        $empty.toggleClass('hidden', expenses.length > 0);

        $status
            .removeClass('bg-slate-100 text-slate-600')
            .addClass('bg-emerald-100 text-emerald-700')
            .text('Selected');
    }

    function refreshAllocationOptions() {
        const fiscalYear = Number($('#fiscalYear').val() || 0);
        const currentId = Number(SELECTED_ALLOCATION_ID || 0);
        const $selector = $('#allocationSelector');
        const matchingAllocations = ALLOCATIONS.filter(allocation => {
            const allocationYear = Number(
                allocation.fiscalYear?.year
                ?? allocation.fiscal_year?.year
                ?? allocation.fiscal_year
                ?? 0
            );
            return allocationYear === fiscalYear;
        });

        $selector.empty().append('<option value="">Select Allocation</option>');

        matchingAllocations.forEach(allocation => {
            const levelCode = String(allocation.level?.level_code ?? '').trim();
            const levelDescription = String(allocation.level?.level_description ?? '').trim();
            const label = levelDescription
                ? `${fiscalYear} — ${levelCode} (${levelDescription})`
                : `${fiscalYear} — ${levelCode}`;
            $selector.append($('<option>', {
                value: String(allocation.id),
                text: label
            }));
        });

        if (currentId && matchingAllocations.some(allocation => Number(allocation.id) === currentId)) {
            $selector.val(String(currentId));
        } else {
            renderAllocationManagement('');
        }

        console.log('Allocation options refreshed:', {
            fiscal_year: fiscalYear,
            total_allocations: ALLOCATIONS.length,
            matching_allocations: matchingAllocations.length,
            allocations: matchingAllocations
        });
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
        $('#unsavedBadge').toggleClass('hidden', !hasUnsavedChanges);
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

        $wrap.html(programClassificationControl({
            row_type: type,
            program_classification: currentValue,
            prexc_code: currentPrexc
        }, isItem, isLocked, $tr));

        if (!isItem) return;

        const $classification = $wrap.find('.program-classification-input');
        const $option = $classification.find('option:selected');
        const prexc = String($option.data('prexc') ?? currentPrexc).trim();
        $tr.find('[data-field="prexc_code"]').val(prexc);
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
    function refreshProgramClassificationDisplay() {
        $('#builderBody tr').each(function () {
            const $tr = $(this);
            const type = $tr.attr('data-row-type');

            if (type === 'header' || type === 'subheader') {
                const currentValue = String(
                    $tr.find('[data-field="program_classification"]').val() || ''
                ).trim();

                $tr.find('.program-classification-wrap').html(
                    programClassificationControl({
                        row_type: type,
                        program_classification: currentValue
                    }, false, isLocked, $tr)
                );

                return;
            }

            if (type !== 'item') return;

            const currentValue = String(
                $tr.find('[data-field="program_classification"]').val() || ''
            ).trim();

            const currentPrexc = String(
                $tr.find('[data-field="prexc_code"]').val() || ''
            ).trim();

            $tr.find('.program-classification-wrap').html(
                programClassificationControl({
                    row_type: 'item',
                    program_classification: currentValue,
                    prexc_code: currentPrexc
                }, true, isLocked, $tr)
            );

            const $option = $tr.find('.program-classification-input option:selected');
            const prexc = String($option.data('prexc') ?? '').trim();

            $tr.find('[data-field="prexc_code"]').val(
                prexc || currentPrexc
            );
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
        refreshAllocationProgrammedSummary();
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
        refreshAllocationProgrammedSummary();
        setDirty(true);
        refreshSubmissionReadiness();
    }
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
    // Load the selected plan
    function loadPlan(manualReload = false) {

        if (manualReload && hasUnsavedChanges) {
            if (!confirm(
                'You have unsaved changes. Loading another plan will discard them. Continue?'
            )) {
                return;
            }
        }

        /*
        * ADMIN:
        * Synchronize selected Staff/Office FIRST.
        *
        * This updates:
        *   window.PLAN_STAFF_ID
        *   #officeName
        */
        if ($('#officeSelector').length) {
            syncSelectedOffice();
        }

        const fiscalYear = $('#fiscalYear').val();
        const officeName = String(
            $('#officeName').val() || ''
        ).trim();

        if (!officeName) {
            showMessage('Please select a Staff/Office.', 'danger');
            return;
        }

        if (!IS_ADMIN && !window.PLAN_STAFF_ID) {
            showMessage(
                'Unable to determine the selected Staff/Office.',
                'danger'
            );
            return;
        }

        if (
            activeLoadRequest &&
            activeLoadRequest.readyState !== 4
        ) {
            activeLoadRequest.abort();
        }

        setLoading(true);

        $.when(
            loadStatus(),
            loadSignatories()
        ).always(function () {

            activeLoadRequest = $.getJSON(
                '{{ route("financial-plans.data") }}',
                {
                    fiscal_year: fiscalYear,
                    office_name: officeName
                }
            ).done(function (rows) {
                const existingAllocationId = rows.length
                    ? Number(rows[0].allocation_id || 0)
                    : 0;

                renderAllocationManagement(existingAllocationId);
                renderRows(rows);

                if (rows.length === 0 && !isLocked) {
                    addRow('header');
                    addRow('item');
                }

                setDirty(false);
        
            }).fail(function (xhr) {

                showMessage(
                    getErrorMessage(
                        xhr,
                        'Failed to load the financial plan.'
                    ),
                    'danger'
                );

            }).always(function () {

                setLoading(false);

                applyLockState(
                    isLocked,
                    currentWorkflowStatus,
                    currentFinalized
                );
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
            fiscal_year: Number($('#fiscalYear').val()),
            office_name: String($('#officeName').val() || '').trim(),
            allocation_id: SELECTED_ALLOCATION_ID,
            rows
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
    // Compare the Financial Plan against the selected Allocation Management expenses.
    // MOOE and CO are checked separately; Contract Amount is the effective total.
    function getAllocationExpenseCeilingIssues() {
        const allocation = ALLOCATIONS.find(item => Number(item.id) === Number(SELECTED_ALLOCATION_ID || 0));
        if (!allocation) {
            return [{
                type: 'error',
                message: 'Please select an Allocation Management allocation before entering budget amounts.'
            }];
        }

        const ceilings = { mooe: 0, capitalOutlay: 0 };
        (Array.isArray(allocation.expenses) ? allocation.expenses : []).forEach(expense => {
            const expenseType = expense.expense_type || expense.expenseType || {};
            const type = String(expenseType.type || '').trim().toUpperCase();
            const cost = Number(expense.cost || 0);
            if (type === 'MOOE') {
                ceilings.mooe += cost;
            } else if (type === 'CO' || type === 'CAPITAL OUTLAY') {
                ceilings.capitalOutlay += cost;
            }
        });

        let programmedMooe = 0;
        let programmedCapitalOutlay = 0;
        let contractTotal = 0;
        let effectiveTotal = 0;

        $('#builderBody tr[data-row-type="item"]').each(function () {
            const info = getEffectiveFinancialTarget($(this));
            programmedMooe += info.effectiveMooe;
            programmedCapitalOutlay += info.effectiveCo;
            effectiveTotal += info.effectiveBudget;
            if (info.contractAmount !== null && Number.isFinite(info.contractAmount)) {
                contractTotal += info.contractAmount;
            }
        });

        const issues = [];
        if (programmedMooe > ceilings.mooe + 0.01) {
            issues.push({
                type: 'error',
                message: `Financial Plan MOOE exceeds the Allocation Management MOOE by ${fmtNum(programmedMooe - ceilings.mooe)}.`
            });
        }
        if (programmedCapitalOutlay > ceilings.capitalOutlay + 0.01) {
            issues.push({
                type: 'error',
                message: `Financial Plan Capital Outlay exceeds the Allocation Management Capital Outlay by ${fmtNum(programmedCapitalOutlay - ceilings.capitalOutlay)}.`
            });
        }
        const allocationTotal = ceilings.mooe + ceilings.capitalOutlay;
        if (effectiveTotal > allocationTotal + 0.01) {
            issues.push({
                type: 'error',
                message: `Financial Plan total exceeds the Allocation Management total by ${fmtNum(effectiveTotal - allocationTotal)}.`
            });
        }
        if (contractTotal > allocationTotal + 0.01) {
            issues.push({
                type: 'error',
                message: `Total Contract Amount exceeds the Allocation Management total by ${fmtNum(contractTotal - allocationTotal)}.`
            });
        }

        return issues;
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
                    const validHierarchyPair = PROGRAM_CLASSIFICATION_TREE.some(header =>
                        (header.sub_headers || []).some(subHeader =>
                            (subHeader.programs || []).some(program =>
                                (program.expenditures || []).some(expenditure =>
                                    String(expenditure.expenditure ?? '').trim() === classification
                                    && String(expenditure.prexc ?? '').trim() === prexcCode
                                )
                            )
                        )
                    );
                    if (!validHierarchyPair) {
                        issues.push({
                            type: 'error',
                            message: `${label}: Program Classification and PREXC Code do not match the active Program Classification hierarchy.`
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
        getAllocationExpenseCeilingIssues().forEach(issue => issues.push(issue));
        return {
            ready: issues.length === 0,
            issues
        };
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

        if (!SELECTED_ALLOCATION_ID) {
            showMessage(
                'Please select an Allocation Management allocation before saving the Financial Plan.',
                'danger'
            );
            return;
        }
        
        const payload = collectPayload();
        const allocationCeilingIssues = getAllocationExpenseCeilingIssues();
        if (allocationCeilingIssues.length) {
            showMessage(
                allocationCeilingIssues.map(issue => issue.message).join(' '),
                'danger'
            );
            return;
        }
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
            prefill.allocation_type = $sourceRow.find('[data-field="allocation_type"]').val() || '';
        }
        const $newRow = $(fieldRow(prefill));
        $currentRow.after($newRow);
        rememberEffectiveBudget($newRow);
        refreshFinancialTargetDisplay($newRow);
        $newRow.find('.specific-activity-input').each(function () {
            autoGrow(this);
        });
        refreshProgramClassificationDisplay();
        refreshAllocationProgrammedSummary();
        setDirty(true);
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
    });
    // Header/Sub Header selections drive the available Budget Line classifications.
    // Budget Line classification automatically fills the PREXC code.
    $('#builderBody').on('change', '.program-classification-input', function () {
        if (isLocked) return;

        const $tr = $(this).closest('tr');
        const rowType = $tr.attr('data-row-type');

        if (rowType === 'item') {
            const $option = $(this).find('option:selected');
            const prexc = String($option.data('prexc') ?? '').trim();
            $tr.find('[data-field="prexc_code"]').val(prexc);
        } else {
            refreshProgramClassificationDisplay();
        }

        setDirty(true);
        refreshSubmissionReadiness();
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
    $('#allocationSelector').on('change', function () {
        const allocationId = Number($(this).val() || 0);

        if (hasUnsavedChanges) {
            const proceed = confirm(
                'Changing the Allocation will change the allocation linked to this Financial Plan. Continue?'
            );

            if (!proceed) {
                renderAllocationManagement(SELECTED_ALLOCATION_ID);
                return;
            }
        }

        renderAllocationManagement(allocationId);
        refreshExpenseItemDropdowns();
        refreshAllocationProgrammedSummary();
        setDirty(true);
        refreshSubmissionReadiness();
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
            } else if ($(this).hasClass('month-input')) {
            refreshFinancialTargetDisplay($tr);
        } else if (field === 'prexc_code') {
            }
        refreshAllocationProgrammedSummary();
        refreshAllocationProgrammedSummary();
        refreshSubmissionReadiness();
    });
    // Financial Target distribution.
    $('#builderBody').on('click', '.btn-distribute-target', function () {
        openTargetDistribution($(this).closest('tr'));
    });
    $('#fiscalYear').on('change', function () {
        if (hasUnsavedChanges) {
            const proceed = confirm(
                'Changing the Fiscal Year may change the available Allocations. Continue?'
            );

            if (!proceed) {
                return;
            }
        }

        SELECTED_ALLOCATION_ID = null;
        refreshAllocationOptions();
        renderAllocationManagement('');
        refreshAllocationProgrammedSummary();

        setDirty(true);
        refreshSubmissionReadiness();
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
        refreshAllocationProgrammedSummary();
        setDirty(true);
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
    $('#btnLoadPlan').on('click', function () {
        loadPlan(true);
    });
    // Warn before leaving with unsaved changes
    window.addEventListener('beforeunload', function (event) {
        if (!hasUnsavedChanges) {
            return;
        }
        event.preventDefault();
        event.returnValue = '';
    });
    enableRowDragging();
    // Build the Allocation dropdown immediately from Allocation Management data.
    // This is required on initial page load; the fiscal-year handler also refreshes it later.
    refreshAllocationOptions();
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
