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
        if (Schema::hasTable('form_submission_values')) {
            return;
        }

        Schema::create('form_submission_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('form_submission_id')->index();
            $table->unsignedBigInteger('form_field_id')->index();
            $table->integer('integer_value')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('form_submission_id')->references('id')->on('form_submissions')->cascadeOnDelete();
            $table->foreign('form_field_id')->references('id')->on('form_fields')->cascadeOnDelete();
            $table->unique(['form_submission_id', 'form_field_id'], 'form_submission_values_submission_field_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submission_values');
    }
};
