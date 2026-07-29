<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurements', function (Blueprint $table) {
            $table->foreignId('financial_plan_item_id')
                ->nullable()
                ->after('id')
                ->constrained('financial_plans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('procurements', function (Blueprint $table) {
            $table->dropForeign(['financial_plan_item_id']);
            $table->dropColumn('financial_plan_item_id');
        });
    }
};