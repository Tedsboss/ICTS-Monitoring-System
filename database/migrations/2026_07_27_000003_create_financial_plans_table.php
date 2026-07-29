<?php
// database/migrations/2026_07_27_000001_create_financial_plans_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()
                ->constrained('financial_plans')->nullOnDelete();

            $table->unsignedSmallInteger('fiscal_year');
            $table->string('office_name', 150);

            $table->string('program_classification', 500)->nullable(); // (a)
            $table->string('prexc_code', 50)->nullable();              // (b)
            $table->string('staff_unit_project', 150)->nullable();     // (c)
            $table->text('specific_activity')->nullable();             // (d)
            $table->string('procurement_status', 150)->nullable();     // (e)
            $table->string('expense_item', 150)->nullable();
            $table->string('assigned_personnel', 150)->nullable();

            $table->decimal('mooe', 14, 2)->default(0);
            $table->decimal('capital_outlay', 14, 2)->default(0);

            $table->enum('row_type', ['group', 'item'])->default('item');
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['fiscal_year', 'office_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_plans');
    }
};
