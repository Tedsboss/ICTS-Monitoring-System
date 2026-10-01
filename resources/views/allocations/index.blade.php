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
        $effectiveAmounts = function ($plan) {
            $mooe = (float) $plan->mooe;
            $co = (float) $plan->capital_outlay;
            if ($plan->contract_amount === null || $plan->contract_amount === '') {
                return [$mooe, $co];
            }
            $contract = (float) $plan->contract_amount;
            $total = $mooe + $co;
            if ($total <= 0) {
                return [0.0, 0.0];
            }
            return [
                $contract * ($mooe / $total),
                $contract * ($co / $total),
            ];
        };
        $allocationStats = $allocations->mapWithKeys(function ($allocation) use ($effectiveAmounts) {
            $programmedMooe = 0.0;
            $programmedCo = 0.0;
            foreach ($allocation->financialPlans as $plan) {
                [$effectiveMooe, $effectiveCo] = $effectiveAmounts($plan);
                $programmedMooe += $effectiveMooe;
                $programmedCo += $effectiveCo;
            }
            $mooeBudget = (float) $allocation->mooe_budget;
            $coBudget = (float) $allocation->co_budget;
            $budget = $mooeBudget + $coBudget;
            $programmed = $programmedMooe + $programmedCo;
            $remainingMooe = $mooeBudget - $programmedMooe;
            $remainingCo = $coBudget - $programmedCo;
            $remaining = $budget - $programmed;
            $utilization = $budget > 0 ? ($programmed / $budget) * 100 : 0;
            return [
                $allocation->id => [
                    'mooe_budget' => $mooeBudget,
                    'co_budget' => $coBudget,
                    'budget' => $budget,
                    'programmed_mooe' => $programmedMooe,
                    'programmed_co' => $programmedCo,
                    'programmed' => $programmed,
                    'remaining_mooe' => $remainingMooe,
                    'remaining_co' => $remainingCo,
                    'remaining' => $remaining,
                    'utilization' => $utilization,
                    'financial_plan_count' => $allocation->financialPlans->count(),
                ],
            ];
        });
        $allocationCount = $allocations->count();
        $expenseCount = $allocations->sum(fn ($allocation) => $allocation->expenses->count());
        $totalMooeBudget = $allocationStats->sum('mooe_budget');
        $totalCoBudget = $allocationStats->sum('co_budget');
        $totalBudget = $allocationStats->sum('budget');
        $totalProgrammedMooe = $allocationStats->sum('programmed_mooe');
        $totalProgrammedCo = $allocationStats->sum('programmed_co');
        $totalProgrammed = $allocationStats->sum('programmed');
        $totalRemaining = $totalBudget - $totalProgrammed;
        $overallUtilization = $totalBudget > 0 ? ($totalProgrammed / $totalBudget) * 100 : 0;
    @endphp
    <section class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h4 class="mb-1 text-lg font-bold text-slate-800">Allocation Management</h4>
                <p class="mb-0 text-sm text-slate-500">Manage Program / Project budgets and monitor Financial Plan utilization.</p>
            </div>
            <a href="{{ route('allocations.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                <i class="fa fa-plus"></i><span>Add Allocation</span>
            </a>
        </div>
    </section>
    @if(session('success'))
        <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <i class="fa fa-check-circle mr-1"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <i class="fa fa-exclamation-circle mr-1"></i>{{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <div class="mb-2 font-semibold"><i class="fa fa-exclamation-circle mr-1"></i>Please correct the following:</div>
            <ul class="mb-0 list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <section class="mb-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Allocations</p>
            <p class="mb-0 text-2xl font-bold text-slate-800">{{ number_format($allocationCount) }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ number_format($expenseCount) }} configured expense lines</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Total Budget</p>
            <p class="mb-0 text-2xl font-bold text-slate-800">₱{{ number_format($totalBudget, 2) }}</p>
            <p class="mt-1 text-xs text-slate-400">MOOE ₱{{ number_format($totalMooeBudget, 2) }} · CO ₱{{ number_format($totalCoBudget, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Programmed</p>
            <p class="mb-0 text-2xl font-bold text-sky-700">₱{{ number_format($totalProgrammed, 2) }}</p>
            <p class="mt-1 text-xs text-slate-400">MOOE ₱{{ number_format($totalProgrammedMooe, 2) }} · CO ₱{{ number_format($totalProgrammedCo, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Remaining</p>
            <p class="mb-0 text-2xl font-bold {{ $totalRemaining < -0.01 ? 'text-red-600' : 'text-emerald-600' }}">₱{{ number_format($totalRemaining, 2) }}</p>
            <p class="mt-1 text-xs text-slate-400">Budget less effective Financial Plan usage</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Utilization</p>
            <p class="mb-0 text-2xl font-bold {{ $overallUtilization > 100.01 ? 'text-red-600' : ($overallUtilization >= 90 ? 'text-amber-600' : 'text-slate-800') }}">{{ number_format($overallUtilization, 1) }}%</p>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
                <div class="h-full rounded-full bg-sky-500" style="width: {{ min(100, max(0, $overallUtilization)) }}%"></div>
            </div>
        </div>
    </section>
    <section class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h5 class="mb-0 text-base font-bold text-slate-800">Allocation Filters</h5>
                    <p class="mb-0 mt-1 text-xs text-slate-500">Filter by Fiscal Year, Level, Program / Project, or Staff / Office.</p>
                </div>
                @if(request()->filled('year_id') || request()->filled('level_id') || request()->filled('program_id') || request()->filled('staff_id'))
                    <span class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700"><i class="fa fa-filter mr-1"></i>Filters Active</span>
                @endif
            </div>
        </div>
        <div class="p-5">
            <form method="GET" action="{{ route('allocations.index') }}">
                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-5">
                    <div>
                        <label for="year_id" class="mb-2 block text-sm font-semibold text-slate-700">Fiscal Year</label>
                        <select name="year_id" id="year_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                            <option value="">All Fiscal Years</option>
                            @foreach($fiscalYears as $fiscalYear)
                                <option value="{{ $fiscalYear->id }}" @selected(request('year_id') == $fiscalYear->id)>{{ $fiscalYear->year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="level_id" class="mb-2 block text-sm font-semibold text-slate-700">Level</label>
                        <select name="level_id" id="level_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                            <option value="">All Levels</option>
                            @foreach($levels as $level)
                                <option value="{{ $level->id }}" @selected(request('level_id') == $level->id)>{{ $level->level_code }} - {{ $level->level_description }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="program_id" class="mb-2 block text-sm font-semibold text-slate-700">Program / Project</label>
                        <select name="program_id" id="program_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                            <option value="">All Programs / Projects</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" @selected(request('program_id') == $program->id)>{{ $program->program }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if(auth()->user()->isAdministrator())
                        <div>
                            <label for="staff_id" class="mb-2 block text-sm font-semibold text-slate-700">Staff / Office</label>
                            <select name="staff_id" id="staff_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                                <option value="">All Staff / Offices</option>
                                @foreach($staffOptions as $staff)
                                    <option value="{{ $staff->id }}" @selected(request('staff_id') == $staff->id)>{{ $staff->abbreviation ?: $staff->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="flex items-end gap-2">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700"><i class="fa fa-filter"></i><span>Apply</span></button>
                        <a href="{{ route('allocations.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"><i class="fa fa-refresh"></i><span>Reset</span></a>
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
                    <p class="mb-0 mt-1 text-xs text-slate-500">Budget, effective Financial Plan utilization, balances, and dependent plans.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $allocationCount }} {{ $allocationCount === 1 ? 'allocation' : 'allocations' }}</span>
            </div>
        </div>
        @if($allocations->count())
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1950px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-left">
                            <th class="w-10 px-5 py-3 text-center font-semibold text-slate-600">#</th>
                            <th class="px-5 py-3 font-semibold text-slate-600">Staff / Office</th>
                            <th class="px-5 py-3 font-semibold text-slate-600">FY</th>
                            <th class="px-5 py-3 font-semibold text-slate-600">Level</th>
                            <th class="px-5 py-3 font-semibold text-slate-600">Program / Project</th>
                            <th class="px-5 py-3 font-semibold text-slate-600">Allocation Budget</th>
                            <th class="px-5 py-3 font-semibold text-slate-600">Programmed</th>
                            <th class="px-5 py-3 font-semibold text-slate-600">Remaining</th>
                            <th class="px-5 py-3 font-semibold text-slate-600">Utilization</th>
                            <th class="px-5 py-3 font-semibold text-slate-600">Financial Plans</th>
                            <th class="px-5 py-3 font-semibold text-slate-600">Expense Setup</th>
                            <th class="px-5 py-3 text-right font-semibold text-slate-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allocations as $allocation)
                            @php
                                $stats = $allocationStats[$allocation->id];
                                $utilization = $stats['utilization'];
                                $nearFull = $utilization >= 90 && $utilization <= 100.01;
                                $over = $utilization > 100.01 || $stats['remaining'] < -0.01;
                            @endphp
                            <tr class="border-b border-slate-100 align-top transition hover:bg-slate-50/70">
                                <td class="px-5 py-4 text-center text-xs font-semibold text-slate-400">{{ $loop->iteration }}</td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-700">{{ $allocation->staff?->name ?? 'Unassigned' }}</div>
                                    @if($allocation->staff?->abbreviation)
                                        <div class="mt-1 text-xs text-slate-500">{{ $allocation->staff->abbreviation }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4"><span class="inline-flex items-center rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-700">{{ $allocation->fiscalYear?->year ?? '—' }}</span></td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-700">{{ $allocation->level?->level_code ?? '—' }}</div>
                                    @if($allocation->level?->level_description)
                                        <div class="mt-1 max-w-[240px] text-xs text-slate-500">{{ $allocation->level->level_description }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4"><div class="max-w-[260px] font-semibold text-slate-700">{{ $allocation->program?->program ?? 'Program not assigned' }}</div></td>
                                <td class="px-5 py-4">
                                    <div class="space-y-1.5">
                                        <div class="flex justify-between gap-4"><span class="text-xs font-semibold text-emerald-700">MOOE</span><span class="font-semibold">₱{{ number_format($stats['mooe_budget'], 2) }}</span></div>
                                        <div class="flex justify-between gap-4"><span class="text-xs font-semibold text-amber-700">CO</span><span class="font-semibold">₱{{ number_format($stats['co_budget'], 2) }}</span></div>
                                        <div class="flex justify-between gap-4 border-t border-slate-200 pt-1.5"><span class="text-xs font-bold">Total</span><span class="font-bold">₱{{ number_format($stats['budget'], 2) }}</span></div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="space-y-1.5">
                                        <div class="flex justify-between gap-4"><span class="text-xs font-semibold text-emerald-700">MOOE</span><span class="font-semibold">₱{{ number_format($stats['programmed_mooe'], 2) }}</span></div>
                                        <div class="flex justify-between gap-4"><span class="text-xs font-semibold text-amber-700">CO</span><span class="font-semibold">₱{{ number_format($stats['programmed_co'], 2) }}</span></div>
                                        <div class="flex justify-between gap-4 border-t border-slate-200 pt-1.5"><span class="text-xs font-bold">Total</span><span class="font-bold text-sky-700">₱{{ number_format($stats['programmed'], 2) }}</span></div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="space-y-1.5">
                                        <div class="flex justify-between gap-4"><span class="text-xs font-semibold text-slate-500">MOOE</span><span class="font-semibold {{ $stats['remaining_mooe'] < -0.01 ? 'text-red-600' : 'text-emerald-600' }}">₱{{ number_format($stats['remaining_mooe'], 2) }}</span></div>
                                        <div class="flex justify-between gap-4"><span class="text-xs font-semibold text-slate-500">CO</span><span class="font-semibold {{ $stats['remaining_co'] < -0.01 ? 'text-red-600' : 'text-emerald-600' }}">₱{{ number_format($stats['remaining_co'], 2) }}</span></div>
                                        <div class="flex justify-between gap-4 border-t border-slate-200 pt-1.5"><span class="text-xs font-bold">Total</span><span class="font-bold {{ $stats['remaining'] < -0.01 ? 'text-red-600' : 'text-emerald-600' }}">₱{{ number_format($stats['remaining'], 2) }}</span></div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="min-w-[150px]">
                                        <div class="mb-2 flex items-center justify-between gap-3">
                                            <span class="font-bold {{ $over ? 'text-red-600' : ($nearFull ? 'text-amber-600' : 'text-slate-700') }}">{{ number_format($utilization, 1) }}%</span>
                                            @if($over)
                                                <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold uppercase text-red-700">Over</span>
                                            @elseif($nearFull)
                                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase text-amber-700">Near Full</span>
                                            @endif
                                        </div>
                                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full {{ $over ? 'bg-red-500' : ($nearFull ? 'bg-amber-500' : 'bg-sky-500') }}" style="width: {{ min(100, max(0, $utilization)) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-center">
                                        <div class="text-xl font-bold text-slate-800">{{ number_format($stats['financial_plan_count']) }}</div>
                                        <div class="text-xs text-slate-500">{{ $stats['financial_plan_count'] === 1 ? 'Financial Plan row' : 'Financial Plan rows' }}</div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="space-y-1">
                                        @forelse($allocation->expenses as $expense)
                                            <div class="flex max-w-[280px] justify-between gap-3 text-xs">
                                                <span class="truncate text-slate-600">{{ $expense->expenseType?->expense_description ?? 'Expense' }}</span>
                                                <span class="whitespace-nowrap font-semibold text-slate-700">₱{{ number_format((float) $expense->cost, 2) }}</span>
                                            </div>
                                        @empty
                                            <span class="text-xs text-slate-400">No expense setup</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('allocations.edit', $allocation) }}" class="inline-flex items-center justify-center gap-1 rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-700 transition hover:bg-sky-100"><i class="fa fa-pencil"></i>Edit</a>
                                        <form action="{{ route('allocations.destroy', $allocation) }}" method="POST" class="delete-allocation-form inline" data-financial-plan-count="{{ $stats['financial_plan_count'] }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="delete-allocation-button inline-flex items-center justify-center gap-1 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-100" @disabled($stats['financial_plan_count'] > 0)>
                                                <i class="fa fa-trash"></i>Delete
                                            </button>
                                        </form>
                                    </div>
                                    @if($stats['financial_plan_count'] > 0)
                                        <div class="mt-2 text-[10px] font-semibold text-slate-400">Delete locked: allocation is in use.</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-5 py-14 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400"><i class="fa fa-folder-open-o text-2xl"></i></div>
                @if(request()->filled('year_id') || request()->filled('level_id') || request()->filled('program_id') || request()->filled('staff_id'))
                    <h6 class="mb-1 text-sm font-bold text-slate-700">No matching allocations found</h6>
                    <p class="mb-4 text-xs text-slate-500">No allocations match the selected filters.</p>
                    <a href="{{ route('allocations.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"><i class="fa fa-refresh"></i>Clear Filters</a>
                @else
                    <h6 class="mb-1 text-sm font-bold text-slate-700">No allocations configured yet</h6>
                    <p class="mb-4 text-xs text-slate-500">Create your first allocation to begin managing Program / Project budgets.</p>
                    <a href="{{ route('allocations.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700"><i class="fa fa-plus"></i>Add Allocation</a>
                @endif
            </div>
        @endif
    </section>
    <div class="mt-6">@include('layouts.footers.auth.footer')</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-allocation-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const financialPlanCount = Number(form.dataset.financialPlanCount || 0);
            if (financialPlanCount > 0) {
                event.preventDefault();
                return;
            }
            if (!confirm('Delete this allocation?\n\nAll expense entries under this allocation will also be deleted.\n\nThis action cannot be undone.')) {
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
