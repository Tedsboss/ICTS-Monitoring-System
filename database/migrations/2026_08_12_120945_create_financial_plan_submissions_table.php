<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_plan_submissions', function (Blueprint $table) {
            $table->id();
            $table->integer('fiscal_year');
            $table->string('office_name', 150);
            $table->enum('finalized', ['yes', 'no'])->default('no');

            // users.id is int(10) unsigned in this app (not Laravel's
            // default bigint) — must match exactly for the FK to form.
            $table->unsignedInteger('submitted_by')->nullable();
            $table->timestamp('submitted_at')->nullable();

            $table->unsignedInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->text('return_remarks')->nullable();
            $table->timestamps();

            $table->unique(['fiscal_year', 'office_name']);

            $table->foreign('submitted_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_plan_submissions');
    }
};