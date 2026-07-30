<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_plan_signatories', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('fiscal_year');
            $table->string('office_name', 150);
            $table->string('prepared_by', 150)->nullable();
            $table->string('reviewed_by', 150)->nullable();
            $table->string('recommended_by', 150)->nullable();
            $table->string('approved_by', 150)->nullable();
            $table->timestamps();

            // One signatory set per WFP (fiscal_year + office_name) — matches
            // how FinancialPlan rows themselves are scoped in the controller.
            $table->unique(['fiscal_year', 'office_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_plan_signatories');
    }
};
