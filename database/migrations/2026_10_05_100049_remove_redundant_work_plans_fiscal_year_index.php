<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_plans', function (Blueprint $table) {
            $table->dropIndex(
                'work_plans_fiscal_year_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('work_plans', function (Blueprint $table) {
            $table->index(
                'fiscal_year',
                'work_plans_fiscal_year_index'
            );
        });
    }
};
