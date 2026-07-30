<?php

namespace App\Http\Controllers;

use App\Models\FinancialPlan;
use App\Models\FinancialPlanSignatory;
use App\Models\FinancialPlanTarget;
use App\Traits\GenerateLogs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
class FinancialPlanController extends Controller
{
    use GenerateLogs;

    // ── CONFIG

    private const MONTHS = [
        1 => 'J', 2 => 'F', 3 => 'M', 4 => 'A', 5 => 'M', 6 => 'J',
        7 => 'J', 8 => 'A', 9 => 'S', 10 => 'O', 11 => 'N', 12 => 'D',
    ];

    // Fields eligible for per-column change tracking when an existing
    // row is updated (mirrors NPMC's fieldMap-driven auditLog calls).
    private const TRACKED_FIELDS = [
        'program_classification',
        'prexc_code',
        'staff_unit_project',
        'specific_activity',
        'procurement_status',
        'expense_item',
        'assigned_personnel',
        'mooe',
        'capital_outlay',
    ];

    // ── HELPERS

    /**
     * Empty-string numeric inputs (a cleared MOOE / Capital Outlay / month
     * cell) must become null before validation — Laravel's `nullable` rule
     * does not treat "" as null, and letting "" flow through into the
     * (float) casts further down is the main source of NaN in the UI.
     */
    private function normalizeRows(array $rows): array
    {
        return collect($rows)->map(function ($row) {
            foreach (['mooe', 'capital_outlay'] as $field) {
                if (array_key_exists($field, $row) && $row[$field] === '') {
                    $row[$field] = null;
                }
            }

            if (isset($row['months']) && is_array($row['months'])) {
                foreach ($row['months'] as $m => $val) {
                    if ($val === '') {
                        $row['months'][$m] = null;
                    }
                }
            }

            return $row;
        })->all();
    }

    // ── AUDIT LOG

    /**
     * GenerateLogs::addSystemLogs() signature is POSITIONAL, not named,
     * and only accepts: activity, userId, userName, ip, table, id, createdAt.
     * There is no separate old/new value column, so the change details are
     * folded directly into the activity string instead.
     */
    private function auditLog($planId, string $field, $old, $new, string $activity): void
    {
        $oldDisplay = $old === null || $old === '' ? '—' : $old;
        $newDisplay = $new === null || $new === '' ? '—' : $new;

        $this->addSystemLogs(
            "Financial Plan: {$activity} (from \"{$oldDisplay}\" to \"{$newDisplay}\")",
            auth()->id(),
            auth()->user()->name ?? auth()->user()->email ?? null,
            request()->getClientIp(true),
            'financial_plans',
            (int) $planId
        );
    }

    private function auditFieldChanges(FinancialPlan $plan, array $incoming): void
    {
        foreach (self::TRACKED_FIELDS as $field) {
            if (! array_key_exists($field, $incoming)) {
                continue;
            }

            $old = $plan->getOriginal($field);
            $new = $incoming[$field];

            if ((string) $old !== (string) $new) {
                $this->auditLog(
                    $plan->id,
                    $field,
                    $old,
                    $new,
                    'Updated ' . str_replace('_', ' ', $field)
                );
            }
        }
    }

    // ── PLANS LIST (browse every WFP that's been filed, across all FYs/offices)

    /**
     * Landing page for the Financial Plan module. Shows every distinct
     * (fiscal_year, office_name) combination that has at least one row on
     * file, so the user isn't dropped straight into editing whatever plan
     * happens to match "current year + first office alphabetically."
     * Also the jumping-off point for filing a brand-new WFP.
     */
    public function plans(Request $request): View
    {
        $this->authorize('viewAny', FinancialPlan::class);

        $plans = FinancialPlan::query()
            ->select('fiscal_year', 'office_name')
            ->selectRaw('COUNT(*) as row_count')
            ->selectRaw("SUM(CASE WHEN row_type = 'item' THEN mooe + capital_outlay ELSE 0 END) as total_budget")
            ->groupBy('fiscal_year', 'office_name')
            ->orderByDesc('fiscal_year')
            ->orderBy('office_name')
            ->get();

        $offices = FinancialPlan::query()->distinct()->orderBy('office_name')->pluck('office_name');

        return view('financial-plans.plans', [
            'plans'   => $plans,
            'offices' => $offices,
        ]);
    }

