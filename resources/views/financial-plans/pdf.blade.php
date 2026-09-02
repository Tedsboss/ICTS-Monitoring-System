<!DOCTYPE html>

<html>

<head>

    <meta charset="utf-8">

    <style>

        @page {

            margin: 0.31in 0.28in 0.28in 0.28in;

        }

        body {

            font-family: 'DejaVu Sans', sans-serif;

            font-size: 7px;

            color: #000;

        }

        .title {

            font-weight: bold;

            font-size: 10px;

        }

        .subtitle {

            font-style: italic;

            color: #333;

            font-size: 8px;

            margin-bottom: 1px;

        }

        .office {

            font-size: 8px;

            margin-bottom: 4px;

        }

        .office span {

            font-weight: bold;

            text-decoration: underline;

        }

        .target-caption {

            font-size: 6.5px;

            font-style: italic;

            text-align: right;

            margin-bottom: 2px;

            color: #333;

        }

        /*
         * Table width adjusted after removing Procurement Status.
         * MOOE / Capital Outlay, month cells and TOTAL were widened.
         *
         * Large totals are handled by the $fmt() helper below.
         * The helper automatically reduces the font size for long amounts
         * so they remain inside their cells.
         */

        table {

            border-collapse: collapse;

            table-layout: fixed;

            width: 852pt;

        }

        .th-target-caption {

            font-style: italic;

            font-weight: normal;

            font-size: 6.5px;

        }

        th,

        td {

            border: 0.5px solid #000;

            padding: 1.5px 2px;

            vertical-align: middle;

            word-wrap: break-word;

            overflow-wrap: break-word;

            line-height: 1.2;

        }

        /*
         * Header labels wrap only at natural word boundaries.
         */

        thead th {

            background: #FFFF00;

            text-align: center;

            font-weight: bold;

            font-size: 6.5px;

            color: #000;

            word-wrap: normal;

            overflow-wrap: normal;

        }

        .text-end {

            text-align: right;

            white-space: nowrap;

            word-wrap: normal;

            overflow-wrap: normal;

            font-size: 6.5px;

        }

        .text-center {

            text-align: center;

        }

        .fw-bold {

            font-weight: bold;

        }

        .row-header td {

            background: #eef1f5;

            font-weight: bold;

        }

        .row-subtotal td {

            background: #f1f3f5;

            font-weight: bold;

        }

        .row-grand td {

            background: #e0e0e0;

            font-weight: bold;

        }

        .amt-hit {

        }
.neg {

            color: #C00000;

        }

        .signatures {

            margin-top: 18px;

            width: 100%;

            border: none;

        }

        .signatures td {

            border: none;

            padding-top: 18px;

            font-size: 7px;

            vertical-align: bottom;

            text-align: center;

        }

        .sig-line {

            border-top: 0.75px solid #000;

            width: 85%;

            margin: 0 auto 2px;

        }

        .sig-name {

            font-weight: bold;

            text-transform: uppercase;

            font-size: 7.5px;

        }

        .sig-position {

            font-style: italic;

            font-size: 6.5px;

            color: #333;

        }

        .sig-title {

            color: #444;

            margin-top: 1px;

        }

        .instructions {

            margin-top: 10px;

            font-size: 6.5px;

            color: #333;

        }

        .instructions div {

            margin-bottom: 1px;

        }

        .long-text {

            font-size: 5.5px;

            line-height: 1.05;

        }

    </style>

</head>

