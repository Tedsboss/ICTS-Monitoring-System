<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_plan_signatories', function (Blueprint $table) {
            $table->string('prepared_by_position', 150)->nullable()->after('prepared_by');
            $table->string('reviewed_by_position', 150)->nullable()->after('reviewed_by');
            $table->string('recommended_by_position', 150)->nullable()->after('recommended_by');
            $table->string('approved_by_position', 150)->nullable()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('financial_plan_signatories', function (Blueprint $table) {
            $table->dropColumn([
                'prepared_by_position',
                'reviewed_by_position',
                'recommended_by_position',
                'approved_by_position',
            ]);
        });
    }
};
