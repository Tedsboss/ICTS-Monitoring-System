<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_plans', function (Blueprint $table) {
            $table->index(['fiscal_year', 'office_name', 'row_type'], 'fp_year_office_type_idx');
            $table->index('prexc_code', 'fp_prexc_code_idx');
        });

        Schema::table('financial_plan_allocations', function (Blueprint $table) {
            if (! Schema::hasColumn('financial_plan_allocations', 'division_id')) {
                $table->integer('division_id')->nullable()->after('office_name');
                $table->index('division_id', 'fpa_division_id_idx');
                $table->foreign('division_id', 'fpa_division_id_fk')
                    ->references('id')->on('divisions')
                    ->restrictOnUpdate()->restrictOnDelete();
            }
        });

        Schema::table('financial_plan_submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('financial_plan_submissions', 'division_id')) {
                $table->integer('division_id')->nullable()->after('office_name');
                $table->index('division_id', 'fpsub_division_id_idx');
                $table->foreign('division_id', 'fpsub_division_id_fk')
                    ->references('id')->on('divisions')
                    ->restrictOnUpdate()->restrictOnDelete();
            }

            if (! Schema::hasColumn('financial_plan_submissions', 'status')) {
                $table->enum('status', ['draft', 'submitted', 'returned', 'approved', 'finalized'])
                    ->default('draft')
                    ->after('division_id');
            }

            if (! Schema::hasColumn('financial_plan_submissions', 'finalized_by')) {
                $table->unsignedInteger('finalized_by')->nullable()->after('approved_at');
                $table->foreign('finalized_by', 'financial_plan_submissions_finalized_by_foreign')
                    ->references('id')->on('users')
                    ->restrictOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('financial_plan_submissions', 'finalized_at')) {
                $table->timestamp('finalized_at')->nullable()->after('finalized_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('financial_plan_submissions', function (Blueprint $table) {
            if (Schema::hasColumn('financial_plan_submissions', 'finalized_by')) {
                $table->dropForeign('financial_plan_submissions_finalized_by_foreign');
                $table->dropColumn('finalized_by');
            }
            if (Schema::hasColumn('financial_plan_submissions', 'finalized_at')) {
                $table->dropColumn('finalized_at');
            }
            if (Schema::hasColumn('financial_plan_submissions', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('financial_plan_submissions', 'division_id')) {
                $table->dropForeign('fpsub_division_id_fk');
                $table->dropIndex('fpsub_division_id_idx');
                $table->dropColumn('division_id');
            }
        });

        Schema::table('financial_plan_allocations', function (Blueprint $table) {
            if (Schema::hasColumn('financial_plan_allocations', 'division_id')) {
                $table->dropForeign('fpa_division_id_fk');
                $table->dropIndex('fpa_division_id_idx');
                $table->dropColumn('division_id');
            }
        });

        Schema::table('financial_plans', function (Blueprint $table) {
            $table->dropIndex('fp_year_office_type_idx');
            $table->dropIndex('fp_prexc_code_idx');
        });
    }
};
