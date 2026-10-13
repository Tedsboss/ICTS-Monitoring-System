@php
    $mdpPdf = $mdpPdf ?? false;
    $mdpAmountStyle = static function (string $text) use ($mdpPdf): string {
        if (!$mdpPdf) {
            return '';
        }
        $length = strlen($text);
        $size = $length >= 17 ? 4.4 : ($length >= 15 ? 5.0 : ($length >= 12 ? 5.8 : 6.8));
        return 'font-size:' . $size . 'px;';
    };
@endphp
<table class="mdp-table">
    <thead><tr><th rowspan="2" scope="col" class="mdp-particulars">Particulars</th><th colspan="12" scope="colgroup">Months</th><th rowspan="2" scope="col" class="mdp-annual">TOTAL</th></tr><tr>@foreach ($months as $month)<th scope="col">{{ $month }}</th>@endforeach</tr></thead>
    <tbody>
        @foreach (($group['rows'] ?? []) as $row)
            <tr><td class="mdp-activity">{{ $row['activity'] ?: 'Untitled activity' }}</td>@foreach ($months as $number => $month)@php $amount = $money($row['monthly_total'][$number], true); @endphp<td class="mdp-number" style="{{ $mdpAmountStyle($amount) }}">{{ $amount }}</td>@endforeach @php $amount = $money($row['annual_total'], true); @endphp<td class="mdp-number mdp-bold" style="{{ $mdpAmountStyle($amount) }}">{{ $amount }}</td></tr>
        @endforeach
        @foreach (['mooe' => 'MOOE', 'co' => 'Capital Outlay', 'total' => 'TOTAL'] as $category => $label)
            <tr class="mdp-summary {{ $category === 'total' ? 'mdp-grand-total' : '' }}"><th scope="row">{{ $label }}</th>@foreach ($months as $number => $month)@php $amount = $money($group['summary']['monthly_' . $category][$number], true); @endphp<td class="mdp-number" style="{{ $mdpAmountStyle($amount) }}">{{ $amount }}</td>@endforeach @php $amount = $money($group['summary']['annual_' . $category], true); @endphp<td class="mdp-number" style="{{ $mdpAmountStyle($amount) }}">{{ $amount }}</td></tr>
        @endforeach
    </tbody>
</table>
