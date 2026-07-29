<?php
// database/migrations/2026_07_27_000002_create_financial_plan_targets_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_plan_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_plan_id')
                ->constrained('financial_plans')->cascadeOnDelete();

            $table->unsignedTinyInteger('month'); // 1 = Jan ... 12 = Dec
            $table->decimal('amount', 14, 2)->default(0);

            $table->timestamps();

            $table->unique(['financial_plan_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_plan_targets');
    }
};
