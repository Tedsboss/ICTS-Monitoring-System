<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allocation_expenses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('allocation_id')
                ->constrained('allocations')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('expense_id')
                ->constrained('expense_types')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->decimal('cost', 18, 2)->default(0);

            $table->timestamps();

            $table->unique(
                ['allocation_id', 'expense_id'],
                'allocation_expenses_allocation_expense_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allocation_expenses');
    }
};