<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_plan_workflow_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('fiscal_year');
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->string('office_name', 150);
            $table->string('action', 30);
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('acted_by')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();

            $table->index(
                ['fiscal_year', 'staff_id', 'office_name'],
                'fp_workflow_scope_index'
            );
            $table->index(['action', 'acted_at'], 'fp_workflow_action_date_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_plan_workflow_histories');
    }
};
