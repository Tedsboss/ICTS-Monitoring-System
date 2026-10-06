<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_plan_submissions', function (Blueprint $table) {
            $table->index(
                [
                    'work_plan_id',
                    'acted_at',
                    'id',
                ],
                'work_plan_submissions_plan_history_index'
            );

            $table->dropIndex(
                'work_plan_submissions_work_plan_id_index'
            );

            $table->dropIndex(
                'work_plan_submissions_acted_at_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('work_plan_submissions', function (Blueprint $table) {
            $table->index(
                'work_plan_id',
                'work_plan_submissions_work_plan_id_index'
            );

            $table->index(
                'acted_at',
                'work_plan_submissions_acted_at_index'
            );

            $table->dropIndex(
                'work_plan_submissions_plan_history_index'
            );
        });
    }
};
