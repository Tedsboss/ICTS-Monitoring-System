@extends('layouts.app')
@section('content')
<div class="px-4 pb-8 pt-4">
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-0 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false">
        <div class="container-fluid px-0">
            @include('layouts.navbars.auth.topnav', ['title' => 'Allocation Management'])
            @include('layouts.navbars.auth.topnav-withdatetime')
        </div>
    </nav>
    <div id="pageMessage"></div>
    @php
        $allocationCount = $allocations->count();
        $expenseCount = $allocations->sum(fn ($allocation) => $allocation->expenses->count());
        $totalMooeBudget = $allocations->sum(fn ($allocation) => (float) $allocation->mooe_budget);
        $totalCoBudget = $allocations->sum(fn ($allocation) => (float) $allocation->co_budget);
        $totalOverallBudget = $totalMooeBudget + $totalCoBudget;
        $totalMooeUsed = $allocations->sum(
            fn ($allocation) => $allocation->expenses
                ->filter(fn ($expense) => strtoupper((string) optional($expense->expenseType)->type) === 'MOOE')
                ->sum('cost')
        );
        $totalCoUsed = $allocations->sum(
            fn ($allocation) => $allocation->expenses
                ->filter(fn ($expense) => strtoupper((string) optional($expense->expenseType)->type) === 'CO')
                ->sum('cost')
        );
        $totalUsed = $totalMooeUsed + $totalCoUsed;
    @endphp
    <section class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h4 class="mb-1 text-lg font-bold text-slate-800">Allocation Management</h4>
                <p class="mb-0 text-sm text-slate-500">Manage Program / Project budget allocations by Staff / Office, Fiscal Year, and Level.</p>
            </div>
            <a href="{{ route('allocations.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                <i class="fa fa-plus"></i>
                <span>Add Allocation</span>
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
    @if($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <div class="mb-2 font-semibold">
                <i class="fa fa-exclamation-circle mr-1"></i>
                Please correct the following:
            </div>
            <ul class="mb-0 list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <section class="mb-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Allocations</p>
            <p class="mb-0 text-2xl font-bold text-slate-800">{{ number_format($allocationCount) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Total Overall Budget</p>
            <p class="mb-0 text-2xl font-bold text-slate-800">₱{{ number_format($totalOverallBudget, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Total Used</p>
            <p class="mb-0 text-2xl font-bold text-slate-800">₱{{ number_format($totalUsed, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Remaining</p>
            <p class="mb-0 text-2xl font-bold {{ $totalOverallBudget - $totalUsed < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                ₱{{ number_format($totalOverallBudget - $totalUsed, 2) }}
            </p>
        </div>
    </section>
    <section class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h5 class="mb-0 text-base font-bold text-slate-800">Allocation Filters</h5>
                    <p class="mb-0 mt-1 text-xs text-slate-500">Filter allocations by Fiscal Year, Level, or Program / Project.</p>
                </div>
                @if(request()->filled('year_id') || request()->filled('level_id') || request()->filled('program_id'))
                    <span class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">
                        <i class="fa fa-filter mr-1"></i>
                        Filters Active
                    </span>
                @endif
            </div>
        </div>
        <div class="p-5">
            <form method="GET" action="{{ route('allocations.index') }}">
                <div class="grid gap-5 md:grid-cols-4">
                    <div>
                        <label for="year_id" class="mb-2 block text-sm font-semibold text-slate-700">Fiscal Year</label>
                        <div class="flex overflow-hidden rounded-xl border border-slate-300 bg-white transition focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-100">
                            <div class="flex w-11 shrink-0 items-center justify-center border-r border-slate-200 bg-slate-50 text-slate-400">
                                <i class="fa fa-calendar"></i>
                            </div>
                            <select name="year_id" id="year_id" class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5 text-sm text-slate-700 outline-none focus:ring-0">
                                <option value="">All Fiscal Years</option>
                                @foreach($fiscalYears as $fiscalYear)
                                    <option value="{{ $fiscalYear->id }}" @selected(request('year_id') == $fiscalYear->id)>
                                        {{ $fiscalYear->year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="level_id" class="mb-2 block text-sm font-semibold text-slate-700">Level</label>
                        <div class="flex overflow-hidden rounded-xl border border-slate-300 bg-white transition focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-100">
                            <div class="flex w-11 shrink-0 items-center justify-center border-r border-slate-200 bg-slate-50 text-slate-400">
                                <i class="fa fa-sitemap"></i>
                            </div>
                            <select name="level_id" id="level_id" class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5 text-sm text-slate-700 outline-none focus:ring-0">
                                <option value="">All Levels</option>
                                @foreach($levels as $level)
                                    <option value="{{ $level->id }}" @selected(request('level_id') == $level->id)>
                                        {{ $level->level_code }} - {{ $level->level_description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="program_id" class="mb-2 block text-sm font-semibold text-slate-700">Program / Project</label>
                        <div class="flex overflow-hidden rounded-xl border border-slate-300 bg-white transition focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-100">
                            <div class="flex w-11 shrink-0 items-center justify-center border-r border-slate-200 bg-slate-50 text-slate-400">
                                <i class="fa fa-sitemap"></i>
                            </div>
                            <select name="program_id" id="program_id" class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5 text-sm text-slate-700 outline-none focus:ring-0">
                                <option value="">All Programs / Projects</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" @selected(request('program_id') == $program->id)>
                                        {{ $program->program }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                            <i class="fa fa-filter"></i>
                            <span>Apply Filter</span>
                        </button>
                        <a href="{{ route('allocations.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            <i class="fa fa-refresh"></i>
                            <span>Reset</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h5 class="mb-0 text-base font-bold text-slate-800">Configured Allocations</h5>
                    <p class="mb-0 mt-1 text-xs text-slate-500">Program-specific budgets and their configured MOOE/CO expenses.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                    {{ $allocationCount }}
                    {{ $allocationCount === 1 ? 'allocation' : 'allocations' }}
                </span>
            </div>
        </div>
        @if($allocations->count())
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1650px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-left">
                            <th class="w-10 px-5 py-3 text-center font-semibold text-slate-600">#</th>
                            <th class="px-5 py-3 font-semibold text-slate-600">Staff / Office</th>
                            <th class="px-5 py-3 font-semibold text-slate-600">Fiscal Year</th>
                            <th class="px-5 py-3 font-semibold text-slate-600">Level</th>
                            <th class="px-5 py-3 font-semibold text-slate-600">Program / Project</th>
                            <th class="px-5 py-3 font-semibold text-slate-600">Overall Budget</th>
                            <th class="px-5 py-3 font-semibold text-slate-600">Expenses</th>
                            <th class="px-5 py-3 font-semibold text-slate-600">Remaining</th>
                            <th class="px-5 py-3 text-right font-semibold text-slate-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allocations as $allocation)
                            @php
                                $mooeBudget = (float) $allocation->mooe_budget;
                                $coBudget = (float) $allocation->co_budget;
                                $totalBudget = $mooeBudget + $coBudget;
                                $mooeUsed = $allocation->expenses
                                    ->filter(fn ($expense) => strtoupper((string) optional($expense->expenseType)->type) === 'MOOE')
                                    ->sum('cost');
                                $coUsed = $allocation->expenses
                                    ->filter(fn ($expense) => strtoupper((string) optional($expense->expenseType)->type) === 'CO')
                                    ->sum('cost');
                                $totalUsed = $mooeUsed + $coUsed;
                                $mooeRemaining = $mooeBudget - $mooeUsed;
                                $coRemaining = $coBudget - $coUsed;
                                $totalRemaining = $totalBudget - $totalUsed;
                            @endphp
                            <tr class="border-b border-slate-100 align-top transition hover:bg-slate-50/70">
                                <td class="px-5 py-4 text-center text-xs font-semibold text-slate-400">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-700">
                                        {{ $allocation->staff?->name ?? 'Unassigned' }}
                                    </div>
                                    @if($allocation->staff?->abbreviation)
                                        <div class="mt-1 text-xs text-slate-500">
                                            {{ $allocation->staff->abbreviation }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-700">
                                        {{ $allocation->fiscalYear?->year ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-700">
                                        {{ $allocation->level?->level_code ?? '—' }}
                                    </div>
                                    @if($allocation->level?->level_description)
                                        <div class="mt-1 text-xs text-slate-500">
                                            {{ $allocation->level->level_description }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if($allocation->program)
                                        <div class="font-semibold text-slate-700">
                                            {{ $allocation->program->program }}
                                        </div>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">
                                            Program not assigned
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between gap-4">
                                            <span class="text-xs font-semibold text-emerald-700">MOOE</span>
                                            <span class="font-semibold text-slate-700">
                                                ₱{{ number_format($mooeBudget, 2) }}
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between gap-4">
                                            <span class="text-xs font-semibold text-amber-700">CO</span>
                                            <span class="font-semibold text-slate-700">
                                                ₱{{ number_format($coBudget, 2) }}
                                            </span>
                                        </div>
                                        <div class="border-t border-slate-200 pt-2">
                                            <div class="flex items-center justify-between gap-4">
                                                <span class="text-xs font-bold text-slate-600">Total</span>
                                                <span class="font-bold text-slate-800">
                                                    ₱{{ number_format($totalBudget, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between gap-4">
                                            <span class="text-xs font-semibold text-emerald-700">MOOE Used</span>
                                            <span class="font-semibold text-slate-700">
                                                ₱{{ number_format($mooeUsed, 2) }}
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between gap-4">
                                            <span class="text-xs font-semibold text-amber-700">CO Used</span>
                                            <span class="font-semibold text-slate-700">
                                                ₱{{ number_format($coUsed, 2) }}
                                            </span>
                                        </div>
                                        <div class="border-t border-slate-200 pt-2">
                                            <div class="flex items-center justify-between gap-4">
                                                <span class="text-xs font-bold text-slate-600">Total Used</span>
                                                <span class="font-bold text-slate-800">
                                                    ₱{{ number_format($totalUsed, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between gap-4">
                                            <span class="text-xs font-semibold text-slate-500">MOOE</span>
                                            <span class="font-semibold {{ $mooeRemaining < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                                ₱{{ number_format($mooeRemaining, 2) }}
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between gap-4">
                                            <span class="text-xs font-semibold text-slate-500">CO</span>
                                            <span class="font-semibold {{ $coRemaining < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                                ₱{{ number_format($coRemaining, 2) }}
                                            </span>
                                        </div>
                                        <div class="border-t border-slate-200 pt-2">
                                            <div class="flex items-center justify-between gap-4">
                                                <span class="text-xs font-bold text-slate-600">Remaining</span>
                                                <span class="font-bold {{ $totalRemaining < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                                    ₱{{ number_format($totalRemaining, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('allocations.edit', $allocation) }}" class="inline-flex items-center justify-center gap-1 rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-700 transition hover:bg-sky-100">
                                            <i class="fa fa-pencil"></i>
                                            Edit
                                        </a>
                                        <form action="{{ route('allocations.destroy', $allocation) }}" method="POST" class="delete-allocation-form inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="delete-allocation-button inline-flex items-center justify-center gap-1 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-100">
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
            <div class="px-5 py-14 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <i class="fa fa-folder-open-o text-2xl"></i>
                </div>
                @if(request()->filled('year_id') || request()->filled('level_id') || request()->filled('program_id'))
                    <h6 class="mb-1 text-sm font-bold text-slate-700">No matching allocations found</h6>
                    <p class="mb-4 text-xs text-slate-500">No allocations match the selected filters.</p>
                    <a href="{{ route('allocations.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <i class="fa fa-refresh"></i>
                        Clear Filters
                    </a>
                @else
                    <h6 class="mb-1 text-sm font-bold text-slate-700">No allocations configured yet</h6>
                    <p class="mb-4 text-xs text-slate-500">Create your first allocation to begin managing Program / Project budgets.</p>
                    <a href="{{ route('allocations.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
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
            if (!confirm(
                'Delete this allocation?\n\n' +
                'All expense entries under this allocation will also be deleted.\n\n' +
                'This action cannot be undone.'
            )) {
                event.preventDefault();
                return;
            }
            const button = form.querySelector('.delete-allocation-button');
            if (button) {
                button.disabled = true;
                button.classList.add('cursor-not-allowed', 'opacity-60');
                button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Deleting...';
            }
        });
    });
});
</script>
@endsection
