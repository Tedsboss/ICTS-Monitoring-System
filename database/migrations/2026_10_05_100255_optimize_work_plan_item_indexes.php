<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_plan_items', function (Blueprint $table) {
            $table->index(
                [
                    'work_plan_id',
                    'sort_order',
                ],
                'work_plan_items_plan_sort_index'
            );

            $table->dropIndex(
                'work_plan_items_work_plan_id_index'
            );

            $table->dropIndex(
                'work_plan_items_sort_order_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('work_plan_items', function (Blueprint $table) {
            $table->index(
                'work_plan_id',
                'work_plan_items_work_plan_id_index'
            );

            $table->index(
                'sort_order',
                'work_plan_items_sort_order_index'
            );

            $table->dropIndex(
                'work_plan_items_plan_sort_index'
            );
        });
    }
};
