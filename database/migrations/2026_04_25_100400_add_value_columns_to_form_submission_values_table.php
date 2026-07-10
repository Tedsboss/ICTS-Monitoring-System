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
            if (!Schema::hasColumn('form_submission_values', 'decimal_value')) {
                $table->decimal('decimal_value', 15, 2)->nullable()->after('integer_value');
            }

            if (!Schema::hasColumn('form_submission_values', 'text_value')) {
                $table->text('text_value')->nullable()->after('decimal_value');
            }

            if (!Schema::hasColumn('form_submission_values', 'date_value')) {
                $table->date('date_value')->nullable()->after('text_value');
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
            if (Schema::hasColumn('form_submission_values', 'date_value')) {
                $table->dropColumn('date_value');
            }

            if (Schema::hasColumn('form_submission_values', 'text_value')) {
                $table->dropColumn('text_value');
            }

            if (Schema::hasColumn('form_submission_values', 'decimal_value')) {
                $table->dropColumn('decimal_value');
            }
        });
    }
};
