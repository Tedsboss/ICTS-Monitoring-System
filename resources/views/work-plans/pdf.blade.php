<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Work Plan FY {{ $plan->fiscal_year }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 8px;
            color: #111;
        }
        .document-header {
            margin: 0 0 10px 0;
        }
        .document-title {
            margin-bottom: 5px;
            font-size: 11px;
            font-weight: bold;
        }
        .office-line {
            font-size: 8px;
            line-height: 1.35;
        }
        .office-line span {
            margin-left: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th,
        td {
            border: 1px solid #222;
            padding: 4px;
            vertical-align: top;
            overflow-wrap: anywhere;
        }
        th {
            background: #dbe8f2;
            text-align: center;
            font-weight: bold;
        }
        .classification-column {
            width: 18%;
        }
        .activity-column {
            width: 18%;
        }
        .month-column {
            width: 5.33%;
        }
        .section-header td {
            background: #cbd5e1;
            padding: 5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .subheader td {
            background: #f1f5f9;
            padding: 5px 10px;
            font-weight: bold;
        }
        .classification-cell,
        .activity-cell {
            font-size: 7.5px;
            line-height: 1.3;
            vertical-align: top;
        }
        .target-cell {
            font-size: 7px;
            line-height: 1.25;
        }   
        .target-range-cell {
            vertical-align: top;
            text-align: left;
        }
        .target-full-range {
            vertical-align: middle;
            text-align: center;
        }
        .target-single-month {
            vertical-align: middle;
            text-align: center;
        }
        .target-empty-cell {
            padding: 0;
        }
.instructions {
            margin: 8px 4px 0;
            font-size: 6.8px;
            line-height: 1.35;
        }
        .instructions-title {
            margin-bottom: 2px;
            font-weight: bold;
        }
        .instruction-indent {
            padding-left: 14px;
        }
        .signatories {
            margin-top: 20px;
            border: 0;
        }
        .signatories td {
            width: 25%;
            border: 0;
            padding: 0 12px;
            text-align: center;
            vertical-align: bottom;
        }
        .signatory-label {
            margin-bottom: 20px;
            text-align: left;
            font-size: 8px;
            font-weight: bold;
        }
        .signatory-name {
            min-height: 14px;
            border-bottom: 1px solid #333;
            padding-bottom: 3px;
            font-weight: bold;
        }
        .signatory-position {
            padding-top: 3px;
            font-size: 7px;
        }
    </style>
</head>
<body>
    <div class="document-header">
        <div class="document-title">FY {{ $plan->fiscal_year }} WORK PLAN</div>
        <div class="office-line">
            <strong>Staff/Office/Service/Regional Office/Operating Unit:</strong>
            <span>{{ $plan->staff?->name ?? '—' }}</span>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th rowspan="2" class="classification-column">
                    DEPDev Program of Expenditure Classification / Budget Structure
                    <br>
                    <span>(a)</span>
                </th>
                <th rowspan="2" class="activity-column">
                    Specific Activity/ies
                    <br>
                    <span>(b)</span>
                </th>
                <th colspan="12">Target Output/s<br><span>(c)</span></th>
            </tr>
            <tr>
                @foreach($months as $monthNumber => $monthName)
                    <th class="month-column">{{ strtoupper(substr($monthName, 0, 1)) }}</th>
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
                    $lanes = [];
                    foreach ($planItem->targets->sortBy('sort_order')->values() as $target) {
                        $targetMonths = $target->relationLoaded('months') && $target->months->isNotEmpty()
                            ? $target->months->pluck('month')->map(fn ($month) => (int) $month)->unique()->sort()->values()
                            : collect([(int) $target->month])->filter(fn ($month) => $month >= 1 && $month <= 12);
                        $targetMonthNumbers = $targetMonths->all();
                        $placed = false;
                        foreach ($lanes as &$lane) {
                            if (empty(array_intersect($lane['usedMonths'], $targetMonthNumbers))) {
                                $lane['targets'][] = ['target' => $target, 'months' => $targetMonthNumbers];
                                $lane['usedMonths'] = array_values(array_unique(array_merge($lane['usedMonths'], $targetMonthNumbers)));
                                sort($lane['usedMonths']);
                                $placed = true;
                                break;
                            }
                        }
                        unset($lane);
                        if (!$placed) {
                            $lanes[] = [
                                'targets' => [['target' => $target, 'months' => $targetMonthNumbers]],
                                'usedMonths' => $targetMonthNumbers,
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
                    <tr class="{{ $displayRow['type'] === 'header' ? 'section-header' : 'subheader' }}">
                        <td colspan="14">{{ $displayItem->title ?: '—' }}</td>
                    </tr>
                @else
                    @php
                        $classificationRowspan = collect($displayRow['items'])->sum('rowspan');
                        $classificationPrinted = false;
                    @endphp
                    @foreach($displayRow['items'] as $activityData)
                        @foreach($activityData['lanes'] as $lane)
                            <tr>
                                @if(!$classificationPrinted)
                                    <td class="classification-cell" rowspan="{{ $classificationRowspan }}">
                                    @if($displayRow['program_classification'])
                                        @if($displayRow['prexc_code'])
                                            <strong>{{ $displayRow['prexc_code'] }}</strong><br>
                                        @endif
                                        {{ $displayRow['program_classification'] }}
                                    @elseif($displayRow['classification'])
                                        @if($displayRow['classification']->code)
                                            <strong>{{ $displayRow['classification']->code }}</strong><br>
                                        @endif
                                        {{ $displayRow['classification']->name }}
                                    @else
                                        —
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
                                    <td colspan="{{ $segment['span'] }}" class="target-cell {{ !$segment['target'] ? 'target-empty-cell' : ($segment['span'] >= 9 ? 'target-full-range' : ($segment['span'] === 1 ? 'target-single-month' : 'target-range-cell')) }}">
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
                    <td colspan="14" style="text-align:center;padding:18px;">No Work Plan rows have been added.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @php
        $signatory = $plan->signatory;
        $hasSignatories = $signatory && (
            $signatory->prepared_by ||
            $signatory->reviewed_by ||
            $signatory->recommended_by ||
            $signatory->approved_by
        );
    @endphp
    @if($hasSignatories)
        <table class="signatories">
            <tr>
                <td>
                    <div class="signatory-label">Prepared by:</div>
                    <div class="signatory-name">{{ $signatory->prepared_by ?: '—' }}</div>
                    <div class="signatory-position">{{ $signatory->prepared_by_position ?: '—' }}</div>
                </td>
                <td>
                    <div class="signatory-label">Checked and Reviewed by:</div>
                    <div class="signatory-name">{{ $signatory->reviewed_by ?: '—' }}</div>
                    <div class="signatory-position">{{ $signatory->reviewed_by_position ?: '—' }}</div>
                </td>
                <td>
                    <div class="signatory-label">Recommended by:</div>
                    <div class="signatory-name">{{ $signatory->recommended_by ?: '—' }}</div>
                    <div class="signatory-position">{{ $signatory->recommended_by_position ?: '—' }}</div>
                </td>
                <td>
                    <div class="signatory-label">Approved by:</div>
                    <div class="signatory-name">{{ $signatory->approved_by ?: '—' }}</div>
                    <div class="signatory-position">{{ $signatory->approved_by_position ?: '—' }}</div>
                </td>
            </tr>
        </table>
    @endif
</body>
</html>
