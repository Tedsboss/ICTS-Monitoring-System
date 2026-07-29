<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saeb', function (Blueprint $table) {
            $table->foreign('financial_plan_item_id')
                  ->references('id')->on('financial_plans')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('saeb', function (Blueprint $table) {
            $table->dropForeign(['financial_plan_item_id']);
        });
    }
};