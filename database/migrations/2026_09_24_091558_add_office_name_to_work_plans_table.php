<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_plans', function (Blueprint $table) {
            $table->string('office_name', 150)
                ->nullable()
                ->after('staff_id');
        });

        /*
         * Populate existing Work Plans using the Staff name.
         * This preserves existing records.
         */
        DB::statement("
            UPDATE work_plans wp
            INNER JOIN staffs s ON s.id = wp.staff_id
            SET wp.office_name = s.name
            WHERE wp.office_name IS NULL
               OR TRIM(wp.office_name) = ''
        ");

        Schema::table('work_plans', function (Blueprint $table) {
            $table->dropUnique(
                'work_plans_fiscal_year_staff_unique'
            );

            $table->unique(
                ['fiscal_year', 'staff_id', 'office_name'],
                'work_plans_year_staff_office_unique'
            );

            $table->index(
                ['fiscal_year', 'office_name'],
                'work_plans_year_office_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('work_plans', function (Blueprint $table) {
            $table->dropUnique(
                'work_plans_year_staff_office_unique'
            );

            $table->dropIndex(
                'work_plans_year_office_index'
            );

            $table->unique(
                ['fiscal_year', 'staff_id'],
                'work_plans_fiscal_year_staff_unique'
            );

            $table->dropColumn('office_name');
        });
    }
};