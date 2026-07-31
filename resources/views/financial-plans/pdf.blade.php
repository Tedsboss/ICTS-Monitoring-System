<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0.31in 0.28in 0.28in 0.28in; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 7px; color: #000; }
        .title { font-weight: bold; font-size: 10px; }
        .subtitle { font-style: italic; color: #333; font-size: 8px; margin-bottom: 1px; }
        .office { font-size: 8px; margin-bottom: 4px; }
        .office span { font-weight: bold; text-decoration: underline; }
        .target-caption { font-size: 6.5px; font-style: italic; text-align: right; margin-bottom: 2px; color: #333; }

        /* Table width bumped from 795pt to 852pt — MOOE/Capital Outlay,
           month cells, and TOTAL all widened, pulled mostly from Specific
           Activity (147pt → 120pt). Usable width on Folio landscape is
           ~895pt, so 852pt leaves ~43pt of margin for dompdf's rounding
           error. This alone is NOT the fix for overflow, though — grand
           totals sum across every row in the plan, so they can land
           anywhere from 7 to 10+ digits and no fixed column width
           survives that. The actual fix is the font-size auto-shrink in
           $fmt() below: past a character-count threshold, the amount
           renders in a smaller size instead of overflowing the cell. */
        table { border-collapse: collapse; table-layout: fixed; width: 852pt; }
        .th-target-caption { font-style: italic; font-weight: normal; font-size: 6.5px; }
        th, td { border: 0.5px solid #000; padding: 1.5px 2px; vertical-align: middle; word-wrap: break-word; overflow-wrap: break-word; line-height: 1.2; }

        /* Header labels should only ever wrap at a natural word boundary
           (a space), never mid-word — word-wrap/overflow-wrap: normal
           overrides the break-word above specifically for th cells.
           Combined with the widened columns below (Status, PREXC, Assigned
           Personnel), this eliminates ugly splits like "procureme/nt". */
        thead th { background: #FFFF00; text-align: center; font-weight: bold; font-size: 6.5px; color: #000; word-wrap: normal; overflow-wrap: normal; }

        .text-end { text-align: right; white-space: nowrap; word-wrap: normal; overflow-wrap: normal; font-size: 6.5px; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }

        .row-header td { background: #eef1f5; font-weight: bold; }
        .row-subtotal td { background: #f1f3f5; font-weight: bold; }
        .row-grand td { background: #e0e0e0; font-weight: bold; }

        .amt-hit { }

        .status-ok {  font-weight: bold; }
        .status-note {  }

        .neg { color: #C00000; }

        .signatures { margin-top: 18px; width: 100%; border: none; }
        .signatures td { border: none; padding-top: 18px; font-size: 7px; vertical-align: bottom; text-align: center; }
        .sig-line { border-top: 0.75px solid #000; width: 85%; margin: 0 auto 2px; }
        .sig-name { font-weight: bold; text-transform: uppercase; font-size: 7.5px; }
        .sig-position { font-style: italic; font-size: 6.5px; color: #333; }
        .sig-title { color: #444; margin-top: 1px; }

        .instructions { margin-top: 10px; font-size: 6.5px; color: #333; }
        .instructions div { margin-bottom: 1px; }
        .long-text { font-size: 5.5px; line-height: 1.05; }
    </style>
</head>
<body>

    <div class="title">FY {{ $fiscalYear }} FINANCIAL PLAN</div>
    <div class="subtitle">(FY {{ $fiscalYear }} Internal Allocation per approved {{ $fiscalYear }} GAA)</div>
    <div class="office">Name of Office/Staff: <span>{{ $officeName ?: 'All Offices' }}</span></div>

    @php
        /**
         * Grand totals sum across every row in the plan, so their digit
         * count is unbounded — a fixed column width can't guarantee they
         * fit. Instead of clipping/overflowing, the rendered font size
         * steps down once the formatted string passes a character
         * threshold, so an 8-, 9-, or 10-digit peso amount still lands
         * inside its cell, just smaller. Thresholds were picked against
         * the widened amount columns (46-50pt): a 6-digit value
         * ("999,999.99", 10 chars) needs no shrink; each step down buys
         * roughly 2-3 more digits of headroom.
         */
        $fmt = function ($v) {
            $v = (float) $v;
            if ($v == 0) return '-';

            $isNeg = $v < 0;
            $text  = number_format(abs($v), 2);
            $len   = strlen($text);

            $size = 6.5;
            if ($len >= 11) $size = 5.6;   // 7-digit integer part
            if ($len >= 13) $size = 4.8;   // 8-digit integer part
            if ($len >= 15) $size = 4.0;   // 9-digit integer part
            if ($len >= 17) $size = 3.3;   // 10-digit+ integer part

            $needsStyle = $size != 6.5;
            $style = $needsStyle ? " style=\"font-size:{$size}px;\"" : '';

            if ($isNeg) {
                return "<span class=\"neg\"{$style}>({$text})</span>";
            }

            return $needsStyle ? "<span{$style}>{$text}</span>" : $text;
        };
        $isLong = fn ($text) => strlen((string) $text) > 150;
    @endphp

    <table>
        <colgroup>
            <col style="width:70pt;">
            <col style="width:26pt;">
            <col style="width:34pt;">
            <col style="width:120pt;">
            <col style="width:34pt;">
            <col style="width:40pt;">
            <col style="width:38pt;">
            <col style="width:46pt;">
            <col style="width:46pt;">
            @for($i = 1; $i <= 12; $i++)<col style="width:29pt;">@endfor
            <col style="width:50pt;">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2">Classification (a)</th>
                <th rowspan="2">PREXC (b)</th>
                <th rowspan="2">Staffs/Units (c)</th>
                <th rowspan="2">Specific Activity (d)</th>
                <th rowspan="2">Status</th>
                <th rowspan="2">Expense Item (e)</th>
                <th rowspan="2">Assigned Personnel</th>
                <th rowspan="2">MOOE</th>
                <th rowspan="2">Capital Outlay</th>
                <th colspan="13" class="th-target-caption">(f) Financial Target/Output (&#8369;)</th>
            </tr>
            <tr>
                @foreach($months as $label)
                    <th>{{ $label }}</th>
                @endforeach
                <th>TOTAL</th>
            </tr>
        </thead>
        <tbody>
        @foreach($blocks as $block)
            @if($block['type'] === 'header')
                <tr class="row-header">
                    <td>{{ $block['row']['program_classification'] ?? '—' }}</td>
                    <td class="text-center">{{ $block['row']['prexc_code'] ?? '' }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    @for($i = 1; $i <= 12; $i++)<td></td>@endfor
                    <td></td>
                </tr>
            @else
                @foreach($block['rows'] as $idx => $r)
                    <tr>
                        @if($idx === 0)
                            <td>{{ $r['program_classification'] ?? '—' }}</td>
                            <td class="text-center">{{ $r['prexc_code'] ?? '—' }}</td>
                        @else
                            <td></td>
                            <td></td>
                        @endif
                        <td class="text-center">{{ $r['staff_unit_project'] ?? '—' }}</td>
                        <td class="{{ $isLong($r['specific_activity'] ?? '') ? 'long-text' : '' }}">{{ $r['specific_activity'] ?? '—' }}</td>
                        <td class="text-center
                            @if($r['procurement_status'] === 'OK') status-ok
                            @elseif(!empty($r['procurement_status'])) status-note
                            @endif
                        ">{{ $r['procurement_status'] ?? '' }}</td>
                        <td class="text-center {{ $isLong($r['expense_item'] ?? '') ? 'long-text' : '' }}">{{ $r['expense_item'] ?? '—' }}</td>
                        <td class="text-center">{{ $r['assigned_personnel'] ?? '—' }}</td>
                        <td class="text-end">{!! $fmt($r['mooe']) !!}</td>
                        <td class="text-end">{!! $fmt($r['capital_outlay']) !!}</td>
                        @for($m = 1; $m <= 12; $m++)
                            @php $mv = (float) ($r['months'][$m] ?? 0); @endphp
                            <td class="text-end {{ $mv != 0 ? 'amt-hit' : '' }}">{!! $fmt($mv) !!}</td>
                        @endfor
                        <td class="text-end fw-bold {{ (float)$r['total'] != 0 ? 'amt-hit' : '' }}">{!! $fmt($r['total']) !!}</td>
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
                    <td class="text-end">{!! $fmt($block['totals']['mooe']) !!}</td>
                    <td class="text-end">{!! $fmt($block['totals']['capital_outlay']) !!}</td>
                    @for($m = 1; $m <= 12; $m++)
                        @php $mv = (float) $block['totals']['months'][$m]; @endphp
                        <td class="text-end {{ $mv != 0 ? 'amt-hit' : '' }}">{!! $fmt($mv) !!}</td>
                    @endfor
                    <td class="text-end {{ (float)$block['totals']['total'] != 0 ? 'amt-hit' : '' }}">{!! $fmt($block['totals']['total']) !!}</td>
                </tr>
            @endif
        @endforeach
        <tr class="row-grand">
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td class="text-end">GRAND TOTAL</td>
            <td class="text-end">{!! $fmt($grandTotals['mooe']) !!}</td>
            <td class="text-end">{!! $fmt($grandTotals['capital_outlay']) !!}</td>
            @for($m = 1; $m <= 12; $m++)
                <td class="text-end">{!! $fmt($grandTotals['months'][$m]) !!}</td>
            @endfor
            <td class="text-end">{!! $fmt($grandTotals['total']) !!}</td>
        </tr>
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td style="width:25%;">
                <div class="sig-line"></div>
                <div class="sig-name">{{ $signatory->prepared_by ?? '' }}</div>
                <div class="sig-position">{{ $signatory->prepared_by_position ?? '' }}</div>
                <div class="sig-title">Prepared by</div>
            </td>
            <td style="width:25%;">
                <div class="sig-line"></div>
                <div class="sig-name">{{ $signatory->reviewed_by ?? '' }}</div>
                <div class="sig-position">{{ $signatory->reviewed_by_position ?? '' }}</div>
                <div class="sig-title">Reviewed by</div>
            </td>
            <td style="width:25%;">
                <div class="sig-line"></div>
                <div class="sig-name">{{ $signatory->recommended_by ?? '' }}</div>
                <div class="sig-position">{{ $signatory->recommended_by_position ?? '' }}</div>
                <div class="sig-title">Recommended by</div>
            </td>
            <td style="width:25%;">
                <div class="sig-line"></div>
                <div class="sig-name">{{ $signatory->approved_by ?? '' }}</div>
                <div class="sig-position">{{ $signatory->approved_by_position ?? '' }}</div>
                <div class="sig-title">Approved by</div>
            </td>
        </tr>
    </table>

    <div style="margin-top:14px; font-size:6px; color:#888;">
        Generated {{ $generatedAt->format('F j, Y g:i A') }}
    </div>

</body>
</html>
