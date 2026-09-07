<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Load existing allocation headers
        $allocations = DB::table('financial_plan_allocations')
            ->whereNotNull('staff_id')
            ->orderBy('id')
            ->get();

        foreach ($allocations as $allocation) {
            $staffId = (int) $allocation->staff_id;

            // Find MITHI configured for this Staff/Office
            $mithiType = DB::table('financial_plan_allocation_types')
                ->where('staff_id', $staffId)
                ->whereRaw('LOWER(code) = ?', ['mithi'])
                ->first();

            // Find NINP configured for this Staff/Office
            $ninpType = DB::table('financial_plan_allocation_types')
                ->where('staff_id', $staffId)
                ->whereRaw('LOWER(code) = ?', ['ninp'])
                ->first();

            // Backfill MITHI MOOE
            if ($mithiType && (float) $allocation->mooe_allocation != 0) {
                $this->upsertAllocationItem(
                    (int) $allocation->id,
                    (int) $mithiType->id,
                    'mooe',
                    $allocation->mooe_allocation
                );
            }

            // Backfill MITHI Capital Outlay
            if ($mithiType && (float) $allocation->capital_outlay_allocation != 0) {
                $this->upsertAllocationItem(
                    (int) $allocation->id,
                    (int) $mithiType->id,
                    'capital_outlay',
                    $allocation->capital_outlay_allocation
                );
            }

            // Backfill NINP MOOE
            if ($ninpType && (float) $allocation->ninp_allocation != 0) {
                $this->upsertAllocationItem(
                    (int) $allocation->id,
                    (int) $ninpType->id,
                    'mooe',
                    $allocation->ninp_allocation
                );
            }
        }
    }

    public function down(): void
    {
        // Remove only legacy MITHI/NINP records created by this transition
        $legacyTypeIds = DB::table('financial_plan_allocation_types')
            ->whereIn(
                DB::raw('LOWER(code)'),
                ['mithi', 'ninp']
            )
            ->pluck('id');

        if ($legacyTypeIds->isEmpty()) {
            return;
        }

        DB::table('financial_plan_allocation_items')
            ->whereIn('allocation_type_id', $legacyTypeIds)
            ->whereIn('expense_category', [
                'mooe',
                'capital_outlay',
            ])
            ->delete();
    }

    // Create or update one generic allocation amount
    private function upsertAllocationItem(
        int $allocationId,
        int $allocationTypeId,
        string $expenseCategory,
        $amount
    ): void {
        DB::table('financial_plan_allocation_items')->updateOrInsert(
            [
                'financial_plan_allocation_id' => $allocationId,
                'allocation_type_id' => $allocationTypeId,
                'expense_category' => $expenseCategory,
            ],
            [
                'amount' => (float) $amount,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
};
