<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_plans', function (Blueprint $table) {
            $table->foreignId('allocation_id')
                ->nullable()
                ->after('fiscal_year')
                ->constrained('allocations')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->index(
                ['allocation_id', 'fiscal_year', 'office_name'],
                'financial_plans_allocation_year_office_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('financial_plans', function (Blueprint $table) {
            $table->dropForeign(['allocation_id']);
            $table->dropIndex('financial_plans_allocation_year_office_index');
            $table->dropColumn('allocation_id');
        });
    }
};