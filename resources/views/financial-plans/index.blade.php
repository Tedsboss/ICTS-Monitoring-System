@extends('layouts.app')

@section('content')

<nav

    class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"

    id="navbarBlur"

    data-scroll="false"

>

    <div class="container-fluid py-2 px-3">

        @include('layouts.navbars.auth.topnav', ['title' => 'FY ' . $fiscalYear . ' Financial Plan'])

        @include('layouts.navbars.auth.topnav-withdatetime')

    </div>

</nav>

<div class="px-4 pb-8 pt-4">

    {{-- Back --}}

    <div class="mb-4">

        <a

            href="{{ route('financial-plans.plans') }}"

            class="inline-flex items-center gap-2 rounded-lg border border-slate-300

                   bg-white px-4 py-2 text-sm font-semibold text-slate-700

                   shadow-sm transition hover:bg-slate-50"

        >

            <i class="fa fa-arrow-left"></i>

            <span>Back to All Plans</span>

        </a>

    </div>



    {{-- Page Messages --}}

    <div id="pageMessage"></div>



    {{-- Plan Controls --}}

    <section class="wfp-control-card mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="wfp-control-layout">

            {{-- Workflow Actions --}}

            <div class="wfp-control-actions">

                <button

                    type="button"

                    id="btnEditPlan"

                    class="inline-flex items-center gap-2 rounded-lg bg-sky-600

                           px-4 py-2 text-sm font-semibold text-white shadow-sm

                           transition hover:bg-sky-700 disabled:cursor-not-allowed

                           disabled:bg-slate-300"

                >

                    <i class="fa fa-pencil"></i>

                    <span>Edit This Plan</span>

                </button>



                <button

                    type="button"

                    id="btnDownloadPdf"

                    class="inline-flex items-center gap-2 rounded-lg border

                           border-emerald-200 bg-emerald-50 px-4 py-2 text-sm

                           font-semibold text-emerald-700 transition

                           hover:bg-emerald-100 disabled:opacity-60"

                >

                    <i class="fa fa-file-pdf-o"></i>

                    <span>Download PDF</span>

                </button>



                <button

                    type="button"

                    id="btnSubmit"

                    class="d-none inline-flex items-center gap-2 rounded-lg

                           border border-cyan-200 bg-cyan-50 px-4 py-2

                           text-sm font-semibold text-cyan-700 transition

                           hover:bg-cyan-100 disabled:opacity-60"

                >

                    <i class="fa fa-paper-plane"></i>

                    <span>Submit for Approval</span>

                </button>



                <button

                    type="button"

                    id="btnApprove"

                    class="d-none inline-flex items-center gap-2 rounded-lg

                           border border-emerald-200 bg-emerald-50 px-4 py-2

                           text-sm font-semibold text-emerald-700 transition

                           hover:bg-emerald-100 disabled:opacity-60"

                >

                    <i class="fa fa-check"></i>

                    <span>Approve</span>

                </button>



                <button

                    type="button"

                    id="btnReturn"

                    class="d-none inline-flex items-center gap-2 rounded-lg

                           border border-amber-200 bg-amber-50 px-4 py-2

                           text-sm font-semibold text-amber-700 transition

                           hover:bg-amber-100 disabled:opacity-60"

                >

                    <i class="fa fa-undo"></i>

                    <span>Return</span>

                </button>



                <button

                    type="button"

                    id="btnFinalize"

                    class="d-none inline-flex items-center gap-2 rounded-lg

                           border border-slate-300 bg-slate-100 px-4 py-2

                           text-sm font-semibold text-slate-700 transition

                           hover:bg-slate-200 disabled:opacity-60"

                >

                    <i class="fa fa-lock"></i>

                    <span>Finalize Plan</span>

                </button>



                <button

                    type="button"

                    id="btnReopen"

                    class="d-none inline-flex items-center gap-2 rounded-lg

                           border border-amber-200 bg-amber-50 px-4 py-2

                           text-sm font-semibold text-amber-700 transition

                           hover:bg-amber-100 disabled:opacity-60"

                >

                    <i class="fa fa-unlock"></i>

                    <span>Reopen for Editing</span>

                </button>



                <span

                    id="planStatusBadge"

                    class="inline-flex items-center rounded-full bg-slate-100

                           px-3 py-1.5 text-xs font-bold text-slate-600"

                >

                    Loading...

                </span>

            </div>



            {{-- Plan Selector --}}

            <div class="wfp-plan-selector">

                <div class="wfp-selector-field wfp-year-field">

                    <label

                        for="filterFiscalYear"

                        class="mb-1.5 block text-xs font-semibold uppercase

                               tracking-wide text-slate-600"

                    >

                        Fiscal Year

                    </label>

                    <input

                        type="number"

                        id="filterFiscalYear"

                        value="{{ $fiscalYear }}"

                        min="2000"

                        max="2100"

                        class="block w-[120px] rounded-lg border border-slate-300

                               bg-white px-3 py-2 text-sm text-slate-700

                               shadow-sm outline-none transition

                               focus:border-sky-500 focus:ring-2 focus:ring-sky-100"

                    >

                </div>



                <div class="wfp-selector-field wfp-office-field">

                    <label

                        for="filterOffice"

                        class="mb-1.5 block text-xs font-semibold uppercase

                               tracking-wide text-slate-600"

                    >

                        Office/Staff

                    </label>

                    <input

                        type="text"

                        id="filterOffice"

                        list="filterOfficeSuggestions"

                        value="{{ $officeName }}"

                        maxlength="150"

                        placeholder="Type an office name"

                        class="block w-full rounded-lg border border-slate-300

                               bg-white px-3 py-2 text-sm text-slate-700

                               shadow-sm outline-none transition

                               placeholder:text-slate-400 focus:border-sky-500

                               focus:ring-2 focus:ring-sky-100 sm:w-[300px]"

                    >

                    <datalist id="filterOfficeSuggestions">

                        @foreach ($offices as $office)

                            <option value="{{ $office }}"></option>

                        @endforeach

                    </datalist>

                </div>



                <button

                    type="button"

                    id="btnLoad"

                    class="inline-flex h-[38px] items-center gap-2 rounded-lg

                           border border-sky-200 bg-sky-50 px-4 text-sm

                           font-semibold text-sky-700 transition hover:bg-sky-100

                           disabled:opacity-60"

                >

                    <i class="fa fa-filter"></i>

                    <span>Load</span>

                </button>

            </div>

        </div>

    </section>



    {{-- Workflow Information --}}

    <section

        id="workflowInfo"

        class="d-none mb-5 rounded-2xl border border-slate-200 bg-white

               p-5 shadow-sm"

    >

        <div class="mb-4">

            <h2 class="text-sm font-bold uppercase tracking-[0.08em] text-slate-700">

                Workflow Information

            </h2>

            <p class="mt-1 text-xs text-slate-500">

                Submission, approval, finalization and return information.

            </p>

        </div>



        <div class="wfp-workflow-grid">

            <div>

                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">

                    Submitted by

                </p>

                <p id="submittedByOut" class="mt-2 text-sm font-semibold text-slate-900">

                    —

                </p>

                <p id="submittedAtOut" class="mt-1 text-xs text-slate-500">

                    —

                </p>

            </div>



            <div>

                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">

                    Approved by

                </p>

                <p id="approvedByOut" class="mt-2 text-sm font-semibold text-slate-900">

                    —

                </p>

                <p id="approvedAtOut" class="mt-1 text-xs text-slate-500">

                    —

                </p>

            </div>



            <div>

                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">

                    Finalized by

                </p>

                <p id="finalizedByOut" class="mt-2 text-sm font-semibold text-slate-900">

                    —

                </p>

                <p id="finalizedAtOut" class="mt-1 text-xs text-slate-500">

                    —

                </p>

            </div>



            <div>

                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">

                    Return remarks

                </p>

                <p

                    id="returnRemarksOut"

                    class="mt-2 break-words text-sm font-semibold text-slate-900"

                >

                    —

                </p>

            </div>

        </div>

    </section>



    {{-- Main Financial Plan --}}

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        {{-- Plan Heading --}}

        <div class="border-b border-slate-200 px-5 py-5">

            <div

                id="planTitle"

                class="text-base font-bold uppercase tracking-wide text-slate-900"

            >

                FY {{ $fiscalYear }} FINANCIAL PLAN

            </div>

            <div

                id="planSubtitle"

                class="mt-1 text-sm italic text-slate-500"

            >

                (FY {{ $fiscalYear }} Internal Allocation per approved {{ $fiscalYear }} GAA)

            </div>

            <div class="mt-2 text-sm text-slate-700">

                Name of Office/Staff:

                <span

                    id="planOfficeName"

                    class="font-bold underline decoration-slate-400 underline-offset-2"

                >

                    {{ $officeName }}

                </span>

            </div>

        </div>



        <div class="p-5">

            {{-- Allocation Summary --}}

            <div class="mb-6">

                <div class="mb-3">

                    <h2 class="text-sm font-bold text-slate-900">

                        Allocation Summary

                    </h2>

                    <p class="mt-1 text-xs text-slate-500">

                        Allocation, programmed amount and remaining balance.

                    </p>

                </div>



                <div class="max-w-3xl overflow-hidden rounded-xl border border-slate-200">

                    <table class="allocation-summary-table w-full">

                        <thead>

                            <tr>

                                <th></th>

                                <th>Allocation</th>

                                <th>Programmed</th>

                                <th>Balance</th>

                            </tr>

                        </thead>

                        <tbody>

                            <tr>

                                <td class="allocation-label">

                                    MOOE (MITHI)

                                </td>

                                <td id="sumMooeAlloc">0.00</td>

                                <td id="sumMooeProg">0.00</td>

                                <td id="sumMooeBalance" class="font-bold">0.00</td>

                            </tr>



                            <tr>

                                <td class="allocation-label">

                                    Capital Outlay (MITHI)

                                </td>

                                <td id="sumCoAlloc">0.00</td>

                                <td id="sumCoProg">0.00</td>

                                <td id="sumCoBalance" class="font-bold">0.00</td>

                            </tr>



                            <tr>

                                <td class="allocation-label">

                                    NINP

                                </td>

                                <td id="sumNinpAlloc">0.00</td>

                                <td id="sumNinpProg">0.00</td>

                                <td id="sumNinpBalance" class="font-bold">0.00</td>

                            </tr>



                            <tr class="allocation-total-row">

                                <td class="allocation-label">

                                    TOTAL

                                </td>

                                <td id="sumTotalAlloc">0.00</td>

                                <td id="sumTotalProg">0.00</td>

                                <td id="sumTotalBalance">0.00</td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>



            {{-- Loading --}}

            <div

                id="tableLoading"

                class="d-none mb-3 rounded-lg border border-sky-100

                       bg-sky-50 px-4 py-3 text-sm text-sky-700"

            >

                <i class="fa fa-spinner fa-spin mr-1"></i>

                Loading financial plan...

            </div>



            {{-- WFP Table --}}

            <div class="financial-plan-table-wrap rounded-xl border border-slate-200">

                <table id="fpTable">

                    <colgroup>

                        <col style="width:220px;">

                        <col style="width:130px;">

                        <col style="width:130px;">

                        <col style="width:200px;">

                        <col style="width:120px;">

                        <col style="width:120px;">

                        <col style="width:110px;">

                        <col style="width:110px;">

                        @for ($i = 1; $i <= 12; $i++)

                            <col style="width:90px;">

                        @endfor

                        <col style="width:120px;">

                    </colgroup>



                    <thead>

                        <tr>

                            <th rowspan="2" class="wrap-cell">

                                DEPDev Program of Expenditure Classification (a)

                            </th>

                            <th rowspan="2" class="prexc-cell">

                                PREXC Code (b)

                            </th>

                            <th rowspan="2" class="wrap-cell">

                                Staffs/Units/Projects Concerned (c)

                            </th>

                            <th rowspan="2" class="wrap-cell">

                                Specific Activity/Project of Staffs/Unit/Project (d)

                            </th>

                            <th rowspan="2" class="wrap-cell">

                                Expense Item

                            </th>

                            <th rowspan="2" class="wrap-cell">

                                Assigned Personnel

                            </th>

                            <th rowspan="2">

                                MOOE

                            </th>

                            <th rowspan="2">

                                Capital Outlay

                            </th>

                            <th colspan="12">

                                Financial Target/Output (₱) (f)

                            </th>

                            <th rowspan="2">

                                TOTAL

                            </th>

                        </tr>



                        <tr>

                            @foreach ($months as $label)

                                <th>{{ $label }}</th>

                            @endforeach

                        </tr>

                    </thead>



                    <tbody id="fpBody"></tbody>



                    <tfoot>

                        <tr class="grand-total-row">

                            <td colspan="6">

                                GRAND TOTAL

                            </td>

                            <td id="totMooe">0.00</td>

                            <td id="totCo">0.00</td>

                            @for ($i = 1; $i <= 12; $i++)

                                <td id="totM{{ $i }}">0.00</td>

                            @endfor

                            <td id="totGrand">0.00</td>

                        </tr>

                    </tfoot>

                </table>

            </div>

        </div>

    </section>



    {{-- Footer --}}

    <div class="mt-6">

        @include('layouts.footers.auth.footer')

    </div>

