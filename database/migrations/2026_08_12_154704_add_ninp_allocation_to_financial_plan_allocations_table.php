<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_plan_allocations', function (Blueprint $table) {
            $table->decimal('ninp_allocation', 15, 2)
                ->default(0)
                ->after('capital_outlay_allocation');
        });
    }

    public function down(): void
    {
        Schema::table('financial_plan_allocations', function (Blueprint $table) {
            $table->dropColumn('ninp_allocation');
        });
    }
};