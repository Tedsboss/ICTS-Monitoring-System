<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0.28in 0.24in 0.26in; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 6.6px;
            line-height: 1.15;
            color: #111;
        }
        .report-title {
            margin: 0;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .15px;
        }
        .report-subtitle {
            margin: 1px 0 3px;
            font-size: 7.4px;
            font-style: italic;
            color: #444;
        }
        .report-meta {
            margin: 0 0 5px;
            font-size: 7.4px;
        }
        .report-meta strong {
            display: inline-block;
            min-width: 66px;
        }
        .report-meta .office-name {
            font-weight: 700;
            text-decoration: underline;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        th, td {
            border: .45px solid #222;
            padding: 1.4px 1.6px;
            vertical-align: middle;
            overflow-wrap: break-word;
        }
        thead th {
            background: #fff2cc;
            font-size: 6.1px;
            font-weight: 700;
            line-height: 1.08;
            text-align: center;
        }
        .target-caption {
            font-size: 5.9px;
            font-style: italic;
            font-weight: 400;
        }
        .text-center { text-align: center; }
        .text-end {
            text-align: right;
            white-space: nowrap;
        }
        .fw-bold { font-weight: 700; }
        .row-header td {
            background: #e9eef5;
            font-weight: 700;
            border-top-width: .8px;
        }
        .row-subtotal td {
            background: #f3f4f6;
            font-weight: 700;
            border-top-width: .7px;
        }
        .row-grand td {
            background: #d9e2f3;
            font-weight: 700;
            border-top: 1px solid #111;
            border-bottom: 1px solid #111;
        }
        .long-text {
            font-size: 5.5px;
            line-height: 1.05;
        }
        .allocation-type {
            font-size: 5.9px;
            font-weight: 700;
            text-align: center;
        }
        .neg { color: #a00000; }
        .no-data {
            padding: 10px 4px;
            text-align: center;
            font-style: italic;
            color: #555;
        }
        .signatures {
            margin-top: 15px;
            border: 0;
            page-break-inside: avoid;
        }
        .signatures td {
            width: 25%;
            border: 0;
            padding: 18px 7px 0;
            text-align: center;
            vertical-align: bottom;
        }
        .sig-line {
            width: 88%;
            margin: 0 auto 2px;
            border-top: .7px solid #111;
        }
        .sig-name {
            min-height: 9px;
            font-size: 7.1px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .sig-position {
            min-height: 8px;
            font-size: 6.2px;
            font-style: italic;
            color: #444;
        }
        .sig-title {
            margin-top: 1px;
            font-size: 6.3px;
            color: #444;
        }
        .generated {
            margin-top: 9px;
            font-size: 5.7px;
            color: #777;
            text-align: right;
        }
    </style>
</head>
<body>
    @php
        $rows = $rows ?? collect();
        $months = $months ?? [
            1 => 'J', 2 => 'F', 3 => 'M', 4 => 'A', 5 => 'M', 6 => 'J',
            7 => 'J', 8 => 'A', 9 => 'S', 10 => 'O', 11 => 'N', 12 => 'D',
        ];
        $generatedAt = $generatedAt ?? now();

        $fmt = function ($value) {
            $value = (float) $value;
            if (abs($value) < 0.005) {
                return '-';
            }
            $negative = $value < 0;
            $text = number_format(abs($value), 2);
            $length = strlen($text);
            $size = 6.3;
            if ($length >= 11) $size = 5.6;
            if ($length >= 13) $size = 5.0;
            if ($length >= 15) $size = 4.4;
            if ($length >= 17) $size = 3.9;
            $style = $size !== 6.3 ? ' style="font-size:' . $size . 'px;"' : '';
            return $negative
                ? '<span class="neg"' . $style . '>(' . $text . ')</span>'
                : ($style ? '<span' . $style . '>' . $text . '</span>' : $text);
        };

        $isLong = fn ($text) => mb_strlen((string) $text) > 145;

        $allocationLabel = function ($code) {
            $code = trim((string) $code);
            return $code !== ''
                ? strtoupper(str_replace('_', ' ', $code))
                : '—';
        };

        $effectiveAmounts = function ($mooe, $capitalOutlay, $contractAmount) {
            $mooe = (float) $mooe;
            $capitalOutlay = (float) $capitalOutlay;

            if ($contractAmount === null || $contractAmount === '') {
                return [$mooe, $capitalOutlay];
            }

            $contractAmount = (float) $contractAmount;
            $total = $mooe + $capitalOutlay;

            if ($total <= 0) {
                return [0.0, 0.0];
            }

            return [
                $contractAmount * ($mooe / $total),
                $contractAmount * ($capitalOutlay / $total),
            ];
        };

        $blankTotals = function () {
            return [
                'mooe' => 0.0,
                'capital_outlay' => 0.0,
                'months' => array_fill(1, 12, 0.0),
                'total' => 0.0,
            ];
        };

        $grandTotals = $blankTotals();
        $blocks = [];
        $currentGroupIndex = null;
        $currentGroupKey = null;

        foreach ($rows as $model) {
            $rowType = (string) ($model->row_type ?? 'item');

            if ($rowType !== 'item') {
                $blocks[] = [
                    'type' => 'header',
                    'row' => [
                        'program_classification' => $model->program_classification
                            ?: ($model->specific_activity ?: '—'),
                        'prexc_code' => $model->prexc_code,
                        'allocation_type' => '',
                    ],
                ];
                $currentGroupIndex = null;
                $currentGroupKey = null;
                continue;
            }

            [$effectiveMooe, $effectiveCapitalOutlay] = $effectiveAmounts(
                $model->mooe,
                $model->capital_outlay,
                $model->contract_amount
            );

            $monthValues = array_fill(1, 12, 0.0);
            foreach (($model->targets ?? collect()) as $target) {
                $month = (int) $target->month;
                if ($month >= 1 && $month <= 12) {
                    $monthValues[$month] = (float) $target->amount;
                }
            }

            $rowTotal = array_sum($monthValues);
            $groupKey = implode('|', [
                trim((string) $model->program_classification),
                trim((string) $model->prexc_code),
            ]);

            if ($currentGroupIndex === null || $currentGroupKey !== $groupKey) {
                $blocks[] = [
                    'type' => 'group',
                    'rows' => [],
                    'totals' => $blankTotals(),
                ];
                $currentGroupIndex = array_key_last($blocks);
                $currentGroupKey = $groupKey;
            }

            $row = [
                'program_classification' => $model->program_classification,
                'prexc_code' => $model->prexc_code,
                'allocation_type' => '',
                'staff_unit_project' => $model->staff_unit_project,
                'specific_activity' => $model->specific_activity,
                'expense_item' => $model->expense_item,
                'assigned_personnel' => $model->assigned_personnel,
                'effective_mooe' => $effectiveMooe,
                'effective_capital_outlay' => $effectiveCapitalOutlay,
                'months' => $monthValues,
                'total' => $rowTotal,
            ];

            $blocks[$currentGroupIndex]['rows'][] = $row;
            $blocks[$currentGroupIndex]['totals']['mooe'] += $effectiveMooe;
            $blocks[$currentGroupIndex]['totals']['capital_outlay'] += $effectiveCapitalOutlay;
            $blocks[$currentGroupIndex]['totals']['total'] += $rowTotal;

            $grandTotals['mooe'] += $effectiveMooe;
            $grandTotals['capital_outlay'] += $effectiveCapitalOutlay;
            $grandTotals['total'] += $rowTotal;

            for ($m = 1; $m <= 12; $m++) {
                $blocks[$currentGroupIndex]['totals']['months'][$m] += $monthValues[$m];
                $grandTotals['months'][$m] += $monthValues[$m];
            }
        }
    @endphp

    <div class="report-title">FY {{ $fiscalYear }} FINANCIAL PLAN</div>
    <div class="report-subtitle">(FY {{ $fiscalYear }} Internal Allocation per approved {{ $fiscalYear }} GAA)</div>
    <div class="report-meta">
        <strong>Office/Staff:</strong>
        <span class="office-name">{{ $officeName ?: 'All Offices' }}</span>
    </div>

    <table>
        <colgroup>
            <col style="width:7.5%;">
            <col style="width:3%;">
            <col style="width:5%;">
            <col style="width:4%;">
            <col style="width:13%;">
            <col style="width:5%;">
            <col style="width:5%;">
            <col style="width:6%;">
            <col style="width:6%;">
            @for ($i = 1; $i <= 12; $i++)
                <col style="width:3%;">
            @endfor
            <col style="width:9.5%;">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2">Classification<br>(a)</th>
                <th rowspan="2">PREXC<br>(b)</th>
                <th rowspan="2">Allocation<br>Type</th>
                <th rowspan="2">Staffs/<br>Units (c)</th>
                <th rowspan="2">Specific Activity (d)</th>
                <th rowspan="2">Expense Item (e)</th>
                <th rowspan="2">Assigned<br>Personnel</th>
                <th rowspan="2">MOOE</th>
                <th rowspan="2">Capital<br>Outlay</th>
                <th colspan="13" class="target-caption">(f) Financial Target/Output (&#8369;)</th>
            </tr>
            <tr>
                @foreach ($months as $label)
                    <th>{{ $label }}</th>
                @endforeach
                <th>TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($blocks as $block)
                @if ($block['type'] === 'header')
                    <tr class="row-header">
                        <td>{{ $block['row']['program_classification'] ?? '—' }}</td>
                        <td class="text-center">{{ $block['row']['prexc_code'] ?? '' }}</td>
                        <td class="allocation-type">{{ $allocationLabel($block['row']['allocation_type'] ?? '') }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        @for ($i = 1; $i <= 12; $i++)
                            <td></td>
                        @endfor
                        <td></td>
                    </tr>
                @else
                    @foreach ($block['rows'] as $idx => $r)
                        <tr>
                            @if ($idx === 0)
                                <td>{{ $r['program_classification'] ?? '—' }}</td>
                                <td class="text-center">{{ $r['prexc_code'] ?? '—' }}</td>
                            @else
                                <td></td>
                                <td></td>
                            @endif
                            <td class="allocation-type">{{ $allocationLabel($r['allocation_type'] ?? '') }}</td>
                            <td class="text-center">{{ $r['staff_unit_project'] ?? '—' }}</td>
                            <td class="{{ $isLong($r['specific_activity'] ?? '') ? 'long-text' : '' }}">
                                {{ $r['specific_activity'] ?? '—' }}
                            </td>
                            <td class="{{ $isLong($r['expense_item'] ?? '') ? 'long-text' : '' }}">
                                {{ $r['expense_item'] ?? '—' }}
                            </td>
                            <td class="text-center">{{ $r['assigned_personnel'] ?? '—' }}</td>
                            <td class="text-end">{!! $fmt($r['effective_mooe'] ?? 0) !!}</td>
                            <td class="text-end">{!! $fmt($r['effective_capital_outlay'] ?? 0) !!}</td>
                            @for ($m = 1; $m <= 12; $m++)
                                <td class="text-end">{!! $fmt($r['months'][$m] ?? 0) !!}</td>
                            @endfor
                            <td class="text-end fw-bold">{!! $fmt($r['total'] ?? 0) !!}</td>
                        </tr>
                    @endforeach

                    <tr class="row-subtotal">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="text-end">TOTAL</td>
                        <td class="text-end">{!! $fmt($block['totals']['mooe'] ?? 0) !!}</td>
                        <td class="text-end">{!! $fmt($block['totals']['capital_outlay'] ?? 0) !!}</td>
                        @for ($m = 1; $m <= 12; $m++)
                            <td class="text-end">{!! $fmt($block['totals']['months'][$m] ?? 0) !!}</td>
                        @endfor
                        <td class="text-end">{!! $fmt($block['totals']['total'] ?? 0) !!}</td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="22" class="no-data">No Financial Plan records found for the selected fiscal year and office/staff.</td>
                </tr>
            @endforelse

            <tr class="row-grand">
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-end">GRAND TOTAL</td>
                <td class="text-end">{!! $fmt($grandTotals['mooe'] ?? 0) !!}</td>
                <td class="text-end">{!! $fmt($grandTotals['capital_outlay'] ?? 0) !!}</td>
                @for ($m = 1; $m <= 12; $m++)
                    <td class="text-end">{!! $fmt($grandTotals['months'][$m] ?? 0) !!}</td>
                @endfor
                <td class="text-end">{!! $fmt($grandTotals['total'] ?? 0) !!}</td>
            </tr>
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td>
                <div class="sig-line"></div>
                <div class="sig-name">{{ optional($signatory)->prepared_by }}</div>
                <div class="sig-position">{{ optional($signatory)->prepared_by_position }}</div>
                <div class="sig-title">Prepared by</div>
            </td>
            <td>
                <div class="sig-line"></div>
                <div class="sig-name">{{ optional($signatory)->reviewed_by }}</div>
                <div class="sig-position">{{ optional($signatory)->reviewed_by_position }}</div>
                <div class="sig-title">Reviewed by</div>
            </td>
            <td>
                <div class="sig-line"></div>
                <div class="sig-name">{{ optional($signatory)->recommended_by }}</div>
                <div class="sig-position">{{ optional($signatory)->recommended_by_position }}</div>
                <div class="sig-title">Recommended by</div>
            </td>
            <td>
                <div class="sig-line"></div>
                <div class="sig-name">{{ optional($signatory)->approved_by }}</div>
                <div class="sig-position">{{ optional($signatory)->approved_by_position }}</div>
                <div class="sig-title">Approved by</div>
            </td>
        </tr>
    </table>

    <div class="generated">Generated {{ $generatedAt->format('F j, Y g:i A') }}</div>
</body>
</html>
