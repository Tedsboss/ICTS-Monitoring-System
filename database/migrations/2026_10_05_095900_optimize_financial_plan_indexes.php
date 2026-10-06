<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_plans', function (Blueprint $table) {
            $table->index(
                [
                    'fiscal_year',
                    'staff_id',
                    'office_name',
                ],
                'financial_plans_year_staff_office_index'
            );

            $table->dropIndex(
                'financial_plans_fiscal_year_office_name_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('financial_plans', function (Blueprint $table) {
            $table->index(
                [
                    'fiscal_year',
                    'office_name',
                ],
                'financial_plans_fiscal_year_office_name_index'
            );

            $table->dropIndex(
                'financial_plans_year_staff_office_index'
            );
        });
    }
};
