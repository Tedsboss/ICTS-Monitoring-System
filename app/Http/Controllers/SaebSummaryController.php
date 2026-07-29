<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SaebSummaryController extends Controller
{
    public function index()
    {
        $saebBalancesByClass = DB::select("
            SELECT
                COALESCE(funding_source, 'Grand Total') AS funding_source,
                SUM(CASE WHEN allotment_class = 'CO'   THEN balances ELSE 0 END) AS co,
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
                SUM(aa)        AS sum_aa,
                SUM(balances)  AS sum_balances,
                CASE
                    WHEN SUM(allotment) = 0 THEN 0
                    ELSE ROUND(SUM(obligated) / SUM(allotment) * 100, 2)
                END AS pct_obligated
            FROM saeb
            GROUP BY funding_source WITH ROLLUP
        ");

        $balancesTotal = collect($saebBalancesByClass)->firstWhere('funding_source', 'Grand Total');
        $fundTotal      = collect($saebFundSummary)->firstWhere('funding_source', 'Grand Total');

        // How far through the calendar year we are, as a percentage —
        // the yardstick "needs attention" is measured against. A fund at
        // 15% obligated in January is on track; the same 15% in October
        // is a problem. Flat thresholds can't tell those apart.
        $dayOfYear     = now()->dayOfYear;
        $daysInYear    = now()->isLeapYear() ? 366 : 365;
        $yearProgress  = round(($dayOfYear / $daysInYear) * 100, 1);

        // A fund needs attention if it's trailing year-progress by more
        // than 15 points — e.g. 60% through the year but only 30% obligated.
        // The 15-point buffer avoids flagging funds that are only slightly
        // behind, which is normal noise, not a real problem.
        $attentionBuffer = 15;

        $flaggedFunds = collect($saebFundSummary)
            ->reject(fn ($row) => $row->funding_source === 'Grand Total')
            ->filter(fn ($row) => $row->pct_obligated < ($yearProgress - $attentionBuffer))
            ->values();

        return view('saeb-index', compact(
            'saebBalancesByClass',
            'saebFundSummary',
            'balancesTotal',
            'fundTotal',
            'yearProgress',
            'flaggedFunds'
        ));
    }
}