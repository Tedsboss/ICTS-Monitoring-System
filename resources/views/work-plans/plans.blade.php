@extends('layouts.app')
@section('content')
@php
    $isWorkPlanAdministrator = in_array(
        (int) auth()->user()->role_id,
        [1, 29],
        true
    );
@endphp

<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-2 px-3">
        @include('layouts.navbars.auth.topnav', ['title' => 'All Work Plans'])
        @include('layouts.navbars.auth.topnav-withdatetime')
    </div>
</nav>

<div class="px-4 pb-8 pt-4">
    <div id="pageMessage"></div>

    <section class="mb-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <form method="GET" action="{{ route('work-plans.plans') }}" class="flex flex-wrap items-end gap-3">
                <div>
                    <label for="filterFiscalYear" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600">
                        Fiscal Year
                    </label>
                    <select id="filterFiscalYear" name="fiscal_year"
                            class="block min-w-[150px] rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                        @if($fiscalYears->isEmpty())
                            <option value="{{ $fiscalYear }}">{{ $fiscalYear }}</option>
                        @else
                            @foreach($fiscalYears as $year)
                                <option value="{{ $year }}" {{ (int) $fiscalYear === (int) $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                @if($isWorkPlanAdministrator)
                    <div class="w-full sm:w-auto">
                        <label for="filterStaff" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600">
                            Office/Staff
                        </label>
                        <select id="filterStaff" name="staff_id"
                                class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100 sm:min-w-[300px]">
                            <option value="">All Offices</option>
                            @foreach($staffs as $staff)
                                <option value="{{ $staff->id }}" {{ (int) $staffId === (int) $staff->id ? 'selected' : '' }}>
                                    {{ $staff->abbreviation ?: $staff->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <button type="submit"
                        class="inline-flex h-[38px] items-center gap-2 rounded-lg border border-sky-200 bg-sky-50 px-4 text-sm font-semibold text-sky-700 shadow-sm transition hover:bg-sky-100">
                    <i class="fa fa-filter"></i>
                    <span>Load</span>
                </button>

                <a href="{{ route('work-plans.plans') }}"
                   class="inline-flex h-[38px] items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    <i class="fa fa-refresh"></i>
                    <span>Reset</span>
                </a>
            </form>

            @can('create', \App\Models\WorkPlan::class)
                <a href="{{ route('work-plans.builder') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                    <i class="fa fa-plus"></i>
                    <span>New Work Plan</span>
                </a>
            @endcan
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-lg font-bold text-slate-900">Filed Work Plans</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Browse Work Plans by fiscal year, office and workflow status.
                </p>
            </div>
            <span class="inline-flex w-fit items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">
                {{ $plans->count() }} plan{{ $plans->count() === 1 ? '' : 's' }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] border-collapse">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="whitespace-nowrap border-b border-slate-200 px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                            Fiscal Year
                        </th>
                        <th class="whitespace-nowrap border-b border-slate-200 px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                            Office/Staff
                        </th>
                        <th class="whitespace-nowrap border-b border-slate-200 px-4 py-3 text-center text-xs font-bold uppercase tracking-wide text-slate-500">
                            Activities
                        </th>
                        <th class="whitespace-nowrap border-b border-slate-200 px-4 py-3 text-center text-xs font-bold uppercase tracking-wide text-slate-500">
                            Status
                        </th>
                        <th class="whitespace-nowrap border-b border-slate-200 px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                            Last Updated
                        </th>
                        <th class="whitespace-nowrap border-b border-slate-200 px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                        @php
                            $isFinalized = ($plan->finalized ?? 'no') === 'yes';
                            $statusKey = $isFinalized ? 'finalized' : ($plan->status ?? 'draft');

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

                            $officeName = $plan->staff
                                ? ($plan->staff->abbreviation ?: $plan->staff->name)
                                : '—';
                        @endphp

                        <tr class="transition hover:bg-slate-50">
                            <td class="border-b border-slate-100 px-5 py-4 text-sm font-bold text-slate-900">
                                FY {{ $plan->fiscal_year }}
                            </td>

                            <td class="border-b border-slate-100 px-4 py-4">
                                <div class="max-w-[320px] break-words text-sm font-semibold text-slate-800">
                                    {{ $officeName }}
                                </div>
                                @if($plan->staff && $plan->staff->abbreviation && $plan->staff->name !== $plan->staff->abbreviation)
                                    <div class="mt-1 max-w-[320px] break-words text-xs text-slate-500">
                                        {{ $plan->staff->name }}
                                    </div>
                                @endif
                            </td>

                            <td class="border-b border-slate-100 px-4 py-4 text-center">
                                <span class="inline-flex min-w-[46px] items-center justify-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                    {{ $plan->items_count ?? $plan->items()->count() }}
                                </span>
                            </td>

                            <td class="border-b border-slate-100 px-4 py-4 text-center">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold {{ $statusClass }}">
                                    @if($isFinalized)
                                        <i class="fa fa-lock"></i>
                                    @endif
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap border-b border-slate-100 px-4 py-4 text-sm text-slate-600">
                                {{ $plan->updated_at ? $plan->updated_at->format('M d, Y h:i A') : '—' }}
                            </td>

                            <td class="border-b border-slate-100 px-5 py-4">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    @can('view', $plan)
                                        <a href="{{ route('work-plans.index', [
                                            'fiscal_year' => $plan->fiscal_year,
                                            'staff_id' => $plan->staff_id,
                                        ]) }}"
                                           class="inline-flex items-center gap-1.5 rounded-lg border border-sky-200 bg-sky-50 px-3 py-1.5 text-xs font-semibold text-sky-700 transition hover:bg-sky-100"
                                           title="View Work Plan">
                                            <i class="fa fa-eye"></i>
                                            <span>View</span>
                                        </a>
                                    @endcan

                                    @can('update', $plan)
                                        @if($plan->isEditable())
                                            <a href="{{ route('work-plans.builder', [
                                                'fiscal_year' => $plan->fiscal_year,
                                                'staff_id' => $plan->staff_id,
                                            ]) }}"
                                               class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
                                               title="Edit Work Plan">
                                                <i class="fa fa-pencil"></i>
                                                <span>Edit</span>
                                            </a>
                                        @else
                                            <button type="button" disabled
                                                    class="inline-flex cursor-not-allowed items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-400"
                                                    title="This Work Plan is currently read-only">
                                                <i class="fa fa-lock"></i>
                                                <span>Locked</span>
                                            </button>
                                        @endif
                                    @endcan

                                    @can('delete', $plan)
                                        @if($plan->isEditable())
                                            <form method="POST"
                                                  action="{{ route('work-plans.destroy', $plan) }}"
                                                  class="m-0"
                                                  onsubmit="return confirm('Delete this Work Plan? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex h-[30px] w-[34px] items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-xs text-rose-600 transition hover:bg-rose-100"
                                                        title="Delete Work Plan">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-xl text-slate-400">
                                    <i class="fa fa-folder-open-o"></i>
                                </div>
                                <p class="mt-3 text-sm font-semibold text-slate-600">
                                    No Work Plans have been filed for FY {{ $fiscalYear }}.
                                </p>
                                <p class="mt-1 text-xs text-slate-400">
                                    Create a new Work Plan to get started.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-6">
        @include('layouts.footers.auth.footer')
    </div>
</div>
@endsection