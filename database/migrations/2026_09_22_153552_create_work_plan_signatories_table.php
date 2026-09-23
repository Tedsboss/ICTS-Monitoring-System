<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_plan_signatories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('work_plan_id')->unique();
            $table->string('prepared_by', 150)->nullable();
            $table->string('prepared_by_position', 150)->nullable();
            $table->string('reviewed_by', 150)->nullable();
            $table->string('reviewed_by_position', 150)->nullable();
            $table->string('recommended_by', 150)->nullable();
            $table->string('recommended_by_position', 150)->nullable();
            $table->string('approved_by', 150)->nullable();
            $table->string('approved_by_position', 150)->nullable();
            $table->timestamps();

            $table->foreign('work_plan_id')
                ->references('id')
                ->on('work_plans')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_plan_signatories');
    }
};