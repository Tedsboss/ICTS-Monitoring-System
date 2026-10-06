<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_plan_submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('financial_plan_submissions', 'reopen_remarks')) {
                $table->text('reopen_remarks')
                    ->nullable()
                    ->after('return_remarks');
            }
        });
    }

    public function down(): void
    {
        Schema::table('financial_plan_submissions', function (Blueprint $table) {
            if (Schema::hasColumn('financial_plan_submissions', 'reopen_remarks')) {
                $table->dropColumn('reopen_remarks');
            }
        });
    }
};
