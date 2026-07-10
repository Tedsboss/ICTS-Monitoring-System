<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('form_submission_values')) {
            return;
        }

        Schema::table('form_submission_values', function (Blueprint $table) {
            if (!Schema::hasColumn('form_submission_values', 'date_start_value')) {
                $table->date('date_start_value')->nullable()->after('date_value');
            }

            if (!Schema::hasColumn('form_submission_values', 'date_end_value')) {
                $table->date('date_end_value')->nullable()->after('date_start_value');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('form_submission_values')) {
            return;
        }

        Schema::table('form_submission_values', function (Blueprint $table) {
            if (Schema::hasColumn('form_submission_values', 'date_end_value')) {
                $table->dropColumn('date_end_value');
            }

            if (Schema::hasColumn('form_submission_values', 'date_start_value')) {
                $table->dropColumn('date_start_value');
            }
        });
    }
};