<body>

    <div class="title">

        FY {{ $fiscalYear }} FINANCIAL PLAN

    </div>

    <div class="subtitle">

        (FY {{ $fiscalYear }} Internal Allocation per approved {{ $fiscalYear }} GAA)

    </div>

    <div class="office">

        Name of Office/Staff:

        <span>{{ $officeName ?: 'All Offices' }}</span>

    </div>

    @php

        // Format financial values and shrink long amounts when needed.

        $fmt = function ($v) {

            $v = (float) $v;

            if ($v == 0) {

                return '-';

            }

            $isNeg = $v < 0;

            $text = number_format(abs($v), 2);

            $len = strlen($text);

            $size = 6.5;

            // 7-digit integer part

            if ($len >= 11) {

                $size = 5.6;

            }

            // 8-digit integer part

            if ($len >= 13) {

                $size = 4.8;

            }

            // 9-digit integer part

            if ($len >= 15) {

                $size = 4.0;

            }

            // 10-digit+ integer part

            if ($len >= 17) {

                $size = 3.3;

            }

            $needsStyle = $size != 6.5;

            $style = $needsStyle

                ? " style=\"font-size:{$size}px;\""

                : '';

            if ($isNeg) {

                return "<span class=\"neg\"{$style}>({$text})</span>";

            }

            return $needsStyle

                ? "<span{$style}>{$text}</span>"

                : $text;

        };

        // Reduce font size for unusually long descriptions.

        $isLong = fn ($text) => strlen((string) $text) > 150;

    @endphp



    <table>

        <colgroup>

                        <col style="width:74pt;">
            <col style="width:28pt;">
            <col style="width:36pt;">
            <col style="width:128pt;">
            <col style="width:44pt;">
            <col style="width:42pt;">
            <col style="width:49pt;">
            <col style="width:49pt;">

            @for ($i = 1; $i <= 12; $i++)

                <col style="width:29pt;">

            @endfor

            <col style="width:50pt;">

        </colgroup>



        <thead>

            <tr>

                <th rowspan="2">Classification (a)</th>

                <th rowspan="2">PREXC (b)</th>

                <th rowspan="2">Staffs/Units (c)</th>

                <th rowspan="2">Specific Activity (d)</th>
