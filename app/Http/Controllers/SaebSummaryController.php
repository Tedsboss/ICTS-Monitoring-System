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

        return view('saeb-index', compact('saebBalancesByClass', 'saebFundSummary'));
    }
}
