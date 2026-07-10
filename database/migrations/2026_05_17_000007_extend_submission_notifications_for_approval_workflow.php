<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('submission_notifications')) {
            return;
        }

        DB::statement('ALTER TABLE submission_notifications MODIFY form_submission_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE submission_notifications MODIFY form_id BIGINT UNSIGNED NULL');

        Schema::table('submission_notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('submission_notifications', 'submission_type')) {
                $table->string('submission_type')->default('indicator')->after('id')->index();
            }

            if (!Schema::hasColumn('submission_notifications', 'uplift_submission_id')) {
                $table->unsignedBigInteger('uplift_submission_id')->nullable()->after('form_submission_id')->index();
                $table->foreign('uplift_submission_id')->references('id')->on('uplift_submissions')->cascadeOnDelete();
            }

            if (!Schema::hasColumn('submission_notifications', 'uplift_measure_id')) {
                $table->unsignedBigInteger('uplift_measure_id')->nullable()->after('form_id')->index();
                $table->foreign('uplift_measure_id')->references('id')->on('uplift_measures')->cascadeOnDelete();
            }

            if (!Schema::hasColumn('submission_notifications', 'recipient_user_id')) {
                $table->unsignedInteger('recipient_user_id')->nullable()->after('agency_id')->index();
            }

            if (!Schema::hasColumn('submission_notifications', 'action')) {
                $table->string('action')->nullable()->after('message')->index();
            }

            if (!Schema::hasColumn('submission_notifications', 'remarks')) {
                $table->text('remarks')->nullable()->after('action');
            }
        });

        if (Schema::hasColumn('submission_notifications', 'recipient_user_id')) {
            DB::statement('ALTER TABLE submission_notifications MODIFY recipient_user_id INT UNSIGNED NULL');
        }

        if (!$this->foreignKeyExists('submission_notifications', 'submission_notifications_recipient_user_id_foreign')) {
            Schema::table('submission_notifications', function (Blueprint $table) {
                $table->foreign('recipient_user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('submission_notifications')) {
            return;
        }

        Schema::table('submission_notifications', function (Blueprint $table) {
            if (Schema::hasColumn('submission_notifications', 'uplift_submission_id')) {
                $table->dropForeign(['uplift_submission_id']);
            }

            if (Schema::hasColumn('submission_notifications', 'uplift_measure_id')) {
                $table->dropForeign(['uplift_measure_id']);
            }

            if (Schema::hasColumn('submission_notifications', 'recipient_user_id')) {
                $table->dropForeign(['recipient_user_id']);
            }
        });

        Schema::table('submission_notifications', function (Blueprint $table) {
            foreach (['remarks', 'action', 'recipient_user_id', 'uplift_measure_id', 'uplift_submission_id', 'submission_type'] as $column) {
                if (Schema::hasColumn('submission_notifications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        return collect(DB::select(
            'select constraint_name from information_schema.table_constraints where table_schema = database() and table_name = ? and constraint_name = ?',
            [$table, $constraint]
        ))->isNotEmpty();
    }
};
