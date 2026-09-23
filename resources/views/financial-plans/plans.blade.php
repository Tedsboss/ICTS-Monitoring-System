@extends('layouts.app')
@section('content')
@php
    $isFinancialPlanAdministrator = in_array(
        (int) auth()->user()->role_id,
        [1, 29],
        true
    );
@endphp
<nav
    class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
    id="navbarBlur"
    data-scroll="false"
>
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'All Work & Financial Plans'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>
<div class="px-4 pb-8 pt-4">
    {{-- Page Message --}}
    <div id="pageMessage"></div>
    {{-- Filters --}}
    <section
        class="mb-5 rounded-2xl border border-slate-200
               bg-white p-5 shadow-sm"
   >
        <div
            class="flex flex-col gap-5 xl:flex-row
                   xl:items-end xl:justify-between"
       >
            <div class="flex flex-wrap items-end gap-3">
                {{-- Fiscal Year --}}
                <div>
                    <label
                        for="filterFiscalYear"
                        class="mb-1.5 block text-xs font-semibold
                               uppercase tracking-wide text-slate-600"
                   >
                        Fiscal Year
                    </label>
                    <select
                        id="filterFiscalYear"
                        class="block min-w-[150px] rounded-lg
                               border border-slate-300 bg-white
                               px-3 py-2 text-sm text-slate-700
                               shadow-sm outline-none transition
                               focus:border-sky-500 focus:ring-2
                               focus:ring-sky-100"
                   >
                        <option value="">
                            All Fiscal Years
                        </option>
                        @foreach ($fiscalYears as $year)
                            <option value="{{ $year }}">
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>
                {{-- Office --}}
                <div class="w-full sm:w-auto">
                    <label
                        for="filterOffice"
                        class="mb-1.5 block text-xs font-semibold
                               uppercase tracking-wide text-slate-600"
                   >
                        Office/Staff
                    </label>
                    <select
                        id="filterOffice"
                        class="block w-full rounded-lg border
                               border-slate-300 bg-white px-3 py-2
                               text-sm text-slate-700 shadow-sm
                               outline-none transition
                               focus:border-sky-500 focus:ring-2
                               focus:ring-sky-100 sm:min-w-[300px]"
                   >
                        <option value="">
                            All Offices
                        </option>
                        @foreach ($offices as $office)
                            <option value="{{ $office }}">
                                {{ $office }}
                            </option>
                        @endforeach
                    </select>
                </div>
                {{-- Status --}}
                <div>
                    <label
                        for="filterStatus"
                        class="mb-1.5 block text-xs font-semibold
                               uppercase tracking-wide text-slate-600"
                   >
                        Status
                    </label>
                    <select
                        id="filterStatus"
                        class="block min-w-[170px] rounded-lg
                               border border-slate-300 bg-white
                               px-3 py-2 text-sm text-slate-700
                               shadow-sm outline-none transition
                               focus:border-sky-500 focus:ring-2
                               focus:ring-sky-100"
                   >
                        <option value="">
                            All Status
                        </option>
                        <option value="draft">
                            Draft
                        </option>
                        <option value="submitted">
                            Submitted
                        </option>
                        <option value="returned">
                            Returned
                        </option>
                        <option value="approved">
                            Approved
                        </option>
                        <option value="finalized">
                            Finalized
                        </option>
                    </select>
                </div>
                {{-- Reset --}}
                <button
                    type="button"
                    id="btnResetFilters"
                    class="inline-flex h-[38px] items-center gap-2
                           rounded-lg border border-slate-300
                           bg-white px-4 text-sm font-semibold
                           text-slate-700 shadow-sm transition
                           hover:bg-slate-50"
               >
                    <i class="fa fa-refresh"></i>
                    <span>
                        Reset
                    </span>
                </button>
            </div>
            {{-- New Plan --}}
            <div>
                @can('create', \App\Models\FinancialPlan::class)
                <a
                    href="{{ route('financial-plans.builder') }}"
                    class="inline-flex items-center gap-2 rounded-lg
                           bg-sky-600 px-4 py-2.5 text-sm
                           font-semibold text-white shadow-sm
                           transition hover:bg-sky-700"
               >
                    <i class="fa fa-plus"></i>
                    <span>
                        New Financial Plan
                    </span>
                </a>
                @endcan
            </div>
        </div>
    </section>
    {{-- Plans --}}
    <section
        class="overflow-hidden rounded-2xl border
               border-slate-200 bg-white shadow-sm"
   >
        {{-- Header --}}
        <div
            class="flex flex-col gap-3 border-b border-slate-200
                   px-5 py-4 sm:flex-row sm:items-center
                   sm:justify-between"
       >
            <div>
                <h1 class="text-lg font-bold text-slate-900">
                    Filed Financial Plans
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Browse WFPs by fiscal year, office and workflow status.
                </p>
            </div>
            <span
                id="visibleCountBadge"
                class="inline-flex w-fit items-center rounded-full
                       bg-slate-100 px-3 py-1.5 text-xs
                       font-semibold text-slate-700"
           >
                {{ $plans->count() }}
                plan{{ $plans->count() === 1 ? '' : 's' }}
            </span>
        </div>
        {{-- Table --}}
        <div class="overflow-x-auto">
            <table
                id="plansTable"
                class="w-full min-w-[950px] border-collapse"
           >
                <thead>
                    <tr class="bg-slate-50">
                        <th
                            class="whitespace-nowrap border-b
                                   border-slate-200 px-5 py-3
                                   text-left text-xs font-bold
                                   uppercase tracking-wide text-slate-500"
                       >
                            Fiscal Year
                        </th>
                        <th
                            class="whitespace-nowrap border-b
                                   border-slate-200 px-4 py-3
                                   text-left text-xs font-bold
                                   uppercase tracking-wide text-slate-500"
                       >
                            Office/Staff
                        </th>
                        <th
                            class="whitespace-nowrap border-b
                                   border-slate-200 px-4 py-3
                                   text-center text-xs font-bold
                                   uppercase tracking-wide text-slate-500"
                       >
                            Budget Lines
                        </th>
                        <th
                            class="whitespace-nowrap border-b
                                   border-slate-200 px-4 py-3
                                   text-right text-xs font-bold
                                   uppercase tracking-wide text-slate-500"
                       >
                            Programmed Budget
                        </th>
                        <th
                            class="whitespace-nowrap border-b
                                   border-slate-200 px-4 py-3
                                   text-center text-xs font-bold
                                   uppercase tracking-wide text-slate-500"
                       >
                            Status
                        </th>
                        <th
                            class="whitespace-nowrap border-b
                                   border-slate-200 px-5 py-3
                                   text-right text-xs font-bold
                                   uppercase tracking-wide text-slate-500"
                       >
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($plans as $plan)
                        @php
                            $isFinalized =
                                ($plan->finalized ?? 'no') === 'yes';
                            $statusKey =
                                $isFinalized
                                    ? 'finalized'
                                    : ($plan->status ?? 'draft');
                            $statusLabel = match ($statusKey) {
                                'submitted' => 'Submitted',
                                'returned' => 'Returned',
                                'approved' => 'Approved',
                                'finalized' => 'Finalized',
                                default => 'Draft',
                            };
                            $statusClass = match ($statusKey) {
                                'submitted' => 'bg-cyan-100 text-cyan-700',
                                'returned' => 'bg-amber-100 text-amber-700',
                                'approved' => 'bg-sky-100 text-sky-700',
                                'finalized' => 'bg-emerald-100 text-emerald-700',
                                default => 'bg-slate-100 text-slate-700',
                            };
                        @endphp
                        <tr
                            class="plan-row transition hover:bg-slate-50"
                            data-fiscal-year="{{ $plan->fiscal_year }}"
                            data-office="{{ strtolower($plan->office_name) }}"
                            data-status="{{ $statusKey }}"
                       >
                            {{-- Fiscal Year --}}
                            <td
                                class="border-b border-slate-100
                                       px-5 py-4 text-sm font-bold
                                       text-slate-900"
                           >
                                FY {{ $plan->fiscal_year }}
                            </td>
                            {{-- Office --}}
                            <td
                                class="border-b border-slate-100
                                       px-4 py-4"
                           >
                                <div
                                    class="max-w-[320px] break-words
                                           text-sm font-semibold
                                           text-slate-800"
                               >
                                    {{ $plan->office_name }}
                                </div>
                            </td>
                            {{-- Budget Lines --}}
                            <td
                                class="border-b border-slate-100
                                       px-4 py-4 text-center"
                           >
                                <span
                                    class="inline-flex min-w-[46px]
                                           items-center justify-center
                                           rounded-full bg-slate-100
                                           px-2.5 py-1 text-xs
                                           font-semibold text-slate-700"
                               >
                                    {{ number_format($plan->row_count) }}
                                </span>
                            </td>
                            {{-- Programmed Budget --}}
                            <td
                                class="whitespace-nowrap border-b
                                       border-slate-100 px-4 py-4
                                       text-right text-sm font-bold
                                       text-slate-900"
                           >
                                ₱{{ number_format((float) $plan->budget_sum, 2) }}
                            </td>
                            {{-- Status --}}
                            <td
                                class="border-b border-slate-100
                                       px-4 py-4 text-center"
                           >
                                <span
                                    class="inline-flex items-center gap-1.5
                                           rounded-full px-3 py-1.5
                                           text-xs font-bold
                                           {{ $statusClass }}"
                               >
                                    @if ($isFinalized)
                                        <i class="fa fa-lock"></i>
                                    @endif
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            {{-- Actions --}}
                            <td
                                class="border-b border-slate-100
                                       px-5 py-4"
                           >
                                <div
                                    class="flex flex-wrap items-center
                                           justify-end gap-2"
                               >
                                    {{-- View --}}
                                    <a
                                        href="{{ route('financial-plans.index', [
                                            'fiscal_year' => $plan->fiscal_year,
                                            'office_name' => $plan->office_name,
                                        ]) }}"
                                        class="inline-flex items-center gap-1.5
                                               rounded-lg border border-sky-200
                                               bg-sky-50 px-3 py-1.5
                                               text-xs font-semibold
                                               text-sky-700 transition
                                               hover:bg-sky-100"
                                        title="View plan"
                                   >
                                        <i class="fa fa-eye"></i>
                                        <span>
                                            View
                                        </span>
                                    </a>
                                    {{-- Edit / Locked --}}
                                    @if (! $isFinalized)
                                        <a
                                            href="{{ route('financial-plans.builder', [
                                                'fiscal_year' => $plan->fiscal_year,
                                                'office_name' => $plan->office_name,
                                            ]) }}"
                                            class="inline-flex items-center gap-1.5
                                                   rounded-lg border
                                                   border-slate-300 bg-white
                                                   px-3 py-1.5 text-xs
                                                   font-semibold text-slate-700
                                                   transition hover:bg-slate-50"
                                            title="Edit plan"
                                       >
                                            <i class="fa fa-pencil"></i>
                                            <span>
                                                Edit
                                            </span>
                                        </a>
                                    @else
                                        <button
                                            type="button"
                                            disabled
                                            class="inline-flex cursor-not-allowed
                                                   items-center gap-1.5
                                                   rounded-lg border
                                                   border-slate-200
                                                   bg-slate-100 px-3 py-1.5
                                                   text-xs font-semibold
                                                   text-slate-400"
                                            title="Reopen the plan before editing"
                                       >
                                            <i class="fa fa-lock"></i>
                                            <span>
                                                Locked
                                            </span>
                                        </button>
                                    @endif
                                    {{-- Delete --}}
                                    @if ($isFinancialPlanAdministrator)
                                    <button
                                        type="button"
                                        class="btn-delete-plan inline-flex
                                               h-[30px] w-[34px] items-center
                                               justify-center rounded-lg
                                               border border-rose-200
                                               bg-rose-50 text-xs
                                               text-rose-600 transition
                                               hover:bg-rose-100
                                               disabled:cursor-not-allowed
                                               disabled:border-slate-200
                                               disabled:bg-slate-100
                                               disabled:text-slate-400"
                                        data-fiscal-year="{{ $plan->fiscal_year }}"
                                        data-office="{{ $plan->office_name }}"
                                        {{ $isFinalized ? 'disabled' : '' }}
                                        title="{{ $isFinalized
                                            ? 'Reopen the plan before deleting'
                                            : 'Delete entire plan' }}"
                                   >
                                        <i class="fa fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyPlansRow">
                            <td
                                colspan="6"
                                class="px-6 py-14 text-center"
                           >
                                <div
                                    class="mx-auto flex h-12 w-12
                                           items-center justify-center
                                           rounded-full bg-slate-100
                                           text-xl text-slate-400"
                               >
                                    <i class="fa fa-folder-open-o"></i>
                                </div>
                                <p
                                    class="mt-3 text-sm font-semibold
                                           text-slate-600"
                               >
                                    No financial plans have been filed yet.
                                </p>
                                <p
                                    class="mt-1 text-xs text-slate-400"
                               >
                                    Create a new Work and Financial Plan to get started.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- No Filter Results --}}
        <div
            id="noFilterResults"
            class="hidden px-6 py-14 text-center"
       >
            <div
                class="mx-auto flex h-12 w-12 items-center
                       justify-center rounded-full bg-slate-100
                       text-xl text-slate-400"
           >
                <i class="fa fa-search"></i>
            </div>
            <p
                class="mt-3 text-sm font-semibold text-slate-600"
           >
                No financial plans match the selected filters.
            </p>
            <p class="mt-1 text-xs text-slate-400">
                Try changing or resetting the filters.
            </p>
        </div>
    </section>
    {{-- Footer --}}
    <div class="mt-6">
        @include('layouts.footers.auth.footer')
    </div>
