<?php

namespace Database\Seeders;

use App\Models\ExpenseType;
use App\Models\FiscalYear;
use App\Models\Level;
use Illuminate\Database\Seeder;

class AllocationManagementSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([2026, 2027] as $year) {
            FiscalYear::updateOrCreate(
                ['year' => $year],
                ['year' => $year]
            );
        }

        $levels = [
            ['level_code' => 'Tier1', 'level_description' => 'Tier One'],
            ['level_code' => 'Tier2', 'level_description' => 'Tier Two'],
            ['level_code' => 'Nep', 'level_description' => 'NEP Level'],
            ['level_code' => 'Gaa', 'level_description' => 'GAA Level'],
            ['level_code' => 'Cont', 'level_description' => 'Continuing'],
        ];

        foreach ($levels as $level) {
            Level::updateOrCreate(
                ['level_code' => $level['level_code']],
                ['level_description' => $level['level_description']]
            );
        }

        $expenseTypes = [
            ['type' => 'MOOE', 'expense_description' => 'Operating Expenses'],
            ['type' => 'MOOE', 'expense_description' => 'Travel'],
            ['type' => 'MOOE', 'expense_description' => 'Training'],
            ['type' => 'MOOE', 'expense_description' => 'Supplies and Materials'],
            ['type' => 'CO', 'expense_description' => 'ICT Equipment'],
            ['type' => 'CO', 'expense_description' => 'ICT Infrastructure'],
        ];

        foreach ($expenseTypes as $expenseType) {
            ExpenseType::updateOrCreate(
                [
                    'type' => $expenseType['type'],
                    'expense_description' => $expenseType['expense_description'],
                ],
                $expenseType
            );
        }
    }
}