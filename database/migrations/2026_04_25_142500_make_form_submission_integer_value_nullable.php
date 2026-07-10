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
        if (!Schema::hasTable('form_submission_values') || !Schema::hasColumn('form_submission_values', 'integer_value')) {
            return;
        }

        Schema::table('form_submission_values', function (Blueprint $table) {
            $table->integer('integer_value')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('form_submission_values') || !Schema::hasColumn('form_submission_values', 'integer_value')) {
            return;
        }

        Schema::table('form_submission_values', function (Blueprint $table) {
            $table->integer('integer_value')->default(0)->nullable(false)->change();
        });
    }
};