    // ── INDEX / BUILDER PAGES

    public function index(Request $request): View
    {
        $this->authorize('viewAny', FinancialPlan::class);

        $fiscalYear = (int) $request->input('fiscal_year', now()->year);
        $officeName = $request->string('office_name')->toString();

        $offices = FinancialPlan::query()->distinct()->orderBy('office_name')->pluck('office_name');

        if (! $officeName && $offices->isNotEmpty()) {
            $officeName = $offices->first();
        }

        return view('financial-plans.index', [
            'fiscalYear' => $fiscalYear,
            'officeName' => $officeName,
            'offices'    => $offices,
            'months'     => self::MONTHS,
        ]);
    }

    public function builder(Request $request): View
    {
        $this->authorize('viewAny', FinancialPlan::class);

        $fiscalYear = (int) $request->input('fiscal_year', now()->year);
        $officeName = $request->string('office_name')->toString();

        return view('financial-plans.builder', [
            'fiscalYear' => $fiscalYear,
            'officeName' => $officeName,
            'months'     => self::MONTHS,
        ]);
    }

    // ── DATA JSON
    // (feeds both the read-only display table and the builder grid preload)

    public function data(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FinancialPlan::class);

        $fiscalYear = (int) $request->input('fiscal_year', now()->year);
        $officeName = $request->string('office_name')->toString();

        $query = FinancialPlan::query()
            ->with('targets')
            ->where('fiscal_year', $fiscalYear);

        if ($officeName) {
            $query->where('office_name', $officeName);
        }

        $rows = $query
            ->with(['saebEntries', 'procurements'])
            ->orderBy('program_classification')
            ->orderBy('prexc_code')
            ->orderBy('sort_order')
            ->get();

