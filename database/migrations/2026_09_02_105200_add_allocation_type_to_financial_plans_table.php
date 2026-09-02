<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_plans', function (Blueprint $table) {
            $table->string('allocation_type', 20)
                ->nullable()
                ->after('prexc_code')
                ->index();
        });

        // Preserve the previous allocation behavior for existing records.
        //
        // This is only a one-time legacy backfill.
        // Future allocation classification must use allocation_type directly
        // instead of deriving it from PREXC.
        DB::table('financial_plans')
            ->where('row_type', 'item')
            ->where('prexc_code', '100000100001000')
            ->update([
                'allocation_type' => 'mithi',
            ]);

        DB::table('financial_plans')
            ->where('row_type', 'item')
            ->where('prexc_code', '200000200001000')
            ->update([
                'allocation_type' => 'ninp',
            ]);
    }

    public function down(): void
    {
        Schema::table('financial_plans', function (Blueprint $table) {
            $table->dropIndex(['allocation_type']);
            $table->dropColumn('allocation_type');
        });
    }
};
