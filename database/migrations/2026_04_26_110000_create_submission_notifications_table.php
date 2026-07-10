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
        if (Schema::hasTable('submission_notifications')) {
            return;
        }

        Schema::create('submission_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('form_submission_id')->index();
            $table->unsignedBigInteger('form_id')->index();
            $table->integer('agency_id')->index();
            $table->string('title');
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('form_submission_id')->references('id')->on('form_submissions')->cascadeOnDelete();
            $table->foreign('form_id')->references('id')->on('forms')->cascadeOnDelete();
            $table->foreign('agency_id')->references('id')->on('agencies')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_notifications');
    }
};
