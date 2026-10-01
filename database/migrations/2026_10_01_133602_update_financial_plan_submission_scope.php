<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('financial_plan_submissions')) {
            return;
        }

        Schema::table('financial_plan_submissions', function (Blueprint $table) {
            $table->dropUnique(
                'financial_plan_submissions_fiscal_year_office_name_unique'
            );

            $table->unique(
                ['fiscal_year', 'staff_id', 'office_name'],
                'financial_plan_submissions_year_staff_office_unique'
            );
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('financial_plan_submissions')) {
            return;
        }

        Schema::table('financial_plan_submissions', function (Blueprint $table) {
            $table->dropUnique(
                'financial_plan_submissions_year_staff_office_unique'
            );

            $table->unique(
                ['fiscal_year', 'office_name'],
                'financial_plan_submissions_fiscal_year_office_name_unique'
            );
        });
    }
};
