<?php

namespace App\Services;

use App\Models\FinancialPlan;
use App\Models\FinancialPlanAllocation;
use App\Models\FinancialPlanSubmission;
use App\Models\FinancialPlanTarget;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FinancialPlanService
{
    public const NINP_PREXC_CODE = '200000200001000';
    public const MOOE_CO_PREXC_CODE = '100000100001000';

    public function effectiveAmounts(float $mooe, float $capitalOutlay, $contractAmount): array
    {
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
    }

    public function savePlan(
        User $user,
        int $year,
        string $office,
        array $rows,
        ?int $divisionId,
        ?callable $auditFieldChanges = null,
        ?callable $auditRow = null,
    ): array {
        return DB::transaction(function () use (
            $year,
            $office,
            $rows,
            $divisionId,
            $auditFieldChanges,
            $auditRow
        ) {
            $existingIds = FinancialPlan::query()
                ->where('fiscal_year', $year)
                ->where('office_name', $office)
                ->pluck('id')
                ->all();

            $savedIds = [];
            $targetRows = [];
            $sortOrder = 10;
            $now = now();

            foreach ($rows as $row) {
                $plan = null;

                if (! empty($row['id'])) {
                    $plan = FinancialPlan::query()
                        ->whereKey($row['id'])
                        ->where('fiscal_year', $year)
                        ->where('office_name', $office)
                        ->first();
                }

                $isNew = ! $plan;
                $plan ??= new FinancialPlan();

                $incoming = [
                    'fiscal_year' => $year,
                    'office_name' => $office,
                    'division_id' => $divisionId,
                    'row_type' => $row['row_type'],
                    'program_classification' => $row['program_classification'] ?? null,
                    'prexc_code' => $row['prexc_code'] ?? null,
                    'staff_unit_project' => $row['staff_unit_project'] ?? null,
                    'specific_activity' => $row['specific_activity'] ?? null,
                    'procurement_status' => $row['procurement_status'] ?? null,
                    'expense_item' => $row['expense_item'] ?? null,
                    'assigned_personnel' => $row['assigned_personnel'] ?? null,
                    'mooe' => $row['mooe'] ?? 0,
                    'capital_outlay' => $row['capital_outlay'] ?? 0,
                    'contract_amount' => array_key_exists('contract_amount', $row)
                        ? $row['contract_amount']
                        : null,
                    'sort_order' => $sortOrder,
                ];

                if (! $isNew && $auditFieldChanges) {
                    $auditFieldChanges($plan, $incoming);
                }

                $plan->fill($incoming)->save();

                if ($isNew && $auditRow) {
                    $auditRow($plan->id, 'created', $row['row_type']);
                }

                $savedIds[] = $plan->id;

                if ($row['row_type'] === FinancialPlan::TYPE_ITEM) {
                    foreach (range(1, 12) as $month) {
                        $targetRows[] = [
                            'financial_plan_id' => $plan->id,
                            'month' => $month,
                            'amount' => $row['months'][$month] ?? 0,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                } else {
                    FinancialPlanTarget::query()
                        ->where('financial_plan_id', $plan->id)
                        ->delete();
                }

                $sortOrder += 10;
            }

            if ($targetRows !== []) {
                FinancialPlanTarget::upsert(
                    $targetRows,
                    ['financial_plan_id', 'month'],
                    ['amount', 'updated_at']
                );
            }

            $deleteIds = array_values(array_diff($existingIds, $savedIds));

            if ($deleteIds !== []) {
                if ($auditRow) {
                    foreach ($deleteIds as $id) {
                        $auditRow($id, 'deleted', null);
                    }
                }

                FinancialPlan::query()->whereIn('id', $deleteIds)->delete();
            }

            return [
                'saved_ids' => $savedIds,
                'deleted_ids' => $deleteIds,
            ];
        });
    }

    public function totals(int $fiscalYear, string $officeName): array
    {
        $items = FinancialPlan::query()
            ->where('fiscal_year', $fiscalYear)
            ->where('office_name', $officeName)
            ->where('row_type', FinancialPlan::TYPE_ITEM)
            ->get(['mooe', 'capital_outlay', 'contract_amount', 'prexc_code']);

        $mooeSum = 0.0;
        $coSum = 0.0;
        $ninpSum = 0.0;

        foreach ($items as $row) {
            [$effectiveMooe, $effectiveCo] = $this->effectiveAmounts(
                (float) $row->mooe,
                (float) $row->capital_outlay,
                $row->contract_amount
            );

            if ($row->prexc_code === self::MOOE_CO_PREXC_CODE) {
                $mooeSum += $effectiveMooe;
                $coSum += $effectiveCo;
            }

            if ($row->prexc_code === self::NINP_PREXC_CODE) {
                $ninpSum += $effectiveMooe;
            }
        }

        $allocation = FinancialPlanAllocation::query()
            ->where('fiscal_year', $fiscalYear)
            ->where('office_name', $officeName)
            ->first();

        $mooeAllocation = (float) ($allocation->mooe_allocation ?? 0);
        $coAllocation = (float) ($allocation->capital_outlay_allocation ?? 0);
        $ninpAllocation = (float) ($allocation->ninp_allocation ?? 0);

        return [
            'mooe_allocation' => $mooeAllocation,
            'capital_outlay_allocation' => $coAllocation,
            'ninp_allocation' => $ninpAllocation,
            'mooe_sum' => $mooeSum,
            'capital_outlay_sum' => $coSum,
            'ninp_sum' => $ninpSum,
            'mooe_balance' => $mooeAllocation - $mooeSum,
            'capital_outlay_balance' => $coAllocation - $coSum,
            'ninp_balance' => $ninpAllocation - $ninpSum,
        ];
    }

    public function submission(int $year, string $office): ?FinancialPlanSubmission
    {
        return FinancialPlanSubmission::query()
            ->where('fiscal_year', $year)
            ->where('office_name', $office)
            ->first();
    }
}
