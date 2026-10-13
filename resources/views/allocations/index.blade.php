@extends('layouts.app')

@section('content')
@php
    $allocationDark = session('user_settings.class_theme', '') === 'dark';
    $effectiveAmounts = function ($plan) {
        $mooe = (float) $plan->mooe;
        $co = (float) $plan->capital_outlay;
        if ($plan->contract_amount === null || $plan->contract_amount === '') {
            return [$mooe, $co];
        }
        $total = $mooe + $co;
        $contract = (float) $plan->contract_amount;
        return $total > 0 ? [$contract * ($mooe / $total), $contract * ($co / $total)] : [0.0, 0.0];
    };
    $allocationStats = $allocations->mapWithKeys(function ($allocation) use ($effectiveAmounts) {
        $mooe = 0.0; $co = 0.0;
        foreach ($allocation->financialPlans as $plan) {
            [$m, $c] = $effectiveAmounts($plan);
            $mooe += $m; $co += $c;
        }
        $mb = (float) $allocation->mooe_budget;
        $cb = (float) $allocation->co_budget;
        $budget = $mb + $cb;
        return [$allocation->id => [
            'mooe_budget' => $mb, 'co_budget' => $cb, 'budget' => $budget,
            'programmed_mooe' => $mooe, 'programmed_co' => $co, 'programmed' => $mooe + $co,
            'remaining_mooe' => $mb - $mooe, 'remaining_co' => $cb - $co,
            'remaining' => $budget - $mooe - $co,
            'utilization' => $budget > 0 ? (($mooe + $co) / $budget) * 100 : 0,
            'financial_plan_count' => $allocation->financialPlans->count(),
        ]];
    });
    $staffLabels = $staffOptions->mapWithKeys(fn ($staff) => [$staff->id => $staff->abbreviation ?: $staff->name]);
    $allocationCount = $allocations->count();
    $expenseCount = $allocations->sum(fn ($allocation) => $allocation->expenses->count());
    $totalBudget = $allocationStats->sum('budget');
    $totalProgrammed = $allocationStats->sum('programmed');
    $totalRemaining = $totalBudget - $totalProgrammed;
    $overallUtilization = $totalBudget > 0 ? $totalProgrammed / $totalBudget * 100 : 0;
    $filtersActive = request()->filled('year_id') || request()->filled('level_id') || request()->filled('program_id') || (auth()->user()->isAdministrator() && request()->filled('staff_id'));
