<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_plan_allocation_types', function (Blueprint $table) {
            $table->id();

            // Staff/Office that owns this Allocation Type.
            // This keeps Allocation Types configurable per staff-level office.
            $table->unsignedBigInteger('staff_id');

            // Stable internal code stored in financial_plans.allocation_type.
            // Examples for ICTS: mithi, ninp.
            $table->string('code', 50);

            // User-facing name shown in the Financial Plan Builder.
            $table->string('name', 150);

            // Determines which expense categories are allowed.
            $table->boolean('allows_mooe')->default(true);
            $table->boolean('allows_capital_outlay')->default(false);

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // The same code cannot be duplicated inside one Staff/Office.
            $table->unique(
                ['staff_id', 'code'],
                'fp_allocation_types_staff_code_unique'
            );

            $table->index(
                ['staff_id', 'is_active', 'sort_order'],
                'fp_allocation_types_staff_active_sort_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_plan_allocation_types');
    }
};