        return response()->json($rows->map(fn (FinancialPlan $p) => [
            'id'                      => $p->id,
            'row_type'                => $p->row_type,
            'program_classification'  => $p->program_classification,
            'prexc_code'              => $p->prexc_code,
            'staff_unit_project'      => $p->staff_unit_project,
            'specific_activity'       => $p->specific_activity,
            'procurement_status'      => $p->is_procured ? 'OK' : $p->procurement_status,
            'expense_item'            => $p->expense_item,
            'assigned_personnel'      => $p->assigned_personnel,
            'mooe'                    => (float) $p->mooe,
            'capital_outlay'          => (float) $p->capital_outlay,
            'months'                  => $p->monthly_amounts,
            'total'                   => $p->total_target,
            'saeb_balance'            => $p->saeb_balance,
        ]));
    }

    public function signatories(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FinancialPlan::class);

        $fiscalYear = (int) $request->input('fiscal_year', now()->year);
        $officeName = $request->string('office_name')->toString();

        $signatory = FinancialPlanSignatory::where('fiscal_year', $fiscalYear)
            ->where('office_name', $officeName)
            ->first();

        return response()->json([
            'prepared_by'              => $signatory->prepared_by ?? '',
            'prepared_by_position'     => $signatory->prepared_by_position ?? '',
            'reviewed_by'              => $signatory->reviewed_by ?? '',
            'reviewed_by_position'     => $signatory->reviewed_by_position ?? '',
            'recommended_by'           => $signatory->recommended_by ?? '',
            'recommended_by_position'  => $signatory->recommended_by_position ?? '',
            'approved_by'              => $signatory->approved_by ?? '',
            'approved_by_position'     => $signatory->approved_by_position ?? '',
        ]);
    }

    public function saveSignatories(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FinancialPlan::class);

        $validated = $request->validate([
            'fiscal_year'              => ['required', 'integer', 'min:2000', 'max:2100'],
            'office_name'              => ['required', 'string', 'max:150'],
            'prepared_by'              => ['nullable', 'string', 'max:150'],
            'prepared_by_position'     => ['nullable', 'string', 'max:150'],
            'reviewed_by'              => ['nullable', 'string', 'max:150'],
            'reviewed_by_position'     => ['nullable', 'string', 'max:150'],
            'recommended_by'           => ['nullable', 'string', 'max:150'],
            'recommended_by_position'  => ['nullable', 'string', 'max:150'],
            'approved_by'              => ['nullable', 'string', 'max:150'],
            'approved_by_position'     => ['nullable', 'string', 'max:150'],
        ]);

        $signatory = FinancialPlanSignatory::updateOrCreate(
            [
                'fiscal_year' => $validated['fiscal_year'],
                'office_name' => $validated['office_name'],
            ],
            [
                'prepared_by'             => $validated['prepared_by'] ?? null,
                'prepared_by_position'    => $validated['prepared_by_position'] ?? null,
                'reviewed_by'             => $validated['reviewed_by'] ?? null,
                'reviewed_by_position'    => $validated['reviewed_by_position'] ?? null,
                'recommended_by'          => $validated['recommended_by'] ?? null,
                'recommended_by_position' => $validated['recommended_by_position'] ?? null,
                'approved_by'             => $validated['approved_by'] ?? null,
                'approved_by_position'    => $validated['approved_by_position'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Signatories saved.',
            'data'    => $signatory,
        ]);
    }


    public function save(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FinancialPlan::class);

        $request->merge([
            'rows' => $this->normalizeRows($request->input('rows', [])),
        ]);

        $validated = $request->validate([
            'fiscal_year'                   => ['required', 'integer', 'min:2000', 'max:2100'],
            'office_name'                   => ['required', 'string', 'max:150'],

            'rows'                          => ['nullable', 'array'],

            'rows.*.id'                     => ['nullable'],
            'rows.*.row_type'               => ['required', 'in:header,subheader,item'],

            'rows.*.program_classification' => ['nullable', 'string', 'max:500'],
            'rows.*.prexc_code'             => ['nullable', 'string', 'max:50'],
            'rows.*.staff_unit_project'     => ['nullable', 'string', 'max:150'],
            'rows.*.specific_activity'      => ['nullable', 'string'],
            'rows.*.procurement_status'     => ['nullable', 'string', 'max:150'],
            'rows.*.expense_item'           => ['nullable', 'string', 'max:150'],
            'rows.*.assigned_personnel'     => ['nullable', 'string', 'max:150'],

            'rows.*.mooe'                   => ['nullable', 'numeric'],
            'rows.*.capital_outlay'         => ['nullable', 'numeric'],

            'rows.*.months'                 => ['nullable', 'array'],
            'rows.*.months.*'               => ['nullable', 'numeric'],
        ]);

        $year   = $validated['fiscal_year'];
        $office = $validated['office_name'];
        $rows   = $validated['rows'] ?? [];

        DB::beginTransaction();

        try {
            $existingIds = FinancialPlan::where('fiscal_year', $year)
                ->where('office_name', $office)
                ->pluck('id')
                ->toArray();

            $savedIds  = [];
            $sortOrder = 10;

            foreach ($rows as $row) {
                $plan = null;

                if (! empty($row['id'])) {
                    $plan = FinancialPlan::where('id', $row['id'])
                        ->where('fiscal_year', $year)
                        ->where('office_name', $office)
                        ->first();
                }

                $isNew = ! $plan;
                $plan  ??= new FinancialPlan();

                $incoming = [
                    'fiscal_year'            => $year,
                    'office_name'            => $office,
                    'row_type'               => $row['row_type'],
                    'program_classification' => $row['program_classification'] ?? null,
                    'prexc_code'             => $row['prexc_code'] ?? null,
                    'staff_unit_project'     => $row['staff_unit_project'] ?? null,
                    'specific_activity'      => $row['specific_activity'] ?? null,
                    'procurement_status'     => $row['procurement_status'] ?? null,
                    'expense_item'           => $row['expense_item'] ?? null,
                    'assigned_personnel'     => $row['assigned_personnel'] ?? null,
                    'mooe'                   => (float) ($row['mooe'] ?? 0),
                    'capital_outlay'         => (float) ($row['capital_outlay'] ?? 0),
                    'sort_order'             => $sortOrder,
                ];

                if (! $isNew) {
                    $this->auditFieldChanges($plan, $incoming);
                }

                $plan->fill($incoming);
                $plan->save();

                if ($isNew) {
                    $this->auditLog($plan->id, 'row', null, $row['row_type'], 'Created a new row');
                }

                $savedIds[] = $plan->id;

                if ($row['row_type'] === 'item') {
                    for ($m = 1; $m <= 12; $m++) {
                        FinancialPlanTarget::updateOrCreate(
                            [
                                'financial_plan_id' => $plan->id,
                                'month'              => $m,
                            ],
                            [
                                'amount' => (float) ($row['months'][$m] ?? 0),
                            ]
                        );
                    }
                } else {
                    FinancialPlanTarget::where('financial_plan_id', $plan->id)->delete();
                }

                $sortOrder += 10;
            }

            $deleteIds = array_diff($existingIds, $savedIds);

            if (! empty($deleteIds)) {
                foreach ($deleteIds as $id) {
                    $this->auditLog($id, 'row', 'present', 'deleted', 'Row removed from plan');
                }
                FinancialPlan::whereIn('id', $deleteIds)->delete();
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Successfully saved data.',
                'redirect' => route('financial-plans.index', [
                    'fiscal_year' => $year,
                    'office_name' => $office,
                ]),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Financial Plan Save Error', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ── DESTROY

    public function destroy(FinancialPlan $financial_plan): RedirectResponse
    {
        $this->authorize('delete', $financial_plan);

        $this->auditLog(
            $financial_plan->id,
            'row',
            $financial_plan->row_type,
            'deleted',
            'Row deleted'
        );

        $financial_plan->delete();

        return redirect()->back()->with('success', 'Row deleted.');
    }

    // ── DESTROY (entire filed plan for one fiscal year + office)

    public function destroyPlan(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', FinancialPlan::class);

        $validated = $request->validate([
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'office_name' => ['required', 'string', 'max:150'],
        ]);

        $year   = $validated['fiscal_year'];
        $office = $validated['office_name'];

        $plans = FinancialPlan::where('fiscal_year', $year)
            ->where('office_name', $office)
            ->get();

        if ($plans->isEmpty()) {
            return redirect()->back()->with('success', 'Nothing to delete — that plan no longer exists.');
        }

        DB::beginTransaction();

        try {
            $rowCount = $plans->count();

            foreach ($plans as $plan) {
                $this->auditLog(
                    $plan->id,
                    'row',
                    $plan->row_type,
                    'deleted',
                    "Row deleted (bulk plan delete: FY {$year}, {$office})"
                );
            }

            FinancialPlanTarget::whereIn('financial_plan_id', $plans->pluck('id'))->delete();
            FinancialPlan::whereIn('id', $plans->pluck('id'))->delete();

            DB::commit();

            return redirect()->route('financial-plans.plans')
                ->with('success', "Deleted FY {$year} plan for {$office} ({$rowCount} row(s)).");
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Financial Plan Bulk Delete Error', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return redirect()->back()->with('success', 'Something went wrong deleting that plan.');
        }
    }

    public function exportPdf(Request $request)
    {
        $this->authorize('viewAny', FinancialPlan::class);

        $fiscalYear = (int) $request->input('fiscal_year', now()->year);
        $officeName = $request->string('office_name')->toString();

        $query = FinancialPlan::query()
            ->with(['targets', 'saebEntries', 'procurements'])
            ->where('fiscal_year', $fiscalYear);

        if ($officeName) {
            $query->where('office_name', $officeName);
        }

        $rows = $query
            ->orderBy('program_classification')
            ->orderBy('prexc_code')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (FinancialPlan $p) => [
                'row_type'               => $p->row_type,
                'program_classification' => $p->program_classification,
                'prexc_code'             => $p->prexc_code,
                'staff_unit_project'     => $p->staff_unit_project,
                'specific_activity'      => $p->specific_activity,
                'procurement_status'     => $p->is_procured ? 'OK' : $p->procurement_status,
                'expense_item'           => $p->expense_item,
                'assigned_personnel'     => $p->assigned_personnel,
                'mooe'                   => (float) $p->mooe,
                'capital_outlay'         => (float) $p->capital_outlay,
                'months'                 => $p->monthly_amounts,
                'total'                  => $p->total_target,
                'saeb_balance'           => $p->saeb_balance,
            ]);

        $blocks      = $this->buildPdfBlocks($rows);
        $grandTotals = $this->buildPdfGrandTotals($rows);


        $signatory = FinancialPlanSignatory::where('fiscal_year', $fiscalYear)
            ->where('office_name', $officeName)
            ->first();

        $pdf = Pdf::loadView('financial-plans.pdf', [
            'fiscalYear'  => $fiscalYear,
            'officeName'  => $officeName,
            'months'      => self::MONTHS,
            'blocks'      => $blocks,
            'grandTotals' => $grandTotals,
            'signatory'   => $signatory,
            'generatedAt' => now(),
        ])->setPaper('folio', 'landscape');

        $filename = "FY{$fiscalYear}_Financial_Plan_" . \Illuminate\Support\Str::slug($officeName ?: 'All') . '.pdf';

        return $pdf->download($filename)->withHeaders([
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function buildPdfBlocks($rows): array
    {
        $blocks = [];
        $run = null;

        foreach ($rows as $r) {
            if ($r['row_type'] === 'header') {
                if ($run) {
                    $blocks[] = $run;
                    $run = null;
                }
                $blocks[] = ['type' => 'header', 'row' => $r];
                continue;
            }

            $key = trim($r['program_classification'] ?? '') . '::' . trim($r['prexc_code'] ?? '');

            if (! $run || $run['key'] !== $key) {
                if ($run) {
                    $blocks[] = $run;
                }
                $run = ['type' => 'group', 'key' => $key, 'rows' => []];
            }

            $run['rows'][] = $r;
        }

        if ($run) {
            $blocks[] = $run;
        }

        foreach ($blocks as &$block) {
            if ($block['type'] !== 'group') {
                continue;
            }

            $totals = [
                'mooe'           => 0,
                'capital_outlay' => 0,
                'total'          => 0,
                'months'         => array_fill(1, 12, 0),
            ];

            foreach ($block['rows'] as $r) {
                $totals['mooe'] += $r['mooe'];
                $totals['capital_outlay'] += $r['capital_outlay'];
                $totals['total'] += $r['total'];

                for ($m = 1; $m <= 12; $m++) {
                    $totals['months'][$m] += (float) ($r['months'][$m] ?? 0);
                }
            }

            $block['totals'] = $totals;
        }
        unset($block);

        return $blocks;
    }

    private function buildPdfGrandTotals($rows): array
    {
        $grand = [
            'mooe'           => 0,
            'capital_outlay' => 0,
            'total'          => 0,
            'months'         => array_fill(1, 12, 0),
        ];

        foreach ($rows as $r) {
            if ($r['row_type'] !== 'item') {
                continue;
            }

            $grand['mooe'] += $r['mooe'];
            $grand['capital_outlay'] += $r['capital_outlay'];
            $grand['total'] += $r['total'];

            for ($m = 1; $m <= 12; $m++) {
                $grand['months'][$m] += (float) ($r['months'][$m] ?? 0);
            }
        }

        return $grand;
    }
}
