<?php

namespace Database\Seeders;

use App\Models\FinancialPlanAllocationType;
use Illuminate\Database\Seeder;

class FinancialPlanAllocationTypeSeeder extends Seeder
{
    public function run(): void
    {
        // One-time ICTS master-data initialization.
        // staff_id 43 is the confirmed ICTS staff-level office.
        //
        // Do not use this hard-coded ID in normal application
        // authorization, Builder filtering, or Controller logic.
        $staffId = 43;

        FinancialPlanAllocationType::updateOrCreate(
            [
                'staff_id' => $staffId,
                'code' => 'mithi',
            ],
            [
                'name' => 'MITHI',
                'allows_mooe' => true,
                'allows_capital_outlay' => true,
                'sort_order' => 10,
                'is_active' => true,
            ]
        );

        FinancialPlanAllocationType::updateOrCreate(
            [
                'staff_id' => $staffId,
                'code' => 'ninp',
            ],
            [
                'name' => 'NINP',
                'allows_mooe' => true,
                'allows_capital_outlay' => false,
                'sort_order' => 20,
                'is_active' => true,
            ]
        );
    }
}
