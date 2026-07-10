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
        if (!Schema::hasTable('uplift_submissions')) {
            Schema::create('uplift_submissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('uplift_measure_id')->index();
                $table->integer('agency_id')->index();
                $table->unsignedInteger('user_id')->index();
                $table->date('reporting_cutoff_date');
                $table->string('status')->default('draft');
                $table->timestamp('submitted_at')->nullable();
                $table->timestamps();

                $table->foreign('uplift_measure_id')->references('id')->on('uplift_measures')->cascadeOnDelete();
                $table->foreign('agency_id')->references('id')->on('agencies')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->unique(['uplift_measure_id', 'agency_id', 'reporting_cutoff_date'], 'uplift_submissions_measure_agency_cutoff_unique');
            });
        }

        if (!Schema::hasTable('uplift_submission_field_values')) {
            Schema::create('uplift_submission_field_values', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('uplift_submission_id')->index();
                $table->unsignedBigInteger('uplift_pillar_field_id')->index();
                $table->integer('integer_value')->nullable();
                $table->decimal('decimal_value', 18, 4)->nullable();
                $table->text('text_value')->nullable();
                $table->date('date_value')->nullable();
                $table->date('date_start_value')->nullable();
                $table->date('date_end_value')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->foreign('uplift_submission_id')->references('id')->on('uplift_submissions')->cascadeOnDelete();
                $table->foreign('uplift_pillar_field_id')->references('id')->on('uplift_pillar_fields')->cascadeOnDelete();
                $table->unique(['uplift_submission_id', 'uplift_pillar_field_id'], 'uplift_submission_field_unique');
            });
        }

        if (!Schema::hasTable('uplift_submission_indicator_values')) {
            Schema::create('uplift_submission_indicator_values', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('uplift_submission_id')->index();
                $table->unsignedBigInteger('uplift_indicator_id')->index();
                $table->integer('integer_value')->nullable();
                $table->decimal('decimal_value', 18, 4)->nullable();
                $table->text('text_value')->nullable();
                $table->date('date_value')->nullable();
                $table->date('date_start_value')->nullable();
                $table->date('date_end_value')->nullable();
                $table->timestamps();

                $table->foreign('uplift_submission_id')->references('id')->on('uplift_submissions')->cascadeOnDelete();
                $table->foreign('uplift_indicator_id')->references('id')->on('uplift_indicators')->cascadeOnDelete();
                $table->unique(['uplift_submission_id', 'uplift_indicator_id'], 'uplift_submission_indicator_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uplift_submission_indicator_values');
        Schema::dropIfExists('uplift_submission_field_values');
        Schema::dropIfExists('uplift_submissions');
    }
};
