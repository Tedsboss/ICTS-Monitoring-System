<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_plan_classifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('fiscal_year');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('code', 100)->nullable();
            $table->text('name');
            $table->unsignedInteger('level')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('fiscal_year');
            $table->index('parent_id');
            $table->index('sort_order');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_plan_classifications');
    }
};
