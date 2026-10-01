<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_plans', function (Blueprint $table) {
            $table->dropIndex('financial_plans_allocation_type_index');
            $table->dropColumn('allocation_type');
        });

        Schema::dropIfExists('financial_plan_allocation_items');
        Schema::dropIfExists('financial_plan_allocation_types');
        Schema::dropIfExists('financial_plan_allocations');
    }

    public function down(): void
    {
        Schema::create('financial_plan_allocations', function (Blueprint $table) {
            $table->id();
            $table->integer('fiscal_year');
            $table->string('office_name', 150);
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->integer('division_id')->nullable();
            $table->decimal('mooe_allocation', 15, 2)->default(0);
            $table->decimal('capital_outlay_allocation', 15, 2)->default(0);
            $table->decimal('ninp_allocation', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(
                ['fiscal_year', 'office_name'],
                'financial_plan_allocations_fiscal_year_office_name_unique'
            );
            $table->index('division_id', 'fpa_division_id_idx');
            $table->index('staff_id', 'financial_plan_allocations_staff_id_index');

            $table->foreign('division_id', 'fpa_division_id_fk')
                ->references('id')
                ->on('divisions')
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });

        Schema::create('financial_plan_allocation_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->string('code', 50);
            $table->string('name', 150);
            $table->boolean('allows_mooe')->default(true);
            $table->boolean('allows_capital_outlay')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(
                ['staff_id', 'code'],
                'fp_allocation_types_staff_code_unique'
            );
            $table->index(
                ['staff_id', 'is_active', 'sort_order'],
                'fp_allocation_types_staff_active_sort_index'
            );
        });

        Schema::create('financial_plan_allocation_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('financial_plan_allocation_id');
            $table->unsignedBigInteger('allocation_type_id');
            $table->string('expense_category', 50);
            $table->decimal('amount', 18, 2)->default(0);
            $table->timestamps();

            $table->unique(
                [
                    'financial_plan_allocation_id',
                    'allocation_type_id',
                    'expense_category',
                ],
                'fp_alloc_items_unique'
            );
            $table->index(
                ['allocation_type_id', 'expense_category'],
                'fp_alloc_items_type_category_idx'
            );

            $table->foreign(
                'financial_plan_allocation_id',
                'fp_alloc_items_allocation_fk'
            )
                ->references('id')
                ->on('financial_plan_allocations')
                ->cascadeOnDelete()
                ->restrictOnUpdate();

            $table->foreign(
                'allocation_type_id',
                'fp_alloc_items_type_fk'
            )
                ->references('id')
                ->on('financial_plan_allocation_types')
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });
    }
};