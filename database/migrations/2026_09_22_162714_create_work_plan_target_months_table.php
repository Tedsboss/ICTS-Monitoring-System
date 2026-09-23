<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_plan_target_months', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('work_plan_target_id');
            $table->unsignedTinyInteger('month');
            $table->timestamps();

            $table->foreign('work_plan_target_id')
                ->references('id')
                ->on('work_plan_targets')
                ->cascadeOnDelete();

            $table->unique(
                ['work_plan_target_id', 'month'],
                'work_plan_target_month_unique'
            );

            $table->index('month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_plan_target_months');
    }
};