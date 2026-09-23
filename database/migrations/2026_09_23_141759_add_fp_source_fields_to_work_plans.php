<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('work_plans', 'division_id')) {
            Schema::table('work_plans', function (Blueprint $table) {
                $table->unsignedBigInteger('division_id')
                    ->nullable()
                    ->after('staff_id');

                $table->foreign('division_id')
                    ->references('id')
                    ->on('divisions')
                    ->nullOnDelete();

                $table->index(
                    ['fiscal_year', 'staff_id', 'division_id'],
                    'work_plans_scope_index'
                );
            });
        }

        if (! Schema::hasColumn('work_plan_items', 'financial_plan_id')) {
            Schema::table('work_plan_items', function (Blueprint $table) {
                $table->unsignedBigInteger('financial_plan_id')
                    ->nullable()
                    ->after('work_plan_id');

                $table->foreign('financial_plan_id')
                    ->references('id')
                    ->on('financial_plans')
                    ->nullOnDelete();

                $table->index(
                    'financial_plan_id',
                    'work_plan_items_financial_plan_index'
                );
            });
        }

        if (! Schema::hasColumn('work_plan_items', 'program_classification')) {
            Schema::table('work_plan_items', function (Blueprint $table) {
                $table->string('program_classification')
                    ->nullable()
                    ->after('classification_id');
            });
        }

        if (! Schema::hasColumn('work_plan_items', 'prexc_code')) {
            Schema::table('work_plan_items', function (Blueprint $table) {
                $table->string('prexc_code', 100)
                    ->nullable()
                    ->after('program_classification');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('work_plan_items', 'financial_plan_id')) {
            Schema::table('work_plan_items', function (Blueprint $table) {
                $table->dropForeign(['financial_plan_id']);
                $table->dropIndex('work_plan_items_financial_plan_index');
            });
        }

        if (Schema::hasColumn('work_plan_items', 'prexc_code')) {
            Schema::table('work_plan_items', function (Blueprint $table) {
                $table->dropColumn('prexc_code');
            });
        }

        if (Schema::hasColumn('work_plan_items', 'program_classification')) {
            Schema::table('work_plan_items', function (Blueprint $table) {
                $table->dropColumn('program_classification');
            });
        }

        if (Schema::hasColumn('work_plan_items', 'financial_plan_id')) {
            Schema::table('work_plan_items', function (Blueprint $table) {
                $table->dropColumn('financial_plan_id');
            });
        }
    }
};