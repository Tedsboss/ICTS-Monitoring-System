<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_plan_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('work_plan_id');
            $table->unsignedBigInteger('staff_id');
            $table->string('action', 30);
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('acted_by');
            $table->timestamp('acted_at');
            $table->timestamps();

            $table->index('work_plan_id');
            $table->index('staff_id');
            $table->index('action');
            $table->index('acted_by');
            $table->index('acted_at');

            $table->foreign('work_plan_id')
                ->references('id')
                ->on('work_plans')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_plan_submissions');
    }
};