</div>

@endsection



@push('css')

<style>
    /* Isolate the WFP toolbar from Bootstrap / Argon layout rules. */
    .wfp-control-card { padding: 18px 20px; }
    .wfp-control-layout { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: end; gap: 20px; min-height: 0; }
    .wfp-control-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; min-width: 0; }
    .wfp-control-actions > button, .wfp-control-actions > span { width: auto !important; margin: 0 !important; flex: 0 0 auto; }
    .wfp-plan-selector { display: grid; grid-template-columns: 110px minmax(260px, 340px) auto; align-items: end; gap: 10px; }
    .wfp-selector-field { min-width: 0; }
    .wfp-year-field input, .wfp-office-field input { width: 100% !important; min-height: 38px; margin: 0 !important; }
    #btnLoad { width: auto !important; min-width: 90px; margin: 0 !important; justify-content: center; }
    .wfp-workflow-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; }
    #workflowInfo { padding: 20px; }
    @media (max-width: 1399px) {
        .wfp-control-layout { grid-template-columns: 1fr; align-items: stretch; }
        .wfp-plan-selector { grid-template-columns: 110px minmax(240px, 1fr) auto; }
    }
    @media (max-width: 767px) {
        .wfp-control-card { padding: 15px; }
        .wfp-control-actions > button { flex: 1 1 180px; justify-content: center; }
        .wfp-plan-selector { grid-template-columns: 1fr; }
        #btnLoad { width: 100% !important; }
        .wfp-workflow-grid { grid-template-columns: 1fr; }
    }


    .allocation-summary-table {

        border-collapse: collapse;

        width: 100%;

        font-size: 0.82rem;

        font-variant-numeric: tabular-nums;

    }

    .allocation-summary-table th {

        background: #f0f9ff;

        color: #075985;

        padding: 10px 12px;

        border-bottom: 1px solid #e2e8f0;

        text-align: right;

        font-size: 0.72rem;

        font-weight: 700;

        text-transform: uppercase;

        letter-spacing: 0.04em;

    }

    .allocation-summary-table th:first-child {

        text-align: left;

    }

    .allocation-summary-table td {

        padding: 10px 12px;

        border-bottom: 1px solid #f1f5f9;

        text-align: right;

        color: #475569;

    }

    .allocation-summary-table .allocation-label {

        text-align: left;

        color: #334155;

        font-weight: 600;

    }

    .allocation-summary-table .allocation-total-row td {

        background: #f8fafc;

        color: #0f172a;

        font-weight: 700;

        border-top: 1px solid #cbd5e1;

    }

    .allocation-summary-table tr:last-child td {

        border-bottom: 0;

    }

    .financial-plan-table-wrap {

        max-height: 70vh;

        overflow: auto;

    }

    #fpTable {

        table-layout: fixed;

        border-collapse: separate;

        border-spacing: 0;

        width: 2230px;

        min-width: 2230px;

        font-size: 0.76rem;

        color: #475569;

        font-variant-numeric: tabular-nums;

    }

    #fpTable th,

    #fpTable td {

        border-right: 1px solid #e2e8f0;

        border-bottom: 1px solid #e2e8f0;

        padding: 7px 6px;

        vertical-align: middle;

    }

    #fpTable th:first-child,

    #fpTable td:first-child {

        border-left: 0;

    }

    #fpTable thead th {

        position: sticky;

        top: 0;

        z-index: 5;

        background: #e0f2fe;

        color: #075985;

        text-align: center;

        font-weight: 700;

        vertical-align: middle;

    }

    #fpTable thead tr:nth-child(2) th {

        // Keep the month row directly below the first sticky header row.
        // 49px left a visible gap where scrolling body amounts appeared.
        top: 28px;

        background: #f0f9ff;

    }

    #fpTable tbody td {

        background: #ffffff;

    }

    #fpTable tbody tr:hover td {

        background: #f8fafc;

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

    #fpTable .align-top {

        vertical-align: top;

    }

    #fpTable .text-end {

        text-align: right;

    }

    #fpTable .text-center {

        text-align: center;

    }

    #fpTable .section-header-row td {

        background: #e2e8f0;

        color: #334155;

        font-weight: 700;

    }

    #fpTable .subheader-row td {

        background: #f8fafc;

        color: #475569;

        font-weight: 600;

    }

    #fpTable .subheader-row td:first-child {

        padding-left: 24px;

    }

    #fpTable .subtotal-row td {

        background: #f1f5f9;

        color: #334155;

        font-weight: 700;

    }

    #fpTable .grand-total-row td {

        position: sticky;

        bottom: 0;

        z-index: 4;

        background: #e2e8f0;

        color: #0f172a;

        text-align: right;

        font-weight: 700;

        border-top: 2px solid #94a3b8;

    }

    #fpTable .grand-total-row td:first-child {

        text-align: right;

    }

    .wfp-negative {

        color: #dc2626 !important;

        font-weight: 700 !important;

    }

    #btnEditPlan:disabled {

        cursor: not-allowed;

    }

