@extends('layouts.app')
@section('content')
@php
    $mdpDark = session('user_settings.class_theme', '') === 'dark';
    $signatory = $workPlan->signatory;
@endphp
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="false"><div class="container-fluid py-2 px-3">@include('layouts.navbars.auth.topnav', ['title' => 'Monthly Disbursement Program']) @include('layouts.navbars.auth.topnav-withdatetime')</div></nav>
<div id="mdp-report" class="mdp-modern" data-theme="{{ $mdpDark ? 'dark' : 'light' }}">
    <div class="mdp-toolbar"><a href="{{ route('mdp.index', ['fiscal_year' => $workPlan->fiscal_year]) }}" class="mdp-button"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back to MDP</a><div class="mdp-actions"><a href="{{ route('work-plans.index', ['fiscal_year' => $workPlan->fiscal_year, 'staff_id' => $workPlan->staff_id, 'office_name' => $workPlan->office_name]) }}" class="mdp-button">View Work Plan</a>@if ($complete)<a href="{{ route('mdp.pdf', $workPlan) }}" target="_blank" rel="noopener" data-no-loader class="mdp-button mdp-primary"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Download PDF</a>@else<button type="button" disabled class="mdp-button">Resolve sources to download PDF</button>@endif</div></div>
    <header class="mdp-heading"><div><span class="mdp-eyebrow">Financial workspace / Fiscal Year {{ $workPlan->fiscal_year }}</span><h2>Monthly Disbursement Program</h2><p>{{ $workPlan->office_name }}</p></div><span class="mdp-badge {{ $complete ? '' : 'mdp-warning-badge' }}">{{ $complete ? 'Sources complete' : 'Incomplete sources' }}</span></header>
    <section class="mdp-panel mdp-source-context"><div><span>Staff / Office</span><strong>{{ optional($workPlan->staff)->name ?: 'Unassigned staff' }}</strong></div><div><span>Source Work Plan</span><strong>{{ ucfirst($workPlan->isFinalized() ? 'finalized' : $workPlan->status) }}</strong></div><div><span>Activities calculated</span><strong>{{ $calculatedCount }} of {{ $sourceCount }}</strong></div></section>
    @if (!empty($sourceErrors))<div role="alert" class="mdp-notice mdp-danger"><strong>The report is incomplete. Displayed figures exclude invalid sources.</strong><ul>@foreach ($sourceErrors as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if (!empty($sourceWarnings))<details class="mdp-notice mdp-warning"><summary>{{ count($sourceWarnings) }} source note(s)</summary><ul>@foreach ($sourceWarnings as $warning)<li>{{ $warning }}</li>@endforeach</ul></details>@endif
    <section class="mdp-metrics" aria-label="Annual disbursement summary">@foreach (['mooe' => 'MOOE', 'co' => 'Capital Outlay', 'total' => 'Annual total'] as $category => $label)<article><span>{{ $label }}</span><strong class="{{ $category === 'total' ? 'mdp-accent' : '' }}">PHP {{ $money($summary['annual_' . $category]) }}</strong><p>{{ $complete ? 'From linked monthly schedules' : 'Calculated valid sources only' }}</p></article>@endforeach</section>
    @foreach ($groups as $group)<section class="mdp-panel"><div class="mdp-panel-heading"><div><span class="mdp-eyebrow">Program {{ $loop->iteration }}</span><h3>{{ $group['name'] }}</h3></div><span class="mdp-badge">Amounts in PHP</span></div><div class="mdp-table-scroll">@include('mdp.table', ['group' => $group])</div></section>@endforeach
    @if (count($groups) > 1)<section class="mdp-panel"><div class="mdp-panel-heading"><h3>Consolidated totals</h3></div><div class="mdp-table-scroll">@include('mdp.table', ['group' => ['rows' => [], 'summary' => $summary]])</div></section>@endif
    <p class="mdp-footnote">Monthly schedules come from the linked Financial Plans. Mixed MOOE/CO amounts are split in proportion to their budgets.</p>
    <section class="mdp-panel mdp-signatories">@foreach (['prepared' => 'Prepared by', 'reviewed' => 'Reviewed by', 'recommended' => 'Recommended by', 'approved' => 'Approved by'] as $key => $label)<div><span>{{ $label }}</span><strong>{{ $signatory?->{$key . '_by'} ?: '—' }}</strong><p>{{ $signatory?->{$key . '_by_position'} ?: '' }}</p></div>@endforeach</section>
    <p class="mdp-footnote">Signatories are carried from the source Work Plan. Generated {{ now()->format('M j, Y g:i A T') }}.</p>
</div>
@include('layouts.footers.auth.footer')
@include('mdp.modern-styles')
@endsection
