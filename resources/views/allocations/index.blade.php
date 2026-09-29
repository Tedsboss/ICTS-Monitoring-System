@extends('layouts.app')

@section('content')
<div class="px-4 pb-8 pt-4">
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-0 shadow-none border-radius-xl z-index-sticky"
         id="navbarBlur"
         data-scroll="false">
        <div class="container-fluid px-0">
            @include('layouts.navbars.auth.topnav', ['title' => 'Allocation Management'])
            @include('layouts.navbars.auth.topnav-withdatetime')
        </div>
    </nav>

    <div id="pageMessage"></div>

    @php
        $allocationCount = $allocations->count();
        $expenseCount = $allocations->sum(fn ($allocation) => $allocation->expenses->count());
        $grandTotal = $allocations->sum(
            fn ($allocation) => $allocation->expenses->sum('cost')
        );
    @endphp

    {{-- Page Header --}}
    <section class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h4 class="mb-1 text-lg font-bold text-slate-800">
                    Allocation Management
                </h4>

                <p class="mb-0 text-sm text-slate-500">
                    Manage budget allocations by Fiscal Year and Level.
                </p>
            </div>

            <a href="{{ route('allocations.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600
                      px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                <i class="fa fa-plus"></i>
                <span>Add Allocation</span>
            </a>
        </div>
    </section>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <i class="fa fa-check-circle mr-1"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <i class="fa fa-exclamation-circle mr-1"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- Summary --}}
    <section class="mb-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Allocations
                    </p>

                    <p class="mb-0 text-2xl font-bold text-slate-800">
                        {{ number_format($allocationCount) }}
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                    <i class="fa fa-list-alt text-lg"></i>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Expense Entries
                    </p>

                    <p class="mb-0 text-2xl font-bold text-slate-800">
                        {{ number_format($expenseCount) }}
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <i class="fa fa-tags text-lg"></i>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Total Allocated
                    </p>

                    <p class="mb-0 text-2xl font-bold text-slate-800">
                        ₱{{ number_format((float) $grandTotal, 2) }}
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <i class="fa fa-money text-lg"></i>
                </div>
            </div>
        </div>
    </section>

    {{-- Filters --}}
    <section class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h5 class="mb-0 text-base font-bold text-slate-800">
                        Allocation Filters
                    </h5>

                    <p class="mb-0 mt-1 text-xs text-slate-500">
                        Filter allocations by Fiscal Year or Level.
                    </p>
                </div>

                @if(request()->filled('year_id') || request()->filled('level_id'))
                    <span class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">
                        <i class="fa fa-filter mr-1"></i>
                        Filters Active
                    </span>
                @endif
            </div>
        </div>

        <div class="p-5">
            <form method="GET" action="{{ route('allocations.index') }}">
                <div class="grid gap-5 md:grid-cols-3">

                    {{-- Fiscal Year --}}
                    <div>
                        <label for="year_id"
                               class="mb-2 block text-sm font-semibold text-slate-700">
                            Fiscal Year
                        </label>

                        <div class="flex overflow-hidden rounded-xl border border-slate-300 bg-white
                                    transition focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-100">
                            <div class="flex w-11 shrink-0 items-center justify-center border-r border-slate-200
                                        bg-slate-50 text-slate-400">
                                <i class="fa fa-calendar"></i>
                            </div>

                            <select name="year_id"
                                    id="year_id"
                                    class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5
                                           text-sm text-slate-700 outline-none focus:ring-0">
                                <option value="">All Fiscal Years</option>

                                @foreach($fiscalYears as $fiscalYear)
                                    <option value="{{ $fiscalYear->id }}"
                                        @selected(request('year_id') == $fiscalYear->id)>
                                        {{ $fiscalYear->year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Level --}}
                    <div>
                        <label for="level_id"
                               class="mb-2 block text-sm font-semibold text-slate-700">
                            Level
                        </label>

                        <div class="flex overflow-hidden rounded-xl border border-slate-300 bg-white
                                    transition focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-100">
                            <div class="flex w-11 shrink-0 items-center justify-center border-r border-slate-200
                                        bg-slate-50 text-slate-400">
                                <i class="fa fa-sitemap"></i>
                            </div>

                            <select name="level_id"
                                    id="level_id"
                                    class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5
                                           text-sm text-slate-700 outline-none focus:ring-0">
                                <option value="">All Levels</option>

                                @foreach($levels as $level)
                                    <option value="{{ $level->id }}"
                                        @selected(request('level_id') == $level->id)>
                                        {{ $level->level_code }} - {{ $level->level_description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Filter Actions --}}
                    <div class="flex items-end gap-2">
                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600
                                       px-4 py-2.5 text-sm font-semibold text-white shadow-sm
                                       transition hover:bg-sky-700">
                            <i class="fa fa-filter"></i>
                            <span>Apply Filter</span>
                        </button>

                        <a href="{{ route('allocations.index') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300
                                  bg-white px-4 py-2.5 text-sm font-semibold text-slate-700
                                  transition hover:bg-slate-50">
                            <i class="fa fa-refresh"></i>
                            <span>Reset</span>
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </section>

    {{-- Allocation Table --}}
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h5 class="mb-0 text-base font-bold text-slate-800">
                        Configured Allocations
                    </h5>

                    <p class="mb-0 mt-1 text-xs text-slate-500">
                        Fiscal Year and Level allocations with their configured expenses.
                    </p>
                </div>

                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                    {{ $allocationCount }}
                    {{ $allocationCount === 1 ? 'allocation' : 'allocations' }}
                </span>
            </div>
        </div>

        @if($allocations->count())
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-left">
                            <th class="w-10 px-5 py-3 text-center font-semibold text-slate-600">
                                #
                            </th>

                            <th class="px-5 py-3 font-semibold text-slate-600">
                                Fiscal Year
                            </th>

                            <th class="px-5 py-3 font-semibold text-slate-600">
                                Level
                            </th>

                            <th class="px-5 py-3 font-semibold text-slate-600">
                                Expenses
                            </th>

                            <th class="px-5 py-3 text-right font-semibold text-slate-600">
                                Total Allocation
                            </th>

                            <th class="px-5 py-3 text-right font-semibold text-slate-600">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($allocations as $allocation)
                            @php
                                $totalCost = $allocation->expenses->sum('cost');
                            @endphp

                            <tr class="border-b border-slate-100 align-top transition hover:bg-slate-50/70">

                                {{-- Number --}}
                                <td class="px-5 py-4 text-center text-xs font-semibold text-slate-400">
                                    {{ $loop->iteration }}
                                </td>

                                {{-- Fiscal Year --}}
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-700">
                                        {{ $allocation->fiscalYear->year }}
                                    </span>
                                </td>

                                {{-- Level --}}
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-700">
                                        {{ $allocation->level->level_code }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $allocation->level->level_description }}
                                    </div>
                                </td>

                                {{-- Expenses --}}
                                <td class="px-5 py-4">
                                    @if($allocation->expenses->count())
                                        <div class="space-y-2">
                                            @foreach($allocation->expenses as $expense)
                                                @php
                                                    $isMooe = strtoupper($expense->expenseType->type) === 'MOOE';
                                                @endphp

                                                <div class="flex items-center justify-between gap-4 rounded-lg
                                                            border border-slate-200 bg-slate-50 px-3 py-2.5">

                                                    <div class="min-w-0">
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            <span class="inline-flex items-center rounded-full
                                                                         {{ $isMooe
                                                                            ? 'bg-emerald-100 text-emerald-700'
                                                                            : 'bg-amber-100 text-amber-700' }}
                                                                         px-2 py-0.5 text-[10px] font-bold">
                                                                {{ $expense->expenseType->type }}
                                                            </span>

                                                            <span class="font-semibold text-slate-700">
                                                                {{ $expense->expenseType->expense_description }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <span class="whitespace-nowrap font-semibold text-slate-700">
                                                        ₱{{ number_format((float) $expense->cost, 2) }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400">
                                            No expenses configured.
                                        </span>
                                    @endif
                                </td>

                                {{-- Total --}}
                                <td class="px-5 py-4 text-right">
                                    <div class="text-base font-bold text-slate-800">
                                        ₱{{ number_format((float) $totalCost, 2) }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-400">
                                        {{ $allocation->expenses->count() }}
                                        {{ $allocation->expenses->count() === 1 ? 'expense' : 'expenses' }}
                                    </div>
                                </td>

                                {{-- Actions --}}
                                <td class="px-5 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">

                                        <a href="{{ route('allocations.edit', $allocation) }}"
                                           class="inline-flex items-center justify-center gap-1 rounded-lg border border-sky-200
                                                  bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-700
                                                  transition hover:bg-sky-100">
                                            <i class="fa fa-pencil"></i>
                                            Edit
                                        </a>

                                        <form action="{{ route('allocations.destroy', $allocation) }}"
                                              method="POST"
                                              class="delete-allocation-form inline">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="delete-allocation-button inline-flex items-center justify-center
                                                           gap-1 rounded-lg border border-red-200 bg-red-50 px-3 py-2
                                                           text-xs font-semibold text-red-700 transition hover:bg-red-100">
                                                <i class="fa fa-trash"></i>
                                                Delete
                                            </button>
                                        </form>

                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            {{-- Empty / No Results --}}
            <div class="px-5 py-14 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <i class="fa fa-folder-open-o text-2xl"></i>
                </div>

                @if(request()->filled('year_id') || request()->filled('level_id'))
                    <h6 class="mb-1 text-sm font-bold text-slate-700">
                        No matching allocations found
                    </h6>

                    <p class="mb-4 text-xs text-slate-500">
                        No allocations match the selected Fiscal Year and Level filters.
                    </p>

                    <a href="{{ route('allocations.index') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300
                              bg-white px-4 py-2 text-sm font-semibold text-slate-700
                              transition hover:bg-slate-50">
                        <i class="fa fa-refresh"></i>
                        Clear Filters
                    </a>
                @else
                    <h6 class="mb-1 text-sm font-bold text-slate-700">
                        No allocations configured yet
                    </h6>

                    <p class="mb-4 text-xs text-slate-500">
                        Create your first allocation to begin managing budget allocations.
                    </p>

                    <a href="{{ route('allocations.create') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600
                              px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                        <i class="fa fa-plus"></i>
                        Add Allocation
                    </a>
                @endif
            </div>
        @endif
    </section>

    <div class="mt-6">
        @include('layouts.footers.auth.footer')
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-allocation-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const confirmed = confirm(
                'Delete this allocation?\n\n' +
                'All expense entries under this allocation will also be deleted.\n\n' +
                'This action cannot be undone.'
            );

            if (!confirmed) {
                event.preventDefault();
                return;
            }

            const button = form.querySelector('.delete-allocation-button');

            if (button) {
                button.disabled = true;
                button.classList.add(
                    'cursor-not-allowed',
                    'opacity-60'
                );

                button.innerHTML =
                    '<i class="fa fa-spinner fa-spin"></i> Deleting...';
            }
        });
    });
});
</script>
@endsection