</style>

@endpush



@push('js')

<script>

$(document).ready(function () {

    const NINP_PREXC_CODE = '200000200001000';

    let ninpAllocation = 0;

    let ninpProgrammed = 0;

    let isFinalized = false;

    let activeDataRequest = null;



    // Escape values before inserting them into HTML*

    function esc(value) {

        return String(value ?? '')

            .replace(/&/g, '&amp;')

            .replace(/</g, '&lt;')

            .replace(/>/g, '&gt;')

            .replace(/"/g, '&quot;')

            .replace(/'/g, '&#039;');

    }



    // Format amount*

    function money(value) {

        const number = Number(value ?? 0);

        const safeNumber =

            Number.isFinite(number)

                ? number

                : 0;

        return safeNumber.toLocaleString('en-PH', {

            minimumFractionDigits: 2,

            maximumFractionDigits: 2

        });

    }



    // Format date*

    function formatDate(value) {

        if (!value) {

            return '—';

        }

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {

            return esc(value);

        }

        return date.toLocaleString('en-PH', {

            year: 'numeric',

            month: 'short',

            day: '2-digit',

            hour: '2-digit',

            minute: '2-digit'

        });

    }



    // Show page message*

    function showMessage(message, type = 'success') {

        const styles = {

            success: {

                box: 'border-emerald-200 bg-emerald-50 text-emerald-800',

                icon: 'fa-check-circle'

            },

            danger: {

                box: 'border-rose-200 bg-rose-50 text-rose-800',

                icon: 'fa-exclamation-circle'

            },

            warning: {

                box: 'border-amber-200 bg-amber-50 text-amber-800',

                icon: 'fa-exclamation-triangle'

            },

            info: {

                box: 'border-sky-200 bg-sky-50 text-sky-800',

                icon: 'fa-info-circle'

            }

        };

        const style =

            styles[type] || styles.info;

        $('#pageMessage').html(`

            <div

                class="mb-4 flex items-start gap-3 rounded-xl border

                       px-4 py-3 text-sm ${style.box}"

            >

                <i class="fa ${style.icon} mt-0.5"></i>

                <div class="flex-1">

                    ${esc(message)}

                </div>

                <button

                    type="button"

                    class="wfp-message-close ml-3 border-0 bg-transparent

                           p-0 text-current opacity-60 hover:opacity-100"

                    aria-label="Close"

                >

                    &times;

                </button>

            </div>

        `);

    }



    $(document).on('click', '.wfp-message-close', function () {

        $('#pageMessage').empty();

    });



    // Read Laravel error response*

    function getErrorMessage(xhr, fallback = 'Something went wrong.') {

        if (xhr?.responseJSON?.message) {

            return xhr.responseJSON.message;

        }

        if (xhr?.responseJSON?.errors) {

            const errors =

                Object.values(xhr.responseJSON.errors).flat();

            if (errors.length) {

                return errors.join('\n');

            }

        }

        return fallback;

    }



    // Get selected plan identity*

    function selectedPlan() {

        return {

            fiscalYear: $('#filterFiscalYear').val(),

            officeName: $('#filterOffice').val().trim()

        };

    }



    // Validate selected plan identity*

    function validateSelection() {

        const {

            fiscalYear,

            officeName

        } = selectedPlan();

        if (

            !fiscalYear ||

            fiscalYear < 2000 ||

            fiscalYear > 2100

        ) {

            showMessage(

                'Please enter a valid fiscal year from 2000 to 2100.',

                'danger'

            );

            return false;

        }



        if (!officeName) {

            showMessage(

                'Name of Office/Staff is required.',

                'danger'

            );

            return false;

        }



        return true;

    }



    // Update heading*

    function updateHeading() {

        const {

            fiscalYear,

            officeName

        } = selectedPlan();

        $('#planTitle')

            .text(`FY ${fiscalYear} FINANCIAL PLAN`);

        $('#planSubtitle')

            .text(`(FY ${fiscalYear} Internal Allocation per approved ${fiscalYear} GAA)`);

        $('#planOfficeName')

            .text(officeName || '—');

    }



    // Render one WFP item row*

    function renderItemRow(row, rowspan) {

        let monthCells = '';

        for (let month = 1; month <= 12; month++) {

            monthCells += `

                <td class="text-end">

                    ${money(row.months?.[month] || 0)}

                </td>

            `;

        }



        const effectiveMooe =

            row.effective_mooe ?? row.mooe;

        const effectiveCo =

            row.effective_capital_outlay ?? row.capital_outlay;



        const classificationCells =

            rowspan > 0

                ? `

                    <td

                        class="wrap-cell align-top"

                        rowspan="${rowspan}"

                    >

                        ${esc(row.program_classification || '—')}

                    </td>

                    <td

                        class="text-center prexc-cell align-top"

                        rowspan="${rowspan}"

                    >

                        ${esc(row.prexc_code || '—')}

                    </td>

                `

                : '';



        return `

            <tr>

                ${classificationCells}

                <td class="text-center wrap-cell">

                    ${esc(row.staff_unit_project || '—')}

                </td>

                <td class="wrap-cell">

                    ${esc(row.specific_activity || '—')}

                </td>

                <td class="text-center wrap-cell">

                    ${esc(row.expense_item || '—')}

                </td>

                <td class="text-center wrap-cell">

                    ${esc(row.assigned_personnel || '—')}

                </td>

                <td class="text-end">

                    ${money(effectiveMooe)}

                </td>

                <td class="text-end">

                    ${money(effectiveCo)}

                </td>

                ${monthCells}

                <td class="text-end font-semibold">

                    ${money(row.total)}

                </td>

            </tr>

        `;

    }



    // Render section header*

    function renderHeaderRow(row) {

        return `

            <tr class="section-header-row">

                <td class="wrap-cell">

                    ${esc(row.program_classification || '—')}

                </td>

                <td class="text-center prexc-cell">

                    ${esc(row.prexc_code || '')}

                </td>

                <td colspan="6"></td>

                ${Array(12).fill('<td></td>').join('')}

                <td></td>

            </tr>

        `;

    }



    // Render subtotal*

    function renderSubtotalRow(totals) {

        let monthCells = '';

        for (let month = 1; month <= 12; month++) {

            monthCells += `

                <td class="text-end">

                    ${money(totals.months[month])}

                </td>

            `;

        }



        return `

            <tr class="subtotal-row">

                <td colspan="6" class="text-end">

                    TOTAL

                </td>

                <td class="text-end">

                    ${money(totals.mooe)}

                </td>

                <td class="text-end">

                    ${money(totals.capital_outlay)}

                </td>

                ${monthCells}

                <td class="text-end">

                    ${money(totals.total)}

                </td>

            </tr>

        `;

    }



    // Create empty subtotal*

    function emptyTotals() {

        const totals = {

            mooe: 0,

            capital_outlay: 0,

            total: 0,

            months: {}

        };



        for (let month = 1; month <= 12; month++) {

            totals.months[month] = 0;

        }



        return totals;

    }



    // Add item values to subtotal*

    function addToTotals(totals, row) {

        if (row.row_type !== 'item') {

            return;

        }



        totals.mooe +=

            Number(row.effective_mooe ?? row.mooe) || 0;



        totals.capital_outlay +=

            Number(

                row.effective_capital_outlay ??

                row.capital_outlay

            ) || 0;



        totals.total +=

            Number(row.total) || 0;



        for (let month = 1; month <= 12; month++) {

            totals.months[month] +=

                Number(row.months?.[month]) || 0;

        }

    }



    // Group consecutive rows using classification and PREXC*

    function buildBlocks(rows) {

        const blocks = [];

        let run = null;



        rows.forEach(row => {

            if (row.row_type === 'header') {

                if (run) {

                    blocks.push(run);

                    run = null;

                }

                blocks.push({

                    type: 'header',

                    row: row

                });

                return;

            }



            const key =

                `${(row.program_classification || '').trim()}::${(row.prexc_code || '').trim()}`;



            if (

                !run ||

                run.key !== key

            ) {

                if (run) {

                    blocks.push(run);

                }

                run = {

                    type: 'group',

                    key: key,

                    rows: []

                };

            }



            run.rows.push(row);

        });



        if (run) {

            blocks.push(run);

        }



        return blocks;

    }



    // Render grouped WFP rows*

    function renderBlocks(blocks, $body) {

        blocks.forEach(block => {

            if (block.type === 'header') {

                $body.append(

                    renderHeaderRow(block.row)

                );

                return;

            }



            const totals =

                emptyTotals();



            const itemRows =

                block.rows.filter(

                    row => row.row_type === 'item'

                );



            block.rows.forEach(row => {

                const itemIndex =

                    itemRows.indexOf(row);



                const rowspan =

                    row.row_type === 'item' &&

                    itemIndex === 0

                        ? itemRows.length

                        : 0;



                if (row.row_type === 'subheader') {

                    $body.append(`

                        <tr class="subheader-row">

                            <td class="wrap-cell">

                                ${esc(row.program_classification || '—')}

                            </td>

                            <td colspan="20"></td>

                        </tr>

                    `);

                    return;

                }



                $body.append(

                    renderItemRow(row, rowspan)

                );



                addToTotals(

                    totals,

                    row

                );

            });



            if (itemRows.length > 0) {

                $body.append(

                    renderSubtotalRow(totals)

                );

            }

        });

    }



    // Update negative class*

    function setBalance(selector, value) {

        $(selector)

            .text(money(value))

            .toggleClass('wfp-negative', value < 0);

    }



    // Update NINP allocation summary*

    function renderNinpSummary() {

        const balance =

            ninpAllocation - ninpProgrammed;

        $('#sumNinpAlloc')

            .text(money(ninpAllocation));

        $('#sumNinpProg')

            .text(money(ninpProgrammed));

        setBalance(

            '#sumNinpBalance',

            balance

        );

    }



    // Reset grand totals*

    function resetGrandTotals() {

        $('#totMooe, #totCo, #totGrand')

            .text('0.00');



        for (let month = 1; month <= 12; month++) {

            $(`#totM${month}`)

                .text('0.00');

        }

    }



    // Load financial plan rows*

    function loadTable() {

        if (!validateSelection()) {

            return $.Deferred()

                .reject()

                .promise();

        }



        const {

            fiscalYear,

            officeName

        } = selectedPlan();



        if (

            activeDataRequest &&

            activeDataRequest.readyState !== 4

        ) {

            activeDataRequest.abort();

        }



        $('#tableLoading')

            .removeClass('d-none');

        $('#fpBody')

            .empty();

        resetGrandTotals();



        activeDataRequest = $.getJSON(

            '{{ route("financial-plans.data") }}',

            {

                fiscal_year: fiscalYear,

                office_name: officeName

            }

        ).done(function (rows) {

            const $body =

                $('#fpBody').empty();



            if (!rows.length) {

                $body.append(`

                    <tr>

                        <td

                            colspan="21"

                            class="text-center"

                            style="

                                padding:32px;

                                color:#94a3b8;

                            "

                        >

                            No WFP rows found for this fiscal year and office.

                        </td>

                    </tr>

                `);

            } else {

                renderBlocks(

                    buildBlocks(rows),

                    $body

                );

            }



            const grand = {

                mooe: 0,

                co: 0,

                months:

                    Array(13).fill(0),

                total: 0

            };



            ninpProgrammed = 0;



            rows.forEach(row => {

                if (row.row_type !== 'item') {

                    return;

                }



                grand.mooe +=

                    Number(

                        row.effective_mooe ??

                        row.mooe

                    ) || 0;



                grand.co +=

                    Number(

                        row.effective_capital_outlay ??

                        row.capital_outlay

                    ) || 0;



                grand.total +=

                    Number(row.total) || 0;



                for (

                    let month = 1;

                    month <= 12;

                    month++

                ) {

                    grand.months[month] +=

                        Number(

                            row.months?.[month]

                        ) || 0;

                }



                if (

                    (row.prexc_code || '').trim() ===

                    NINP_PREXC_CODE

                ) {

                    ninpProgrammed +=

                        Number(

                            row.effective_mooe ??

                            row.mooe

                        ) || 0;

                }

            });



            $('#totMooe')

                .text(money(grand.mooe));

            $('#totCo')

                .text(money(grand.co));

            $('#totGrand')

                .text(money(grand.total));



            for (

                let month = 1;

                month <= 12;

                month++

            ) {

                $(`#totM${month}`)

                    .text(

                        money(

                            grand.months[month]

                        )

                    );

            }



            renderNinpSummary();

        }).fail(function (xhr) {

            if (xhr.statusText !== 'abort') {

                showMessage(

                    getErrorMessage(

                        xhr,

                        'Failed to load financial plan.'

                    ),

                    'danger'

                );

            }

        }).always(function () {

            $('#tableLoading')

                .addClass('d-none');

        });



        return activeDataRequest;

    }



    // Load allocation summary*

    function loadAllocationSummary() {

        if (!validateSelection()) {

            return $.Deferred()

                .reject()

                .promise();

        }



        const {

            fiscalYear,

            officeName

        } = selectedPlan();



        return $.getJSON(

            '{{ route("financial-plans.totals") }}',

            {

                fiscal_year: fiscalYear,

                office_name: officeName

            }

        ).done(function (response) {

            const mooeAllocation =

                Number(response.mooe_allocation) || 0;

            const mooeProgrammed =

                Number(response.mooe_sum) || 0;

            const mooeBalance =

                Number(response.mooe_balance) || 0;



            const coAllocation =

                Number(response.capital_outlay_allocation) || 0;

            const coProgrammed =

                Number(response.capital_outlay_sum) || 0;

            const coBalance =

                Number(response.capital_outlay_balance) || 0;



            ninpAllocation =

                Number(response.ninp_allocation) || 0;



            const ninpBalance =

                ninpAllocation - ninpProgrammed;



            $('#sumMooeAlloc')

                .text(money(mooeAllocation));

            $('#sumMooeProg')

                .text(money(mooeProgrammed));

            setBalance(

                '#sumMooeBalance',

                mooeBalance

            );



            $('#sumCoAlloc')

                .text(money(coAllocation));

            $('#sumCoProg')

                .text(money(coProgrammed));

            setBalance(

                '#sumCoBalance',

                coBalance

            );



            renderNinpSummary();



            const totalAllocation =

                mooeAllocation +

                coAllocation +

                ninpAllocation;



            const totalProgrammed =

                mooeProgrammed +

                coProgrammed +

                ninpProgrammed;



            const totalBalance =

                mooeBalance +

                coBalance +

                ninpBalance;



            $('#sumTotalAlloc')

                .text(money(totalAllocation));

            $('#sumTotalProg')

                .text(money(totalProgrammed));

            setBalance(

                '#sumTotalBalance',

                totalBalance

            );

        }).fail(function (xhr) {

            showMessage(

                getErrorMessage(

                    xhr,

                    'Failed to load allocation summary.'

                ),

                'danger'

            );

        });

    }



    // Apply workflow status*

    function applyPlanStatus(response) {

        const status =

            response.status || 'draft';



        isFinalized =

            response.finalized === 'yes';



        const labels = {

            draft:

                'Draft',

            submitted:

                'Submitted',

            returned:

                'Returned',

            approved:

                'Approved',

            finalized:

                'Finalized'

        };



        const classes = {

            draft:

                'bg-slate-100 text-slate-700',

            submitted:

                'bg-cyan-100 text-cyan-700',

            returned:

                'bg-amber-100 text-amber-700',

            approved:

                'bg-sky-100 text-sky-700',

            finalized:

                'bg-emerald-100 text-emerald-700'

        };



        const displayStatus =

            isFinalized

                ? 'finalized'

                : status;



        $('#planStatusBadge')

            .removeClass(

                'bg-slate-100 text-slate-700 ' +

                'bg-cyan-100 text-cyan-700 ' +

                'bg-amber-100 text-amber-700 ' +

                'bg-sky-100 text-sky-700 ' +

                'bg-emerald-100 text-emerald-700 ' +

                'bg-rose-100 text-rose-700'

            )

            .addClass(

                classes[displayStatus] ||

                classes.draft

            )

            .text(

                labels[displayStatus] ||

                displayStatus

            );



        $('#btnSubmit')

            .toggleClass(

                'd-none',

                !response.can_submit

            );



        $('#btnApprove')

            .toggleClass(

                'd-none',

                !response.can_approve

            );



        $('#btnReturn')

            .toggleClass(

                'd-none',

                !response.can_return

            );



        $('#btnFinalize')

            .toggleClass(

                'd-none',

                !response.can_finalize

            );



        $('#btnReopen')

            .toggleClass(

                'd-none',

                !response.can_reopen

            );



        $('#btnEditPlan')

            .prop(

                'disabled',

                isFinalized

            );



        $('#submittedByOut')

            .text(

                response.submitted_by ||

                '—'

            );



        $('#submittedAtOut')

            .text(

                formatDate(

                    response.submitted_at

                )

            );



        $('#approvedByOut')

            .text(

                response.approved_by ||

                '—'

            );



        $('#approvedAtOut')

            .text(

                formatDate(

                    response.approved_at

                )

            );



        $('#finalizedByOut')

            .text(

                response.finalized_by ||

                '—'

            );



        $('#finalizedAtOut')

            .text(

                formatDate(

                    response.finalized_at

                )

            );



        $('#returnRemarksOut')

            .text(

                response.return_remarks ||

                '—'

            );



        const hasWorkflowInfo =

            Boolean(

                response.submitted_by ||

                response.approved_by ||

                response.finalized_by ||

                response.return_remarks

            );



        $('#workflowInfo')

            .toggleClass(

                'd-none',

                !hasWorkflowInfo

            );

    }



    // Load workflow status*

    function loadPlanStatus() {

        if (!validateSelection()) {

            return $.Deferred()

                .reject()

                .promise();

        }



        const {

            fiscalYear,

            officeName

        } = selectedPlan();



        return $.getJSON(

            '{{ route("financial-plans.status") }}',

            {

                fiscal_year: fiscalYear,

                office_name: officeName

            }

        ).done(function (response) {

            applyPlanStatus(response);

        }).fail(function (xhr) {

            showMessage(

                getErrorMessage(

                    xhr,

                    'Failed to load plan status.'

                ),

                'danger'

            );

        });

    }



    // Load selected plan*

    function loadSelectedPlan() {

        if (!validateSelection()) {

            return;

        }



        updateHeading();



        const $button =

            $('#btnLoad');



        const originalHtml =

            $button.html();



        $button

            .prop('disabled', true)

            .html(

                '<i class="fa fa-spinner fa-spin"></i>' +

                '<span> Loading...</span>'

            );



        $.when(

            loadTable(),

            loadAllocationSummary(),

            loadPlanStatus()

        ).always(function () {

            $button

                .prop('disabled', false)

                .html(originalHtml);

        });

    }



    // Open builder*

    $('#btnEditPlan').on('click', function () {

        if (

            isFinalized ||

            $(this).prop('disabled')

        ) {

            showMessage(

                'This plan is finalized. Reopen it before editing.',

                'warning'

            );

            return;

        }



        if (!validateSelection()) {

            return;

        }



        const {

            fiscalYear,

            officeName

        } = selectedPlan();



        const url =

            `{{ route('financial-plans.builder') }}` +

            `?fiscal_year=${encodeURIComponent(fiscalYear)}` +

            `&office_name=${encodeURIComponent(officeName)}`;



        window.location.href =

            url;

    });



    // Download PDF*

    $('#btnDownloadPdf').on('click', function () {

        if (!validateSelection()) {

            return;

        }



        const $button =

            $(this);



        const {

            fiscalYear,

            officeName

        } = selectedPlan();



        const url =

            `{{ route('financial-plans.export-pdf') }}` +

            `?fiscal_year=${encodeURIComponent(fiscalYear)}` +

            `&office_name=${encodeURIComponent(officeName)}`;



        const originalHtml =

            $button.html();



        $button

            .prop('disabled', true)

            .html(

                '<i class="fa fa-spinner fa-spin"></i>' +

                '<span> Preparing PDF...</span>'

            );



        fetch(url, {

            credentials: 'same-origin'

        })

            .then(response => {

                if (!response.ok) {

                    throw new Error(

                        `Server returned ${response.status}`

                    );

                }



                const disposition =

                    response.headers.get(

                        'Content-Disposition'

                    ) || '';



                const match =

                    disposition.match(

                        /filename="?([^";]+)"?/

                    );



                const filename =

                    match

                        ? match[1]

                        : `FY${fiscalYear}_Financial_Plan.pdf`;



                return response

                    .blob()

                    .then(blob => ({

                        blob,

                        filename

                    }));

            })

            .then(({ blob, filename }) => {

                const blobUrl =

                    window.URL.createObjectURL(blob);



                const link =

                    document.createElement('a');



                link.href =

                    blobUrl;

                link.download =

                    filename;



                document.body

                    .appendChild(link);



                link.click();

                link.remove();



                window.URL

                    .revokeObjectURL(blobUrl);

            })

            .catch(error => {

                console.error(

                    'PDF export failed:',

                    error

                );



                showMessage(

                    'Failed to generate the PDF. Please try again.',

                    'danger'

                );

            })

            .finally(() => {

                $button

                    .prop('disabled', false)

                    .html(originalHtml);

            });

    });



    // Send workflow action*

    function workflowRequest(

        $button,

        url,

        payload,

        workingText,

        successFallback

    ) {

        const originalHtml =

            $button.html();



        $button

            .prop('disabled', true)

            .html(

                '<i class="fa fa-spinner fa-spin"></i>' +

                `<span> ${esc(workingText)}</span>`

            );



        return $.ajax({

            url: url,

            type: 'POST',

            contentType:

                'application/json',

            data:

                JSON.stringify(payload),

            headers: {

                'X-CSRF-TOKEN':

                    '{{ csrf_token() }}'

            }

        }).done(function (response) {

            showMessage(

                response.message ||

                successFallback

            );



            loadPlanStatus();

        }).fail(function (xhr) {

            showMessage(

                getErrorMessage(

                    xhr,

                    'Workflow action failed.'

                ),

                'danger'

            );

        }).always(function () {

            $button

                .prop('disabled', false)

                .html(originalHtml);

        });

    }



    // Submit*

    $('#btnSubmit').on('click', function () {

        if (!validateSelection()) {

            return;

        }



        if (

            !confirm(

                'Submit this plan for approval?'

            )

        ) {

            return;

        }



        const {

            fiscalYear,

            officeName

        } = selectedPlan();



        workflowRequest(

            $(this),

            '{{ route("financial-plans.submit") }}',

            {

                fiscal_year: fiscalYear,

                office_name: officeName

            },

            'Submitting...',

            'Plan submitted for approval.'

        );

    });



    // Approve*

    $('#btnApprove').on('click', function () {

        if (!validateSelection()) {

            return;

        }



        if (

            !confirm(

                'Approve this submitted plan?'

            )

        ) {

            return;

        }



        const {

            fiscalYear,

            officeName

        } = selectedPlan();



        workflowRequest(

            $(this),

            '{{ route("financial-plans.approve") }}',

            {

                fiscal_year: fiscalYear,

                office_name: officeName

            },

            'Approving...',

            'Plan approved.'

        );

    });



    // Return*

    $('#btnReturn').on('click', function () {

        if (!validateSelection()) {

            return;

        }



        const remarks =

            prompt(

                'Enter return remarks for the encoder:'

            );



        if (remarks === null) {

            return;

        }



        if (!remarks.trim()) {

            showMessage(

                'Return remarks are required.',

                'warning'

            );

            return;

        }



        const {

            fiscalYear,

            officeName

        } = selectedPlan();



        workflowRequest(

            $(this),

            '{{ route("financial-plans.return") }}',

            {

                fiscal_year: fiscalYear,

                office_name: officeName,

                return_remarks: remarks.trim()

            },

            'Returning...',

            'Plan returned for revision.'

        );

    });



    // Finalize*

    $('#btnFinalize').on('click', function () {

        if (!validateSelection()) {

            return;

        }



        if (

            !confirm(

                'Finalize this plan? It will be locked from further edits until reopened.'

            )

        ) {

            return;

        }



        const $button =

            $(this);



        const {

            fiscalYear,

            officeName

        } = selectedPlan();



        const originalHtml =

            $button.html();



        $button

            .prop('disabled', true)

            .html(

                '<i class="fa fa-spinner fa-spin"></i>' +

                '<span> Finalizing...</span>'

            );



        $.ajax({

            url:

                '{{ route("financial-plans.finalize") }}',

            type:

                'POST',

            contentType:

                'application/json',

            data:

                JSON.stringify({

                    fiscal_year: fiscalYear,

                    office_name: officeName

                }),

            headers: {

                'X-CSRF-TOKEN':

                    '{{ csrf_token() }}'

            }

        }).done(function (response) {

            showMessage(

                response.message ||

                'Plan finalized successfully.'

            );



            loadPlanStatus();

        }).fail(function (xhr) {

            showMessage(

                getErrorMessage(

                    xhr,

                    'Failed to finalize plan.'

                ),

                'danger'

            );

        }).always(function () {

            $button

                .prop('disabled', false)

                .html(originalHtml);

        });

    });



    // Reopen*

    $('#btnReopen').on('click', function () {

        if (!validateSelection()) {

            return;

        }



        if (

            !confirm(

                'Reopen this plan for editing?'

            )

        ) {

            return;

        }



        const $button =

            $(this);



        const {

            fiscalYear,

            officeName

        } = selectedPlan();



        const originalHtml =

            $button.html();



        $button

            .prop('disabled', true)

            .html(

                '<i class="fa fa-spinner fa-spin"></i>' +

                '<span> Reopening...</span>'

            );



        $.ajax({

            url:

                '{{ route("financial-plans.reopen") }}',

            type:

                'POST',

            contentType:

                'application/json',

            data:

                JSON.stringify({

                    fiscal_year: fiscalYear,

                    office_name: officeName

                }),

            headers: {

                'X-CSRF-TOKEN':

                    '{{ csrf_token() }}'

            }

        }).done(function (response) {

            showMessage(

                response.message ||

                'Plan reopened successfully.'

            );



            loadPlanStatus();

        }).fail(function (xhr) {

            showMessage(

                getErrorMessage(

                    xhr,

                    'Failed to reopen plan.'

                ),

                'danger'

            );

        }).always(function () {

            $button

                .prop('disabled', false)

                .html(originalHtml);

        });

    });



    // Load*

    $('#btnLoad').on(

        'click',

        loadSelectedPlan

    );



    // Enter key*

    $('#filterFiscalYear, #filterOffice')

        .on(

            'keydown',

            function (event) {

                if (event.key === 'Enter') {

                    event.preventDefault();

                    loadSelectedPlan();

                }

            }

        );



    // Bootstrap tooltips if available*

    if (

        typeof $.fn.tooltip === 'function'

    ) {

        $('[data-bs-toggle="tooltip"]').tooltip();

    }



    // Initial load*

    loadSelectedPlan();

});

</script>

@endpush
