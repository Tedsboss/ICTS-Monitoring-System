<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class SaebSummaryController extends Controller
{
    public function index()
    {
        $saebBalancesByClass = DB::select("
            SELECT
                COALESCE(funding_source, 'Grand Total') AS funding_source,
                SUM(CASE WHEN allotment_class = 'CO' THEN balances ELSE 0 END) AS co,
                SUM(CASE WHEN allotment_class = 'MOOE' THEN balances ELSE 0 END) AS mooe,
                SUM(balances) AS grand_total
            FROM saeb
            GROUP BY funding_source WITH ROLLUP
        ");

        $saebFundSummary = DB::select("
            SELECT
                COALESCE(funding_source, 'Grand Total') AS funding_source,
                SUM(allotment) AS sum_allotment,
                SUM(obligated) AS sum_obligated,
                SUM(aa) AS sum_aa,
                SUM(balances) AS sum_balances,
                CASE
                    WHEN SUM(allotment) = 0 THEN 0
                    ELSE ROUND(SUM(obligated) / SUM(allotment) * 100, 2)
                END AS pct_obligated
            FROM saeb
            GROUP BY funding_source WITH ROLLUP
        ");

        $fundTotal = collect($saebFundSummary)
            ->firstWhere('funding_source', 'Grand Total');

        // Percentage of the current calendar year already completed
        $dayOfYear = now()->dayOfYear;
        $daysInYear = now()->isLeapYear() ? 366 : 365;
        $yearProgress = round(($dayOfYear / $daysInYear) * 100, 1);

        // A fund is flagged when obligation progress trails the year by more than 15 points
        $attentionBuffer = 15;

        $flaggedFunds = collect($saebFundSummary)
            ->reject(fn ($row) => $row->funding_source === 'Grand Total')
            ->filter(
                fn ($row) => (float) $row->pct_obligated
                    < ($yearProgress - $attentionBuffer)
            )
            ->values();

        return view('saeb-index', compact(
            'saebBalancesByClass',
            'saebFundSummary',
            'fundTotal',
            'yearProgress',
            'attentionBuffer',
            'flaggedFunds'
        ));
    }
}
