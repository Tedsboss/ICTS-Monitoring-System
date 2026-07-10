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
        if (Schema::hasTable('form_submissions')) {
            return;
        }

        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('form_id')->index();
            $table->integer('agency_id')->index();
            $table->unsignedInteger('user_id')->index();
            $table->date('reporting_cutoff_date');
            $table->string('status')->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->foreign('form_id')->references('id')->on('forms')->cascadeOnDelete();
            $table->foreign('agency_id')->references('id')->on('agencies')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['form_id', 'agency_id', 'reporting_cutoff_date'], 'form_submissions_form_agency_cutoff_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
