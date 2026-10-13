@extends('layouts.app')
@section('content')
@php $mdpDark = session('user_settings.class_theme', '') === 'dark'; @endphp
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false"><div class="container-fluid py-2 px-3">@include('layouts.navbars.auth.topnav', ['title' => 'Monthly Disbursement Program']) @include('layouts.navbars.auth.topnav-withdatetime')</div></nav>
<div id="mdp-directory" class="mdp-modern" data-theme="{{ $mdpDark ? 'dark' : 'light' }}">
    <header class="mdp-heading"><div><span class="mdp-eyebrow">Financial workspace / MDP</span><h2>A clearer view of cash requirements.</h2><p>Generate monthly disbursement schedules from your Work Plans and linked Financial Plans.</p></div><span class="mdp-badge">Fiscal Year {{ $fiscalYear }}</span></header>
    @if ($errors->any())<div class="mdp-notice mdp-danger" role="alert">{{ $errors->first() }}</div>@endif
    <section class="mdp-metrics" aria-label="Directory summary"><article><span>Matching Work Plans</span><strong>{{ number_format($plans->total()) }}</strong><p>For the selected fiscal year</p></article><article><span>On this page</span><strong>{{ number_format($plans->count()) }}</strong><p>Plans available to review</p></article><article><span>Planning period</span><strong>{{ $fiscalYear }}</strong><p>January through December</p></article></section>
    <section class="mdp-panel">
        <div class="mdp-panel-heading"><div><h3>Disbursement directory</h3><p>Open a Work Plan to review its monthly cash requirements.</p></div><form method="GET" action="{{ route('mdp.index') }}" class="mdp-filter"><label for="mdp-year">Fiscal year<select id="mdp-year" name="fiscal_year">@if (!$fiscalYears->contains($fiscalYear))<option value="{{ $fiscalYear }}" selected>{{ $fiscalYear }}</option>@endif @foreach ($fiscalYears as $year)<option value="{{ $year }}" @selected((int) $year === (int) $fiscalYear)>{{ $year }}</option>@endforeach</select></label><button type="submit" class="mdp-button mdp-primary">Apply</button></form></div>
        <div class="mdp-table-scroll"><table class="mdp-directory-table"><caption class="visually-hidden">Work Plans available for Monthly Disbursement Program reports</caption><thead><tr>@foreach (['Staff / Office', 'Office name', 'Work Plan status', 'Action'] as $label)<th scope="col">{{ $label }}</th>@endforeach</tr></thead><tbody>
        @forelse ($plans as $plan)
            <tr><td><strong>{{ optional($plan->staff)->name ?: 'Unassigned staff' }}</strong></td><td>{{ $plan->office_name }}</td><td><span class="mdp-badge">{{ ucfirst($plan->isFinalized() ? 'finalized' : $plan->status) }}</span></td><td>@can('view', $plan)<a href="{{ route('mdp.show', $plan) }}" class="mdp-button mdp-primary">View MDP <i class="fa fa-arrow-right" aria-hidden="true"></i></a>@else<span class="mdp-muted">Unavailable</span>@endcan</td></tr>
        @empty
            <tr><td colspan="4" class="mdp-empty"><i class="fa fa-calendar" aria-hidden="true"></i><h4>No matching Work Plans</h4><p>No saved Work Plans with matching Financial Plan sources are available for this fiscal year.</p></td></tr>
        @endforelse
        </tbody></table></div>
        @if ($plans->hasPages())<div class="mdp-pagination">{{ $plans->links() }}</div>@endif
    </section>
</div>
@include('layouts.footers.auth.footer')
@include('mdp.modern-styles')
@endsection
