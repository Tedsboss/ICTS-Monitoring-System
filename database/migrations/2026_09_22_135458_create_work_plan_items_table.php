<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_plan_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('work_plan_id');
            $table->unsignedBigInteger('classification_id');
            $table->text('specific_activity');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('work_plan_id');
            $table->index('classification_id');
            $table->index('sort_order');

            $table->foreign('work_plan_id')
                ->references('id')
                ->on('work_plans')
                ->onDelete('cascade');

            $table->foreign('classification_id')
                ->references('id')
                ->on('work_plan_classifications')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_plan_items');
    }
};
