<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('work_plan_targets')
            ->whereNotNull('month')
            ->orderBy('id')
            ->chunkById(500, function ($targets) {
                $rows = [];

                foreach ($targets as $target) {
                    $month = (int) $target->month;

                    if ($month < 1 || $month > 12) {
                        continue;
                    }

                    $rows[] = [
                        'work_plan_target_id' => $target->id,
                        'month' => $month,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if ($rows) {
                    DB::table('work_plan_target_months')
                        ->insertOrIgnore($rows);
                }
            });
    }

    public function down(): void
    {
        DB::table('work_plan_target_months')->delete();
    }
};