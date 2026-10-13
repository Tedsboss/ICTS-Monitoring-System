<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>FY {{ $workPlan->fiscal_year }} Monthly Disbursement Program</title>
    <style>
        @page { margin:20px 22px; }
        body { margin:0; font-family:DejaVu Sans,sans-serif; font-size:9px; color:#111; }
        h1 { margin:0 0 5px; font-size:13px; }
        h2 { margin:12px 0 6px; font-size:10px; }
        p { margin:3px 0; }
        .heading { border:1px solid #111; padding:8px; }
        .mdp-table { width:100%; border-collapse:collapse; table-layout:fixed; font-size:6.8px; }
        .mdp-table th, .mdp-table td { border:1px solid #111; padding:5px 2px; vertical-align:middle; }
        .mdp-table thead th { text-align:center; background:#eee; }
        .mdp-table thead { display:table-header-group; }
        .mdp-table tr { page-break-inside:avoid; }
        .mdp-particulars { width:18%; }
        .mdp-table thead th[colspan="12"] { width:72%; }
        .mdp-annual { width:10%; }
        .mdp-activity { white-space:normal; overflow-wrap:break-word; word-wrap:break-word; }
        .mdp-table td.mdp-number { text-align:right; white-space:nowrap; overflow-wrap:normal; word-wrap:normal; word-break:normal; }
        .mdp-summary, .mdp-bold { font-weight:bold; }
        .mdp-summary th { text-align:left; }
        .mdp-grand-total { background:#eee; }
        .note { font-size:7px; margin-top:7px; }
        .signatures { width:100%; border-collapse:separate; border-spacing:10px; margin-top:25px; page-break-inside:avoid; }
        .signatures td { width:25%; vertical-align:top; }
        .name { padding-top:30px; padding-bottom:4px; border-bottom:1px solid #111; font-weight:bold; }
    </style>
</head>
<body>
@php $signatory = $workPlan->signatory; @endphp
<div class="heading">
    <h1>FY {{ $workPlan->fiscal_year }} MONTHLY DISBURSEMENT PROGRAM</h1>
    <p>Name of Office/Staff: {{ $workPlan->office_name }}</p>
    <p>Source Work Plan status: {{ ucfirst($workPlan->isFinalized() ? 'finalized' : $workPlan->status) }}. Generated {{ now()->format('F j, Y') }}. Amounts in PHP.</p>
</div>
@foreach ($groups as $group)
    <h2>{{ $loop->index < 26 ? chr(97 + $loop->index) : $loop->iteration }}.) {{ $group['name'] }}</h2>
    @include('mdp.table', ['group' => $group, 'mdpPdf' => true])
@endforeach
@if (count($groups) > 1)
    <h2>Consolidated totals</h2>
    @include('mdp.table', ['group' => ['rows' => [], 'summary' => $summary], 'mdpPdf' => true])
@endif
<p class="note">Monthly cash amounts are taken from the linked Financial Plan schedules. Mixed MOOE/CO activities are split proportionally to their budgets, with cent rounding carried between months.</p>
@if (!empty($sourceWarnings))<div class="note"><strong>Source notes:</strong>@foreach ($sourceWarnings as $warning)<p>{{ $warning }}</p>@endforeach</div>@endif
<table class="signatures"><tr>
@foreach (['prepared' => 'Prepared by', 'reviewed' => 'Reviewed by', 'recommended' => 'Recommended by', 'approved' => 'Approved by'] as $key => $label)
    <td><p>{{ $label }}:</p><p class="name">{{ $signatory?->{$key . '_by'} ?: ' ' }}</p><p>{{ $signatory?->{$key . '_by_position'} ?: ($key === 'recommended' ? 'Director/Head of Staff or Unit' : ($key === 'approved' ? 'Supervising Undersecretary' : '')) }}</p></td>
@endforeach
</tr></table>
<p class="note">Signatory names are carried from the source Work Plan. Signature lines are provided for signing this report.</p>
</body>
</html>
