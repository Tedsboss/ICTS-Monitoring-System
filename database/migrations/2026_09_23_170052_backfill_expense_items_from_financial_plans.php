<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $expenseItems = DB::table('financial_plans')
            ->select([
                'fiscal_year',
                'staff_id',
                'expense_item',
            ])
            ->whereNotNull('staff_id')
            ->whereNotNull('expense_item')
            ->whereRaw("TRIM(expense_item) <> ''")
            ->distinct()
            ->get();

        $now = now();

        foreach ($expenseItems as $item) {
            $name = trim((string) $item->expense_item);

            if ($name === '') {
                continue;
            }

            $exists = DB::table('expense_items')
                ->where('fiscal_year', $item->fiscal_year)
                ->where('staff_id', $item->staff_id)
                ->where('name', $name)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('expense_items')->insert([
                'fiscal_year' => $item->fiscal_year,
                'staff_id' => $item->staff_id,
                'name' => $name,
                'is_active' => true,
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        //
        // Intentionally left empty.
        //
        // Expense Items may already be referenced or modified after
        // this migration runs. Automatically deleting them during
        // rollback could remove valid master data.
        //
    }
};