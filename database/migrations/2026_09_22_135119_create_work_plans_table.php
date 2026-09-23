<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('fiscal_year');
            $table->unsignedBigInteger('staff_id');
            $table->string('status', 30)->default('draft');
            $table->string('finalized', 10)->default('no');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->unsignedBigInteger('finalized_by')->nullable();
            $table->timestamps();

            $table->unique(
                ['fiscal_year', 'staff_id'],
                'work_plans_fiscal_year_staff_unique'
            );

            $table->index('fiscal_year');
            $table->index('staff_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_plans');
    }
};
