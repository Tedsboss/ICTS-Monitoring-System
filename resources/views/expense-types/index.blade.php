@extends('layouts.app')

@section('content')
<div class="px-4 pb-8 pt-4">
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-0 shadow-none border-radius-xl z-index-sticky"
         id="navbarBlur"
         data-scroll="false">
        <div class="container-fluid px-0">
            @include('layouts.navbars.auth.topnav', ['title' => 'Expense Type Management'])
            @include('layouts.navbars.auth.topnav-withdatetime')
        </div>
    </nav>

    @php
        $totalExpenseTypes = $expenseTypes->count();
        $expenseTypesInUse = $expenseTypes->where('allocation_expenses_count', '>', 0)->count();
        $availableExpenseTypes = $totalExpenseTypes - $expenseTypesInUse;
        $mooeCount = $expenseTypes->filter(fn ($expenseType) => strtoupper($expenseType->type) === 'MOOE')->count();
        $coCount = $expenseTypes->filter(fn ($expenseType) => strtoupper($expenseType->type) === 'CO')->count();
    @endphp

    <div id="pageMessage"></div>

    <section class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h4 class="mb-1 text-lg font-bold text-slate-800">
                    Expense Type Management
                </h4>
                <p class="mb-0 text-sm text-slate-500">
                    Configure the expense types available for Allocation Management.
                </p>
            </div>

            <a href="{{ route('expense-types.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600
                      px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                <i class="fa fa-plus"></i>
                <span>Add Expense Type</span>
            </a>
        </div>
    </section>

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

    <section class="mb-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Total Types
                    </p>
                    <p class="mb-0 text-2xl font-bold text-slate-800">
                        {{ $totalExpenseTypes }}
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-sky-100 text-sky-700">
                    <i class="fa fa-tags"></i>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        In Use
                    </p>
                    <p class="mb-0 text-2xl font-bold text-slate-800">
                        {{ $expenseTypesInUse }}
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-sky-100 text-sky-700">
                    <i class="fa fa-link"></i>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        MOOE / CO
                    </p>
                    <p class="mb-0 text-2xl font-bold text-slate-800">
                        {{ $mooeCount }} / {{ $coCount }}
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-100 text-violet-700">
                    <i class="fa fa-pie-chart"></i>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Available
                    </p>
                    <p class="mb-0 text-2xl font-bold text-slate-800">
                        {{ $availableExpenseTypes }}
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                    <i class="fa fa-check-circle"></i>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h5 class="mb-0 text-base font-bold text-slate-800">
                        Configured Expense Types
                    </h5>
                    <p class="mb-0 mt-1 text-xs text-slate-500">
                        These expense types can be assigned to an Allocation.
                    </p>
                </div>

                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                    {{ $totalExpenseTypes }}
                    {{ $totalExpenseTypes === 1 ? 'type' : 'types' }}
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[850px] text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-left">
                        <th class="px-5 py-3 font-semibold text-slate-600">
                            Type
                        </th>
                        <th class="px-5 py-3 font-semibold text-slate-600">
                            Expense Description
                        </th>
                        <th class="px-5 py-3 text-center font-semibold text-slate-600">
                            Used in Allocations
                        </th>
                        <th class="px-5 py-3 text-center font-semibold text-slate-600">
                            Status
                        </th>
                        <th class="px-5 py-3 text-right font-semibold text-slate-600">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($expenseTypes as $expenseType)
                        @php
                            $allocationCount = $expenseType->allocation_expenses_count ?? 0;
                            $expenseTypeCode = strtoupper($expenseType->type);
                        @endphp

                        <tr class="border-b border-slate-100 transition hover:bg-slate-50/70">
                            <td class="px-5 py-4">
                                @if($expenseTypeCode === 'MOOE')
                                    <span class="inline-flex items-center rounded-full bg-sky-100 px-2.5 py-1
                                                 text-xs font-semibold text-sky-700">
                                        <i class="fa fa-cubes mr-1"></i>
                                        MOOE
                                    </span>
                                @elseif($expenseTypeCode === 'CO')
                                    <span class="inline-flex items-center rounded-full bg-violet-100 px-2.5 py-1
                                                 text-xs font-semibold text-violet-700">
                                        <i class="fa fa-building mr-1"></i>
                                        CO
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1
                                                 text-xs font-semibold text-slate-600">
                                        {{ $expenseType->type }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <div class="font-medium text-slate-700">
                                    {{ $expenseType->expense_description }}
                                </div>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex min-w-[2rem] items-center justify-center rounded-full
                                             bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                    {{ $allocationCount }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-center">
                                @if($allocationCount > 0)
                                    <span class="inline-flex items-center rounded-full bg-sky-100 px-2.5 py-1
                                                 text-xs font-semibold text-sky-700">
                                        <i class="fa fa-link mr-1"></i>
                                        In Use
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1
                                                 text-xs font-semibold text-emerald-700">
                                        <i class="fa fa-check-circle mr-1"></i>
                                        Available
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('expense-types.edit', $expenseType) }}"
                                       class="inline-flex items-center justify-center gap-1 rounded-lg
                                              border border-sky-200 bg-sky-50 px-3 py-2 text-xs
                                              font-semibold text-sky-700 transition hover:bg-sky-100">
                                        <i class="fa fa-pencil"></i>
                                        Edit
                                    </a>

                                    @if($allocationCount === 0)
                                        <form action="{{ route('expense-types.destroy', $expenseType) }}"
                                              method="POST"
                                              class="inline"
                                              onsubmit="return confirm('Delete {{ $expenseType->type }} - {{ $expenseType->expense_description }}? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="inline-flex items-center justify-center gap-1 rounded-lg
                                                           border border-red-200 bg-red-50 px-3 py-2 text-xs
                                                           font-semibold text-red-700 transition hover:bg-red-100">
                                                <i class="fa fa-trash"></i>
                                                Delete
                                            </button>
                                        </form>
                                    @else
                                        <span class="inline-flex items-center justify-center gap-1 rounded-lg
                                                     border border-slate-200 bg-slate-50 px-3 py-2 text-xs
                                                     font-semibold text-slate-400"
                                              title="This expense type is already used in an allocation.">
                                            <i class="fa fa-lock"></i>
                                            Locked
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full
                                            bg-slate-100 text-slate-400">
                                    <i class="fa fa-folder-open-o text-xl"></i>
                                </div>

                                <p class="mb-1 text-sm font-semibold text-slate-700">
                                    No Expense Types Configured
                                </p>

                                <p class="mb-4 text-xs text-slate-500">
                                    Add an expense type to make it available for Allocation Management.
                                </p>

                                <a href="{{ route('expense-types.create') }}"
                                   class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-4 py-2
                                          text-xs font-semibold text-white transition hover:bg-sky-700">
                                    <i class="fa fa-plus"></i>
                                    Add Expense Type
                                </a>
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