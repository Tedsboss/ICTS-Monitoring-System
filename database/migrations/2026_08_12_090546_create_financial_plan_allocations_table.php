<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_plan_allocations', function (Blueprint $table) {
            $table->id();
            $table->integer('fiscal_year');
            $table->string('office_name', 150);
            $table->decimal('mooe_allocation', 15, 2)->default(0);
            $table->decimal('capital_outlay_allocation', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['fiscal_year', 'office_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_plan_allocations');
    }
};