<th rowspan="2">Expense Item (e)</th>

                <th rowspan="2">Assigned Personnel</th>

                <th rowspan="2">MOOE</th>

                <th rowspan="2">Capital Outlay</th>

                <th colspan="13" class="th-target-caption">

                    (f) Financial Target/Output (&#8369;)

                </th>

            </tr>

            <tr>

                @foreach ($months as $label)

                    <th>{{ $label }}</th>

                @endforeach

                <th>TOTAL</th>

            </tr>

        </thead>



        <tbody>

            @foreach ($blocks as $block)

                @if ($block['type'] === 'header')

                    <tr class="row-header">

                        <td>

                            {{ $block['row']['program_classification'] ?? '—' }}

                        </td>

                        <td class="text-center">

                            {{ $block['row']['prexc_code'] ?? '' }}

                        </td>



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

                                <td>

                                    {{ $r['program_classification'] ?? '—' }}

                                </td>

                                <td class="text-center">

                                    {{ $r['prexc_code'] ?? '—' }}

                                </td>

                            @else

                                <td></td>

                                <td></td>

                            @endif



                            <td class="text-center">

                                {{ $r['staff_unit_project'] ?? '—' }}

                            </td>



                            <td

                                class="{{ $isLong($r['specific_activity'] ?? '') ? 'long-text' : '' }}"

                            >

                                {{ $r['specific_activity'] ?? '—' }}

                            </td>
<td

                                class="text-center {{ $isLong($r['expense_item'] ?? '') ? 'long-text' : '' }}"

                            >

                                {{ $r['expense_item'] ?? '—' }}

                            </td>



                            <td class="text-center">

                                {{ $r['assigned_personnel'] ?? '—' }}

                            </td>



                            <td class="text-end">

                                {!! $fmt($r['effective_mooe'] ?? 0) !!}

                            </td>



                            <td class="text-end">

                                {!! $fmt($r['effective_capital_outlay'] ?? 0) !!}

                            </td>



                            @for ($m = 1; $m <= 12; $m++)

                                @php

                                    $mv = (float) ($r['months'][$m] ?? 0);

                                @endphp

                                <td

                                    class="text-end {{ $mv != 0 ? 'amt-hit' : '' }}"

                                >

                                    {!! $fmt($mv) !!}

                                </td>

                            @endfor



                            <td

                                class="text-end fw-bold

                                       {{ (float) ($r['total'] ?? 0) != 0 ? 'amt-hit' : '' }}"

                            >

                                {!! $fmt($r['total'] ?? 0) !!}

                            </td>

                        </tr>

                    @endforeach



                    {{-- Block subtotal --}}

                    <tr class="row-subtotal">



                        <td></td>

                        <td></td>

                        <td></td>

                        <td></td>

                        <td></td>

                        <td class="text-end">

                            TOTAL

                        </td>



                        <td class="text-end">

                            {!! $fmt($block['totals']['mooe'] ?? 0) !!}

                        </td>



                        <td class="text-end">

                            {!! $fmt($block['totals']['capital_outlay'] ?? 0) !!}

                        </td>



                        @for ($m = 1; $m <= 12; $m++)

                            @php

                                $mv = (float) ($block['totals']['months'][$m] ?? 0);

                            @endphp

                            <td

                                class="text-end {{ $mv != 0 ? 'amt-hit' : '' }}"

                            >

                                {!! $fmt($mv) !!}

                            </td>

                        @endfor



                        <td

                            class="text-end

                                   {{ (float) ($block['totals']['total'] ?? 0) != 0 ? 'amt-hit' : '' }}"

                        >

                            {!! $fmt($block['totals']['total'] ?? 0) !!}

                        </td>

                    </tr>

                @endif

            @endforeach



            {{-- Grand Total --}}

            <tr class="row-grand">



                <td></td>

                <td></td>

                <td></td>

                <td></td>

                <td></td>

                <td class="text-end">

                    GRAND TOTAL

                </td>



                <td class="text-end">

                    {!! $fmt($grandTotals['mooe'] ?? 0) !!}

                </td>



                <td class="text-end">

                    {!! $fmt($grandTotals['capital_outlay'] ?? 0) !!}

                </td>



                @for ($m = 1; $m <= 12; $m++)

                    <td class="text-end">

                        {!! $fmt($grandTotals['months'][$m] ?? 0) !!}

                    </td>

                @endfor



                <td class="text-end">

                    {!! $fmt($grandTotals['total'] ?? 0) !!}

                </td>

            </tr>

        </tbody>

    </table>



    {{-- Signatories --}}

    <table class="signatures">

        <tr>

            <td style="width:25%;">

                <div class="sig-line"></div>

                <div class="sig-name">

                    {{ $signatory->prepared_by ?? '' }}

                </div>

                <div class="sig-position">

                    {{ $signatory->prepared_by_position ?? '' }}

                </div>

                <div class="sig-title">

                    Prepared by

                </div>

            </td>



            <td style="width:25%;">

                <div class="sig-line"></div>

                <div class="sig-name">

                    {{ $signatory->reviewed_by ?? '' }}

                </div>

                <div class="sig-position">

                    {{ $signatory->reviewed_by_position ?? '' }}

                </div>

                <div class="sig-title">

                    Reviewed by

                </div>

            </td>



            <td style="width:25%;">

                <div class="sig-line"></div>

                <div class="sig-name">

                    {{ $signatory->recommended_by ?? '' }}

                </div>

                <div class="sig-position">

                    {{ $signatory->recommended_by_position ?? '' }}

                </div>

                <div class="sig-title">

                    Recommended by

                </div>

            </td>



            <td style="width:25%;">

                <div class="sig-line"></div>

                <div class="sig-name">

                    {{ $signatory->approved_by ?? '' }}

                </div>

                <div class="sig-position">

                    {{ $signatory->approved_by_position ?? '' }}

                </div>

                <div class="sig-title">

                    Approved by

                </div>

            </td>

        </tr>

    </table>



    <div

        style="

            margin-top:14px;

            font-size:6px;

            color:#888;

        "

    >

        Generated {{ $generatedAt->format('F j, Y g\:i A') }}

    </div>

</body>

</html>