@endphp
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false"><div class="container-fluid py-1 px-3">@include('layouts.navbars.auth.topnav', ['title' => 'Allocation Management']) @include('layouts.navbars.auth.topnav-withdatetime')</div></nav>
<div id="direk-allocations" data-theme="{{ $allocationDark ? 'dark' : 'light' }}">
    <header class="allocation-heading"><div><span class="allocation-eyebrow">Financial workspace / Allocations</span><h2>Funding with a clear view.</h2><p>Manage program budgets and see how Financial Plans use each allocation.</p></div>@if ($canAddAllocations ?? false)<a href="{{ route('allocations.create') }}" class="allocation-button allocation-primary"><i class="fa fa-plus" aria-hidden="true"></i> New allocation</a>@endif</header>
    <div id="pageMessage"></div>
    @if (session('success') || session('succes'))<div class="allocation-notice allocation-success" role="status">{{ session('success') ?: session('succes') }}</div>@endif
    @if (session('error'))<div class="allocation-notice allocation-danger" role="alert">{{ session('error') }}</div>@endif
    @if ($errors->any())<div class="allocation-notice allocation-danger" role="alert"><strong>Please correct the following:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <section class="allocation-metrics" aria-label="Summary of the allocations matching the selected filters">
        <article class="allocation-panel"><span class="allocation-summary-label">Allocations</span><strong>{{ number_format($allocationCount) }}</strong><p>{{ number_format($expenseCount) }} expense lines · {{ $filtersActive ? 'filtered records' : 'accessible records' }}</p></article>
        <article class="allocation-panel"><span class="allocation-summary-label">Overall budget</span><strong>₱{{ number_format($totalBudget, 2) }}</strong><p>MOOE ₱{{ number_format($allocationStats->sum('mooe_budget'), 2) }}<br>CO ₱{{ number_format($allocationStats->sum('co_budget'), 2) }}</p></article>
        <article class="allocation-panel"><span class="allocation-summary-label">Programmed</span><strong class="allocation-accent">₱{{ number_format($totalProgrammed, 2) }}</strong><p>{{ number_format($overallUtilization, 1) }}% of overall budget<br>Effective Financial Plan amounts</p></article>
        <article class="allocation-panel"><span class="allocation-summary-label">Remaining</span><strong class="{{ $totalRemaining < -0.005 ? 'allocation-negative' : 'allocation-positive' }}">₱{{ number_format($totalRemaining, 2) }}</strong><p>Overall budget less programmed usage<br>Category balances appear below</p></article>
    </section>
    <section class="allocation-panel">
        <div class="allocation-panel-heading"><div><h3>Find an allocation</h3><p>Summary amounts reflect the selected filters.</p></div>@if ($filtersActive)<span class="allocation-badge">Filters active</span>@endif</div>
        <form method="GET" action="{{ route('allocations.index') }}" class="allocation-panel-body allocation-filter-grid">
            <label for="year_id">Fiscal year<select name="year_id" id="year_id"><option value="">All fiscal years</option>@foreach ($fiscalYears as $year)<option value="{{ $year->id }}" @selected(request('year_id') == $year->id)>{{ $year->year }}{{ auth()->user()->isAdministrator() ? ' · ' . ($staffLabels[$year->staff_id] ?? 'Unassigned office') : '' }}</option>@endforeach</select></label>
            <label for="level_id">Level<select name="level_id" id="level_id"><option value="">All levels</option>@foreach ($levels as $level)<option value="{{ $level->id }}" @selected(request('level_id') == $level->id)>{{ $level->level_code }}{{ $level->level_description ? ' — ' . $level->level_description : '' }}{{ auth()->user()->isAdministrator() ? ' · ' . ($staffLabels[$level->staff_id] ?? 'Unassigned office') : '' }}</option>@endforeach</select></label>
            <label for="program_id">Program / Project<select name="program_id" id="program_id"><option value="">All programs</option>@foreach ($programs as $program)<option value="{{ $program->id }}" @selected(request('program_id') == $program->id)>{{ $program->program }}</option>@endforeach</select></label>
            @if (auth()->user()->isAdministrator())<label for="staff_id">Staff / Office<select name="staff_id" id="staff_id"><option value="">All offices</option>@foreach ($staffOptions as $staff)<option value="{{ $staff->id }}" @selected(request('staff_id') == $staff->id)>{{ $staff->abbreviation ?: $staff->name }}</option>@endforeach</select></label>@endif
            <div class="allocation-filter-actions"><button type="submit" class="allocation-button allocation-primary"><i class="fa fa-filter" aria-hidden="true"></i> Apply</button><a href="{{ route('allocations.index') }}" class="allocation-button">Reset</a></div>
        </form>
    </section>
    <section class="allocation-panel">
        <div class="allocation-panel-heading"><div><h3>Allocation directory</h3><p>Expand a program’s expense breakdown to inspect its planning limits.</p></div><span class="allocation-badge">{{ $allocationCount }} {{ $allocationCount === 1 ? 'allocation' : 'allocations' }}</span></div>
        @if ($allocationCount)
            <div class="allocation-table-wrap"><table class="allocation-table allocation-directory"><caption class="visually-hidden">Allocation budgets, Financial Plan usage and remaining funds</caption><thead><tr><th scope="col">Office / Context</th><th scope="col">Program / Project</th><th scope="col">Budget</th><th scope="col">Programmed</th><th scope="col">Remaining</th><th scope="col">Usage</th><th scope="col">Actions</th></tr></thead><tbody>
            @foreach ($allocations as $allocation)
                @php
                    $stats = $allocationStats[$allocation->id];
                    $over = $stats['remaining_mooe'] < -0.005 || $stats['remaining_co'] < -0.005;
                    $nearFull = !$over && $stats['utilization'] >= 90;
                @endphp
                <tr>
                    <td><strong>{{ $allocation->staff?->abbreviation ?: ($allocation->staff?->name ?? 'Unassigned') }}</strong><small>{{ $allocation->staff?->name }}</small><div class="allocation-context-tags"><span>{{ $allocation->fiscalYear?->year ?? '—' }}</span><span>{{ $allocation->level?->level_code ?? '—' }}</span></div>@if ($allocation->level?->level_description)<small>{{ $allocation->level->level_description }}</small>@endif</td>
                    <td><strong>{{ $allocation->program?->program ?? 'Program not assigned' }}</strong><details class="allocation-expense-details"><summary>{{ $allocation->expenses->count() }} expense lines</summary><div>@forelse ($allocation->expenses as $expense)<p><span>{{ $expense->expenseType?->type }} · {{ $expense->expenseType?->expense_description ?? 'Expense unavailable' }}</span><b>₱{{ number_format((float) $expense->cost, 2) }}</b></p>@empty<p>No expense setup.</p>@endforelse</div></details></td>
                    @foreach (['budget', 'programmed', 'remaining'] as $category)
                        <td class="allocation-money-cell"><strong class="{{ $category === 'remaining' ? ($stats['remaining'] < -0.005 ? 'allocation-negative' : 'allocation-positive') : ($category === 'programmed' ? 'allocation-accent' : '') }}">₱{{ number_format($stats[$category], 2) }}</strong>@foreach (['mooe' => 'MOOE', 'co' => 'CO'] as $key => $label)@php $value = $category === 'budget' ? $stats[$key . '_budget'] : $stats[$category . '_' . $key]; @endphp<small class="{{ $category === 'remaining' && $value < -0.005 ? 'allocation-negative' : '' }}">{{ $label }} ₱{{ number_format($value, 2) }}</small>@endforeach</td>
                    @endforeach
                    <td><div class="allocation-utilization"><strong class="{{ $over ? 'allocation-negative' : '' }}">{{ number_format($stats['utilization'], 1) }}%</strong><span class="allocation-badge {{ $over ? 'allocation-badge-danger' : '' }}">{{ $over ? 'Over budget' : ($nearFull ? 'Near full' : 'Within budget') }}</span><div class="allocation-progress" aria-hidden="true"><span style="width:{{ min(100, max(0, $stats['utilization'])) }}%"></span></div><small>{{ number_format($stats['financial_plan_count']) }} Financial Plan {{ $stats['financial_plan_count'] === 1 ? 'row' : 'rows' }}</small></div></td>
                    <td><div class="allocation-row-actions">@if ($canEditAllocations ?? false)<a href="{{ route('allocations.edit', $allocation) }}" class="allocation-button">Edit</a>@endif @if ($canDeleteAllocations ?? false) @if ($stats['financial_plan_count'] > 0)<span class="allocation-locked" title="Financial Plan rows use this allocation."><i class="fa fa-lock" aria-hidden="true"></i> In use</span>@else<form action="{{ route('allocations.destroy', $allocation) }}" method="POST" class="delete-allocation-form" data-financial-plan-count="{{ $stats['financial_plan_count'] }}">@csrf @method('DELETE')<button type="submit" class="allocation-button allocation-remove delete-allocation-button">Delete</button></form>@endif @endif @if (!($canEditAllocations ?? false) && !($canDeleteAllocations ?? false))<span class="allocation-locked">—</span>@endif</div></td>
                </tr>
            @endforeach
            </tbody></table></div>
        @else
            <div class="allocation-empty"><i class="fa fa-folder-open-o" aria-hidden="true"></i><h4>{{ $filtersActive ? 'No matching allocations' : 'Build your funding foundation' }}</h4><p>{{ $filtersActive ? 'Try a different fiscal year, level, program, or office.' : 'Create an allocation to set program budgets and expense planning limits.' }}</p>@if ($filtersActive)<a href="{{ route('allocations.index') }}" class="allocation-button">Clear filters</a>@elseif ($canAddAllocations ?? false)<a href="{{ route('allocations.create') }}" class="allocation-button allocation-primary">New allocation</a>@endif</div>
        @endif
        <p class="allocation-directory-note">Programmed usage uses MOOE + CO when no contract exists. With a contract, its amount is split proportionally across MOOE and CO.</p>
    </section>
</div>
@include('layouts.footers.auth.footer')
@include('allocations.styles')
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-allocation-form').forEach(function (form) {
        let deleting = false;
        const button = form.querySelector('.delete-allocation-button');
        form.addEventListener('submit', function (event) {
            if (deleting || Number(form.dataset.financialPlanCount || 0) > 0) { event.preventDefault(); return; }
            if (!confirm('Delete this allocation and its expense entries? This action cannot be undone.')) { event.preventDefault(); return; }
            deleting = true;
            button.disabled = true;
            button.textContent = 'Deleting…';
        });
        window.addEventListener('pageshow', function () { deleting = false; button.disabled = false; button.textContent = 'Delete'; });
    });
});
</script>
@endpush
