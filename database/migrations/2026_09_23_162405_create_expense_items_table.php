<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('fiscal_year');
            $table->integer('staff_id');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('staff_id')
                ->references('id')
                ->on('staffs');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->unique(
                ['fiscal_year', 'staff_id', 'name'],
                'expense_items_year_staff_name_unique'
            );

            $table->index(
                ['fiscal_year', 'staff_id', 'is_active'],
                'expense_items_scope_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_items');
    }
};