<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('submission_approval_histories')) {
            return;
        }

        Schema::create('submission_approval_histories', function (Blueprint $table) {
            $table->id();
            $table->string('submission_type');
            $table->unsignedBigInteger('submission_id');
            $table->index(['submission_type', 'submission_id'], 'sub_approval_histories_submission_idx');
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->string('action', 30)->index();
            $table->text('remarks');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_approval_histories');
    }
};
