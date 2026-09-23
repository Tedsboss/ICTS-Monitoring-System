@extends('layouts.app')
@section('content')
@php
    $status = $plan?->status ?? 'draft';
    $isFinalized = $plan?->isFinalized() ?? false;
    $isEditable = $plan?->isEditable() ?? false;
    $statusLabel = $plan
        ? ($isFinalized ? 'Finalized' : ucfirst($status))
        : 'No Plan';
    $statusClass = match ($isFinalized ? 'finalized' : $status) {
        'submitted' => 'bg-cyan-100 text-cyan-700',
        'returned' => 'bg-amber-100 text-amber-700',
        'approved' => 'bg-sky-100 text-sky-700',
        'finalized' => 'bg-emerald-100 text-emerald-700',
        default => 'bg-slate-100 text-slate-700',
    };
    $selectedStaff = $plan?->staff ?? $staffs->firstWhere('id', $staffId);
    $officeName = $selectedStaff
        ? ($selectedStaff->abbreviation ?: $selectedStaff->name)
        : '';
@endphp
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'Work Plan'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>
<div class="px-4 pb-8 pt-4">
    <div class="mx-auto max-w-[1900px]">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('work-plans.plans', ['fiscal_year' => $fiscalYear]) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                <i class="fa fa-arrow-left"></i>
                Back to All Work Plans
            </a>
            @if($plan)
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold {{ $statusClass }}">
                    @if($isFinalized)
                        <i class="fa fa-lock"></i>
                    @endif
                    {{ $statusLabel }}
                </span>
            @endif
        </div>
        @if(session()->has('succes'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('succes') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        <div id="workPlanMessage"></div>
        <section class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <form method="GET" action="{{ route('work-plans.index') }}">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                    <div class="order-2 flex flex-wrap items-end gap-3">
                        <div>
                            <label for="fiscalYear" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Fiscal Year
                            </label>
                            <input type="number"
                                   id="fiscalYear"
                                   name="fiscal_year"
                                   value="{{ $fiscalYear }}"
                                   min="2000"
                                   max="2100"
                                   class="w-[140px] rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                        </div>
                        <div>
                            <label for="staffId" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Office/Staff
                            </label>
                            <select id="staffId"
                                    name="staff_id"
                                    class="min-w-[300px] rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                                <option value="">Select Office/Staff</option>
                                @foreach($staffs as $staff)
                                    <option value="{{ $staff->id }}" {{ (int) $staffId === (int) $staff->id ? 'selected' : '' }}>
                                        {{ $staff->abbreviation ?: $staff->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-lg border border-sky-300 bg-sky-50 px-4 py-2 text-sm font-semibold text-sky-700 transition hover:bg-sky-100">
                            <i class="fa fa-folder-open"></i>
                            Load Plan
                        </button>
                    </div>
                    <div class="order-1 flex flex-wrap items-center gap-2">
                        @if($plan)
                            @can('update', $plan)
                                @if($isEditable)
                                    <a href="{{ route('work-plans.builder', [
                                        'fiscal_year' => $plan->fiscal_year,
                                        'staff_id' => $plan->staff_id,
                                    ]) }}"
                                       class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                        <i class="fa fa-pencil"></i>
                                        Edit
                                    </a>
                                @endif
                            @endcan
                            <a href="{{ route('work-plans.export-pdf', ['workPlan' => $plan->id]) }}"
                               target="_blank"
                               rel="noopener"
                               class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700 transition hover:bg-emerald-100">
                                <i class="fa fa-file-pdf-o"></i>
                                Download PDF
                            </a>
                            @can('submit', $plan)
                                @if(in_array($status, ['draft', 'returned'], true) && !$isFinalized)
                                    <button type="button"
                                            class="workflow-action inline-flex items-center gap-2 rounded-lg bg-sky-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-sky-700"
                                            data-url="{{ route('work-plans.submit', $plan) }}"
                                            data-action="submit">
                                        <i class="fa fa-paper-plane"></i>
                                        Submit
                                    </button>
                                @endif
                            @endcan
                            @can('approve', $plan)
                                @if($status === 'submitted' && !$isFinalized)
                                    <button type="button"
                                            class="workflow-action inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-emerald-700"
                                            data-url="{{ route('work-plans.approve', $plan) }}"
                                            data-action="approve">
                                        <i class="fa fa-check"></i>
                                        Approve
                                    </button>
                                @endif
                            @endcan
                            @can('return', $plan)
                                @if(in_array($status, ['submitted', 'approved'], true) && !$isFinalized)
                                    <button type="button"
                                            id="btnReturnPlan"
                                            class="inline-flex items-center gap-2 rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-700 transition hover:bg-amber-100">
                                        <i class="fa fa-undo"></i>
                                        Return
                                    </button>
                                @endif
                            @endcan
                            @can('finalize', $plan)
                                @if($status === 'approved' && !$isFinalized)
                                    <button type="button"
                                            class="workflow-action inline-flex items-center gap-2 rounded-lg bg-emerald-700 px-3 py-2 text-xs font-bold text-white transition hover:bg-emerald-800"
                                            data-url="{{ route('work-plans.finalize', $plan) }}"
                                            data-action="finalize">
                                        <i class="fa fa-lock"></i>
                                        Finalize
                                    </button>
                                @endif
                            @endcan
                            @can('reopen', $plan)
                                @if($isFinalized)
                                    <button type="button"
                                            class="workflow-action inline-flex items-center gap-2 rounded-lg border border-sky-300 bg-sky-50 px-3 py-2 text-xs font-bold text-sky-700 transition hover:bg-sky-100"
                                            data-url="{{ route('work-plans.reopen', $plan) }}"
                                            data-action="reopen">
                                        <i class="fa fa-unlock"></i>
                                        Reopen
                                    </button>
                                @endif
                            @endcan
                        @else
                            @can('create', \App\Models\WorkPlan::class)
                                @if($staffId)
                                    <a href="{{ route('work-plans.builder', [
                                        'fiscal_year' => $fiscalYear,
                                        'staff_id' => $staffId,
                                    ]) }}"
                                       class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-4 py-2 text-xs font-bold text-white transition hover:bg-sky-700">
                                        <i class="fa fa-plus"></i>
                                        Create Work Plan
                                    </a>
                                @endif
                            @endcan
                        @endif
                    </div>
                </div>
            </form>
        </section>
        @if(!$staffId)
            <section class="rounded-2xl border border-slate-200 bg-white px-6 py-14 text-center shadow-sm">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-xl text-slate-400">
                    <i class="fa fa-folder-open-o"></i>
                </div>
                <h2 class="mt-3 text-base font-bold text-slate-700">Select an Office/Staff</h2>
                <p class="mb-0 mt-1 text-sm text-slate-500">
                    Select a fiscal year and Office/Staff to view its Work Plan.
                </p>
            </section>
        @elseif(!$plan)
            <section class="rounded-2xl border border-amber-200 bg-amber-50 px-6 py-14 text-center shadow-sm">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-xl text-amber-500">
                    <i class="fa fa-file-text-o"></i>
                </div>
                <h2 class="mt-3 text-base font-bold text-amber-800">No Work Plan Found</h2>
                <p class="mb-0 mt-1 text-sm text-amber-700">
                    No Work Plan exists for FY {{ $fiscalYear }} and {{ $officeName ?: 'the selected Office/Staff' }}.
                </p>
                @can('create', \App\Models\WorkPlan::class)
                    <a href="{{ route('work-plans.builder', [
                        'fiscal_year' => $fiscalYear,
                        'staff_id' => $staffId,
                    ]) }}"
                       class="mt-4 inline-flex items-center gap-2 rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-700">
                        <i class="fa fa-plus"></i>
                        Create Work Plan
                    </a>
                @endcan
            </section>
        @else
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4 text-center">
                    <h1 class="m-0 text-lg font-bold uppercase text-slate-900">
                        Work Plan
                    </h1>
                    <div class="mt-1 text-sm font-semibold text-slate-700">
                        FY {{ $plan->fiscal_year }}
                    </div>
                    <div class="mt-1 text-sm text-slate-600">
                        {{ $plan->staff?->name ?? '—' }}
                    </div>
                </div>
                <div class="work-plan-table-wrap overflow-x-auto">
                    <table class="work-plan-table w-full border-collapse text-xs">
                        <thead>
                            <tr>
                                <th rowspan="2" class="classification-column">
                                    DEPDev Program of Expenditure Classification / Budget Structure
                                    <div class="mt-1 font-normal">(a)</div>
                                </th>
                                <th rowspan="2" class="activity-column">
                                    Specific Activity/ies
                                    <div class="mt-1 font-normal">(b)</div>
                                </th>
                                <th colspan="12">
                                    Target Output/s
                                </th>
                            </tr>
                            <tr>
                                @foreach($months as $monthNumber => $monthName)
                                    <th class="month-column" title="{{ $monthName }}">
                                        {{ strtoupper(substr($monthName, 0, 1)) }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
@php
    $displayRows = [];
    $currentGroup = null;
    foreach ($plan->items as $planItem) {
        if ($planItem->row_type !== 'item') {
            if ($currentGroup) {
                $displayRows[] = $currentGroup;
                $currentGroup = null;
            }
            $displayRows[] = ['type' => $planItem->row_type, 'item' => $planItem];
            continue;
        }
    $classificationKey = $planItem->financial_plan_id
        ? 'fp-' . mb_strtolower(trim((string) $planItem->program_classification))
        : ($planItem->classification_id
            ? 'classification-' . $planItem->classification_id
            : 'item-' . $planItem->id);
        if (!$currentGroup || $currentGroup['key'] !== $classificationKey) {
            if ($currentGroup) {
                $displayRows[] = $currentGroup;
            }
            $currentGroup = [
                'type' => 'classification',
                'key' => $classificationKey,
                'classification' => $planItem->classification,
                'program_classification' => $planItem->program_classification,
                'prexc_code' => $planItem->prexc_code,
                'items' => [],
            ];
        }
        /* Pack targets into the minimum number of visual rows (lanes). Targets whose applicable months do not overlap share one row. */
        $lanes = [];
        foreach ($planItem->targets->sortBy('sort_order')->values() as $target) {
            $targetMonths = $target->relationLoaded('months') && $target->months->isNotEmpty()
                ? $target->months->pluck('month')->map(fn ($month) => (int) $month)->unique()->sort()->values()
                : collect([(int) $target->month])->filter(fn ($month) => $month >= 1 && $month <= 12);
            $months = $targetMonths->all();
            $placed = false;
            foreach ($lanes as &$lane) {
                if (empty(array_intersect($lane['usedMonths'], $months))) {
                    $lane['targets'][] = ['target' => $target, 'months' => $months];
                    $lane['usedMonths'] = array_values(array_unique(array_merge($lane['usedMonths'], $months)));
                    sort($lane['usedMonths']);
                    $placed = true;
                    break;
                }
            }
            unset($lane);
            if (!$placed) {
                $lanes[] = [
                    'targets' => [['target' => $target, 'months' => $months]],
                    'usedMonths' => $months,
                ];
            }
        }
        if (empty($lanes)) {
            $lanes[] = ['targets' => [], 'usedMonths' => []];
        }
        foreach ($lanes as &$lane) {
            $monthTarget = array_fill(1, 12, null);
            foreach ($lane['targets'] as $laneTarget) {
                foreach ($laneTarget['months'] as $month) {
                    if ($month >= 1 && $month <= 12) {
                        $monthTarget[$month] = $laneTarget['target'];
                    }
                }
            }
            $segments = [];
            $month = 1;
            while ($month <= 12) {
                $target = $monthTarget[$month];
                $targetId = $target ? $target->id : null;
                $startMonth = $month;
                while ($month <= 12) {
                    $currentTarget = $monthTarget[$month];
                    $currentTargetId = $currentTarget ? $currentTarget->id : null;
                    if ($currentTargetId !== $targetId) {
                        break;
                    }
                    $month++;
                }
                $segments[] = [
                    'target' => $target,
                    'span' => $month - $startMonth,
                ];
            }
            $lane['segments'] = $segments;
        }
        unset($lane);
        $currentGroup['items'][] = [
            'item' => $planItem,
            'lanes' => $lanes,
            'rowspan' => count($lanes),
        ];
    }
    if ($currentGroup) {
        $displayRows[] = $currentGroup;
    }
@endphp
@forelse($displayRows as $displayRow)
    @if(in_array($displayRow['type'], ['header', 'subheader'], true))
        @php
            $displayItem = $displayRow['item'];
        @endphp
        <tr class="{{ $displayRow['type'] === 'header' ? 'section-header-row' : 'subheader-row' }}">
            <td colspan="14">{{ $displayItem->title ?: '—' }}</td>
        </tr>
    @else
        @php
            $classificationRowspan = collect($displayRow['items'])->sum('rowspan');
            $classificationPrinted = false;
        @endphp
        @foreach($displayRow['items'] as $activityData)
            @foreach($activityData['lanes'] as $lane)
                <tr class="budget-line-row">
                    @if(!$classificationPrinted)
                        <td class="classification-cell" rowspan="{{ $classificationRowspan }}">
                            @if($displayRow['program_classification'])
                                @if($displayRow['prexc_code'])
                                    <div class="mb-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">{{ $displayRow['prexc_code'] }}</div>
                                @endif
                                <div class="font-semibold text-slate-800">{{ $displayRow['program_classification'] }}</div>
                            @elseif($displayRow['classification'])
                                @if($displayRow['classification']->code)
                                    <div class="mb-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">{{ $displayRow['classification']->code }}</div>
                                @endif
                                <div class="font-semibold text-slate-800">{{ $displayRow['classification']->name }}</div>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        @php
                            $classificationPrinted = true;
                        @endphp
                    @endif
                    @if($loop->first)
                        <td class="activity-cell" rowspan="{{ $activityData['rowspan'] }}">
                            {!! nl2br(e($activityData['item']->specific_activity ?: '—')) !!}
                        </td>
                    @endif
                    @foreach($lane['segments'] as $segment)
                        <td colspan="{{ $segment['span'] }}" class="target-cell {{ $segment['target'] ? 'target-range-cell' : 'target-empty-cell' }}">
                            @if($segment['target'])
                                {!! nl2br(e($segment['target']->target_output)) !!}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        @endforeach
    @endif
@empty
    <tr>
        <td colspan="14" class="px-5 py-12 text-center text-sm text-slate-500">No Work Plan rows have been added.</td>
    </tr>
@endforelse
</tbody>
                    </table>
                </div>
            </section>
            @php
                $signatory = $plan->signatory;
                $hasSignatories = $signatory && ($signatory->prepared_by || $signatory->reviewed_by || $signatory->recommended_by || $signatory->approved_by);
            @endphp
            @if($hasSignatories)
                <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="m-0 text-base font-bold text-slate-900">Signatories</h2>
                    <div class="mt-8 grid grid-cols-1 gap-8 md:grid-cols-2 xl:grid-cols-4">
                        <div class="signatory-block">
                            <div class="signatory-label">Prepared by:</div>
                            <div class="signatory-name">{{ $signatory->prepared_by ?: '—' }}</div>
                            <div class="signatory-position">{{ $signatory->prepared_by_position ?: '—' }}</div>
                        </div>
                        <div class="signatory-block">
                            <div class="signatory-label">Reviewed by:</div>
                            <div class="signatory-name">{{ $signatory->reviewed_by ?: '—' }}</div>
                            <div class="signatory-position">{{ $signatory->reviewed_by_position ?: '—' }}</div>
                        </div>
                        <div class="signatory-block">
                            <div class="signatory-label">Recommended by:</div>
                            <div class="signatory-name">{{ $signatory->recommended_by ?: '—' }}</div>
                            <div class="signatory-position">{{ $signatory->recommended_by_position ?: '—' }}</div>
                        </div>
                        <div class="signatory-block">
                            <div class="signatory-label">Approved by:</div>
                            <div class="signatory-name">{{ $signatory->approved_by ?: '—' }}</div>
                            <div class="signatory-position">{{ $signatory->approved_by_position ?: '—' }}</div>
                        </div>
                    </div>
                </section>
            @endif
            @if($plan->submissions->isNotEmpty())
                <section class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-5 py-4">
                        <h2 class="m-0 text-base font-bold text-slate-900">
                            Workflow History
                        </h2>
                        <p class="mb-0 mt-1 text-xs text-slate-500">
                            Submission, approval, return, finalization and reopening history.
                        </p>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach($plan->submissions as $submission)
                            @php
                                $actionLabel = match ($submission->action) {
                                    'submit' => 'Submitted',
                                    'approve' => 'Approved',
                                    'return' => 'Returned',
                                    'finalize' => 'Finalized',
                                    'reopen' => 'Reopened',
                                    default => ucfirst($submission->action),
                                };
                                $actionClass = match ($submission->action) {
                                    'submit' => 'bg-cyan-100 text-cyan-700',
                                    'approve' => 'bg-sky-100 text-sky-700',
                                    'return' => 'bg-amber-100 text-amber-700',
                                    'finalize' => 'bg-emerald-100 text-emerald-700',
                                    'reopen' => 'bg-violet-100 text-violet-700',
                                    default => 'bg-slate-100 text-slate-700',
                                };
                            @endphp
                            <div class="flex flex-col gap-2 px-5 py-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $actionClass }}">
                                            {{ $actionLabel }}
                                        </span>
                                        @if($submission->from_status)
                                            <span class="text-xs text-slate-500">
                                                {{ ucfirst($submission->from_status) }}
                                                <i class="fa fa-arrow-right mx-1"></i>
                                                {{ ucfirst($submission->to_status) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="mt-2 text-xs text-slate-600">
                                        By {{ $submission->actor?->name ?? 'User' }}
                                    </div>
                                    @if($submission->remarks)
                                        <div class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                                            <span class="font-bold">Remarks:</span>
                                            {!! nl2br(e($submission->remarks)) !!}
                                        </div>
                                    @endif
                                </div>
                                <div class="whitespace-nowrap text-xs text-slate-500">
                                    {{ $submission->acted_at?->format('M d, Y h:i A') ?? '—' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        @endif
        <div class="mt-6">
            @include('layouts.footers.auth.footer')
        </div>
    </div>
</div>
@if($plan)
    @can('return', $plan)
        <div id="returnPlanModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/50 p-4">
            <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <h3 class="m-0 text-base font-bold text-slate-900">Return Work Plan</h3>
                        <p class="mb-0 mt-1 text-xs text-slate-500">
                            Provide the reason or revisions required.
                        </p>
                    </div>
                    <button type="button"
                            id="btnCloseReturnModal"
                            class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-slate-500 hover:bg-slate-50">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="p-5">
                    <label for="returnRemarks" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-600">
                        Remarks
                    </label>
                    <textarea id="returnRemarks"
                              rows="5"
                              maxlength="5000"
                              placeholder="Enter return remarks..."
                              class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-100"></textarea>
                    <div id="returnRemarksError" class="mt-1 hidden text-xs text-rose-600"></div>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                    <button type="button"
                            id="btnCancelReturn"
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="button"
                            id="btnConfirmReturn"
                            data-url="{{ route('work-plans.return', $plan) }}"
                            class="rounded-lg bg-amber-600 px-4 py-2 text-xs font-bold text-white hover:bg-amber-700">
                        Return Work Plan
                    </button>
                </div>
            </div>
        </div>
    @endcan
@endif
<style>
    .work-plan-table{width:100%;table-layout:fixed;border-collapse:collapse}
    .work-plan-table th,.work-plan-table td{border:1px solid #94a3b8;vertical-align:top}
    .work-plan-table th{background:#e2e8f0;padding:8px 6px;text-align:center;font-weight:700;color:#1e293b}
    .work-plan-table td{padding:8px;color:#334155;background:#fff}
    .work-plan-table .classification-column{width:20%}
    .work-plan-table .activity-column{width:20%}
    .work-plan-table .month-column{width:5%}
    .work-plan-table .classification-cell,.work-plan-table .activity-cell,.work-plan-table .target-cell{font-size:10px;line-height:1.4;vertical-align:top;white-space:normal;overflow-wrap:break-word;word-wrap:break-word}
    .work-plan-table .target-cell{font-size:11px;line-height:1.45}
    .work-plan-table .target-range-cell{padding:8px 10px;vertical-align:top;text-align:left;background:#fff;line-height:1.4;white-space:normal;overflow-wrap:break-word;word-wrap:break-word}
    .work-plan-table .target-empty-cell{padding:0;background:#fff}
    .work-plan-table .section-header-row td{background:#cbd5e1;color:#0f172a;font-weight:800;text-transform:uppercase;padding:7px 10px}
    .work-plan-table .subheader-row td{background:#f1f5f9;color:#1e293b;font-weight:700;padding:7px 18px}
    .signatory-block{text-align:center}
    .signatory-label{margin-bottom:34px;text-align:left;font-size:12px;font-weight:700;color:#475569}
    .signatory-name{min-height:22px;border-bottom:1px solid #64748b;padding:0 8px 4px;font-size:13px;font-weight:700;color:#0f172a}
    .signatory-position{padding-top:4px;font-size:11px;color:#64748b}
</style>
@endsection
@push('js')
<script>
$(document).ready(function () {
    const csrfToken = '{{ csrf_token() }}';
    function escapeHtml(value) {
        return $('<div>').text(String(value ?? '')).html();
    }
    function showMessage(message, type = 'success') {
        $('#workPlanMessage').html(`
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${escapeHtml(message)}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
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
    function postWorkflow(url, data = {}, confirmMessage = null) {
        if (confirmMessage && !window.confirm(confirmMessage)) {
            return;
        }
        $('.workflow-action').prop('disabled', true);
        $.ajax({
            url: url,
            method: 'POST',
            data: {
                ...data,
                _token: csrfToken
            }
        }).done(function (response) {
            if (response?.success === false) {
                showMessage(response.message || 'The action could not be completed.', 'danger');
                return;
            }
            showMessage(response?.message || 'Work Plan updated successfully.');
            window.setTimeout(function () {
                window.location.reload();
            }, 500);
        }).fail(function (xhr) {
            showMessage(
                getErrorMessage(xhr, 'The Work Plan action could not be completed.'),
                'danger'
            );
            $('.workflow-action').prop('disabled', false);
        });
    }
    $('.workflow-action').on('click', function () {
        const url = $(this).data('url');
        const action = String($(this).data('action') || '');
        const messages = {
            submit: 'Submit this Work Plan for approval?',
            approve: 'Approve this Work Plan?',
            finalize: 'Finalize and lock this Work Plan?',
            reopen: 'Reopen this Work Plan as a draft?'
        };
        postWorkflow(url, {}, messages[action] || null);
    });
    $('#btnReturnPlan').on('click', function () {
        $('#returnRemarks').val('');
        $('#returnRemarksError').addClass('hidden').text('');
        $('#returnPlanModal').removeClass('hidden').addClass('flex');
    });
    function closeReturnModal() {
        $('#returnPlanModal').addClass('hidden').removeClass('flex');
    }
    $('#btnCloseReturnModal, #btnCancelReturn').on('click', function () {
        closeReturnModal();
    });
    $('#btnConfirmReturn').on('click', function () {
        const remarks = String($('#returnRemarks').val() || '').trim();
        if (!remarks) {
            $('#returnRemarksError')
                .removeClass('hidden')
                .text('Return remarks are required.');
            return;
        }
        $('#returnRemarksError').addClass('hidden').text('');
        $('#btnConfirmReturn').prop('disabled', true);
        $.ajax({
            url: $(this).data('url'),
            method: 'POST',
            data: {
                _token: csrfToken,
                remarks: remarks
            }
        }).done(function (response) {
            closeReturnModal();
            showMessage(response?.message || 'Work Plan returned successfully.');
            window.setTimeout(function () {
                window.location.reload();
            }, 500);
        }).fail(function (xhr) {
            $('#returnRemarksError')
                .removeClass('hidden')
                .text(getErrorMessage(xhr, 'Unable to return the Work Plan.'));
            $('#btnConfirmReturn').prop('disabled', false);
        });
    });
    $('#returnPlanModal').on('click', function (event) {
        if (event.target === this) {
            closeReturnModal();
        }
    });
});
</script>
@endpush