</div>
@endsection
@push('js')
<script>
$(document).ready(function () {
    function esc(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
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
                class="mb-4 flex items-start gap-3 rounded-xl
                       border px-4 py-3 text-sm ${style.box}"
           >
                <i class="fa ${style.icon} mt-0.5"></i>
                <div class="flex-1">
                    ${esc(message)}
                </div>
                <button
                    type="button"
                    class="page-message-close ml-3 border-0
                           bg-transparent p-0 text-lg leading-none
                           text-current opacity-60 hover:opacity-100"
                    aria-label="Close"
               >
                    &times;
                </button>
            </div>
        `);
    }
    $(document).on(
        'click',
        '.page-message-close',
        function () {
            $('#pageMessage').empty();
        }
    );
    function getErrorMessage(
        xhr,
        fallback = 'Something went wrong.'
    ) {
        if (xhr?.responseJSON?.message) {
            return xhr.responseJSON.message;
        }
        if (xhr?.responseJSON?.errors) {
            const errors =
                Object.values(
                    xhr.responseJSON.errors
                ).flat();
            if (errors.length) {
                return errors.join('\n');
            }
        }
        return fallback;
    }
    function applyFilters() {
        const fiscalYear =
            String(
                $('#filterFiscalYear').val() || ''
            );
        const office =
            String(
                $('#filterOffice').val() || ''
            ).toLowerCase();
        const status =
            String(
                $('#filterStatus').val() || ''
            );
        let visibleCount = 0;
        $('.plan-row').each(function () {
            const $row =
                $(this);
            const rowFiscalYear =
                String(
                    $row.data('fiscal-year')
                );
            const rowOffice =
                String(
                    $row.data('office') || ''
                ).toLowerCase();
            const rowStatus =
                String(
                    $row.data('status') || ''
                );
            const matchesFiscalYear =
                !fiscalYear ||
                rowFiscalYear === fiscalYear;
            const matchesOffice =
                !office ||
                rowOffice === office;
            const matchesStatus =
                !status ||
                rowStatus === status;
            const visible =
                matchesFiscalYear &&
                matchesOffice &&
                matchesStatus;
            $row.toggle(visible);
            if (visible) {
                visibleCount++;
            }
        });
        $('#visibleCountBadge')
            .text(
                `${visibleCount} plan${visibleCount === 1 ? '' : 's'}`
            );
        const hasPlans =
            $('.plan-row').length > 0;
        $('#noFilterResults')
            .toggleClass(
                'hidden',
                !hasPlans || visibleCount > 0
            );
    }
    $('#filterFiscalYear, #filterOffice, #filterStatus')
        .on(
            'change',
            applyFilters
        );
    $('#btnResetFilters').on(
        'click',
        function () {
            $('#filterFiscalYear')
                .val('');
            $('#filterOffice')
                .val('');
            $('#filterStatus')
                .val('');
            applyFilters();
        }
    );
    $('.btn-delete-plan').on(
        'click',
        function () {
            const $button =
                $(this);
            if ($button.prop('disabled')) {
                return;
            }
            const fiscalYear =
                $button.data('fiscal-year');
            const officeName =
                $button.data('office');
            if (
                !confirm(
                    `Delete the entire FY ${fiscalYear} financial plan for ${officeName}? This cannot be undone.`
                )
            ) {
                return;
            }
            const originalHtml =
                $button.html();
            $button
                .prop('disabled', true)
                .html(
                    '<i class="fa fa-spinner fa-spin"></i>'
                );
            $.ajax({
                url:
                    '{{ route("financial-plans.destroy-plan") }}',
                type:
                    'DELETE',
                data: {
                    fiscal_year:
                        fiscalYear,
                    office_name:
                        officeName,
                    _token:
                        '{{ csrf_token() }}'
                }
            }).done(function (response) {
                if (
                    response.success === false
                ) {
                    showMessage(
                        response.message ||
                        'Failed to delete plan.',
                        'danger'
                    );
                    $button
                        .prop('disabled', false)
                        .html(originalHtml);
                    return;
                }
                showMessage(
                    response.message ||
                    'Financial plan deleted successfully.'
                );
                $button
                    .closest('tr')
                    .fadeOut(
                        200,
                        function () {
                            $(this).remove();
                            applyFilters();
                            if (
                                $('.plan-row').length === 0
                            ) {
                                window.location.reload();
                            }
                        }
                    );
            }).fail(function (xhr) {
                showMessage(
                    getErrorMessage(
                        xhr,
                        'Failed to delete financial plan.'
                    ),
                    'danger'
                );
                $button
                    .prop('disabled', false)
                    .html(originalHtml);
            });
        }
    );
    applyFilters();
});
</script>
@endpush
