<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add staff ownership to Financial Plan rows
        if (! Schema::hasColumn('financial_plans', 'staff_id')) {
            Schema::table('financial_plans', function (Blueprint $table) {
                $table->unsignedBigInteger('staff_id')
                    ->nullable()
                    ->after('office_name')
                    ->index();
            });
        }

        // Add staff ownership to allocation records
        if (! Schema::hasColumn('financial_plan_allocations', 'staff_id')) {
            Schema::table('financial_plan_allocations', function (Blueprint $table) {
                $table->unsignedBigInteger('staff_id')
                    ->nullable()
                    ->after('office_name')
                    ->index();
            });
        }

        // Add staff ownership to signatory records
        if (! Schema::hasColumn('financial_plan_signatories', 'staff_id')) {
            Schema::table('financial_plan_signatories', function (Blueprint $table) {
                $table->unsignedBigInteger('staff_id')
                    ->nullable()
                    ->after('office_name')
                    ->index();
            });
        }

        // Add staff ownership to workflow submission records
        if (! Schema::hasColumn('financial_plan_submissions', 'staff_id')) {
            Schema::table('financial_plan_submissions', function (Blueprint $table) {
                $table->unsignedBigInteger('staff_id')
                    ->nullable()
                    ->after('office_name')
                    ->index();
            });
        }

        // Backfill the existing ICTS WFP.
        // Both authorized Planning and Finance Staff users belong to staff_id 43.
        $ictsOfficeName = 'Information and Communications Technology Staff (ICTS)';
        $ictsStaffId = 43;

        DB::table('financial_plans')
            ->where('office_name', $ictsOfficeName)
            ->whereNull('staff_id')
            ->update([
                'staff_id' => $ictsStaffId,
            ]);

        DB::table('financial_plan_allocations')
            ->where('office_name', $ictsOfficeName)
            ->whereNull('staff_id')
            ->update([
                'staff_id' => $ictsStaffId,
            ]);

        DB::table('financial_plan_signatories')
            ->where('office_name', $ictsOfficeName)
            ->whereNull('staff_id')
            ->update([
                'staff_id' => $ictsStaffId,
            ]);

        DB::table('financial_plan_submissions')
            ->where('office_name', $ictsOfficeName)
            ->whereNull('staff_id')
            ->update([
                'staff_id' => $ictsStaffId,
            ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('financial_plan_submissions', 'staff_id')) {
            Schema::table('financial_plan_submissions', function (Blueprint $table) {
                $table->dropIndex(['staff_id']);
                $table->dropColumn('staff_id');
            });
        }

        if (Schema::hasColumn('financial_plan_signatories', 'staff_id')) {
            Schema::table('financial_plan_signatories', function (Blueprint $table) {
                $table->dropIndex(['staff_id']);
                $table->dropColumn('staff_id');
            });
        }

        if (Schema::hasColumn('financial_plan_allocations', 'staff_id')) {
            Schema::table('financial_plan_allocations', function (Blueprint $table) {
                $table->dropIndex(['staff_id']);
                $table->dropColumn('staff_id');
            });
        }

        if (Schema::hasColumn('financial_plans', 'staff_id')) {
            Schema::table('financial_plans', function (Blueprint $table) {
                $table->dropIndex(['staff_id']);
                $table->dropColumn('staff_id');
            });
        }
    }
};
