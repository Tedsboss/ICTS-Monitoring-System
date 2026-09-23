<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_plan_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('work_plan_item_id');
            $table->unsignedTinyInteger('month');
            $table->text('target_output');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('work_plan_item_id');
            $table->index('month');
            $table->index('sort_order');

            $table->foreign('work_plan_item_id')
                ->references('id')
                ->on('work_plan_items')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_plan_targets');
    }
};
