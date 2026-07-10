<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            if (!Schema::hasColumn('forms', 'assigned_sector_id')) {
                $table->integer('assigned_sector_id')->nullable()->after('template_source_form_id')->index();
                $table->foreign('assigned_sector_id')->references('id')->on('staffs')->nullOnDelete();
            }
        });

        Schema::table('uplift_measures', function (Blueprint $table) {
            if (!Schema::hasColumn('uplift_measures', 'assigned_sector_id')) {
                $table->integer('assigned_sector_id')->nullable()->after('lead_agency_id')->index();
                $table->foreign('assigned_sector_id')->references('id')->on('staffs')->nullOnDelete();
            }
        });

        foreach (['form_submissions', 'uplift_submissions'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'approved_by')) {
                    $table->unsignedInteger('approved_by')->nullable()->after('submitted_at')->index();
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                    $table->unsignedInteger('returned_by')->nullable()->after('approved_at')->index();
                    $table->timestamp('returned_at')->nullable()->after('returned_by');
                    $table->unsignedInteger('rejected_by')->nullable()->after('returned_at')->index();
                    $table->timestamp('rejected_at')->nullable()->after('rejected_by');
                    $table->text('approval_remarks')->nullable()->after('rejected_at');

                    $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
                    $table->foreign('returned_by')->references('id')->on('users')->nullOnDelete();
                    $table->foreign('rejected_by')->references('id')->on('users')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['form_submissions', 'uplift_submissions'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'approved_by')) {
                    $table->dropForeign([$tableName === 'form_submissions' ? 'approved_by' : 'approved_by']);
                    $table->dropForeign([$tableName === 'form_submissions' ? 'returned_by' : 'returned_by']);
                    $table->dropForeign([$tableName === 'form_submissions' ? 'rejected_by' : 'rejected_by']);
                    $table->dropColumn([
                        'approved_by',
                        'approved_at',
                        'returned_by',
                        'returned_at',
                        'rejected_by',
                        'rejected_at',
                        'approval_remarks',
                    ]);
                }
            });
        }

        Schema::table('uplift_measures', function (Blueprint $table) {
            if (Schema::hasColumn('uplift_measures', 'assigned_sector_id')) {
                $table->dropForeign(['assigned_sector_id']);
                $table->dropColumn('assigned_sector_id');
            }
        });

        Schema::table('forms', function (Blueprint $table) {
            if (Schema::hasColumn('forms', 'assigned_sector_id')) {
                $table->dropForeign(['assigned_sector_id']);
                $table->dropColumn('assigned_sector_id');
            }
        });
    }
};
