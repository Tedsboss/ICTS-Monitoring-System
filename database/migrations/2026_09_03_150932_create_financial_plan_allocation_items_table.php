<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_plan_allocation_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('financial_plan_allocation_id');
            $table->unsignedBigInteger('allocation_type_id');

            $table->string('expense_category', 50);
            $table->decimal('amount', 18, 2)->default(0);

            $table->timestamps();

            // Allocation header
            $table->foreign(
                'financial_plan_allocation_id',
                'fp_alloc_items_allocation_fk'
            )
                ->references('id')
                ->on('financial_plan_allocations')
                ->cascadeOnDelete();

            // Allocation type
            $table->foreign(
                'allocation_type_id',
                'fp_alloc_items_type_fk'
            )
                ->references('id')
                ->on('financial_plan_allocation_types')
                ->restrictOnDelete();

            // Prevent duplicate allocation entries
            $table->unique(
                [
                    'financial_plan_allocation_id',
                    'allocation_type_id',
                    'expense_category',
                ],
                'fp_alloc_items_unique'
            );

            $table->index(
                [
                    'allocation_type_id',
                    'expense_category',
                ],
                'fp_alloc_items_type_category_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_plan_allocation_items');
    }
};
