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
        Schema::create('procurements', function (Blueprint $table) {
            $table->id();
            $table->string('funding_source', 50);
            $table->string('procurement_title', 255);
            $table->string('expense_class', 20);
            $table->string('division_assigned', 20);
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('quarter', 10)->nullable();
            $table->string('procurement_status', 20)->nullable();
            $table->string('payment_status', 20)->nullable();
            $table->string('retention_status', 20)->nullable();
            $table->timestamps();

            $table->index('funding_source');
            $table->index('expense_class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurements');
    }
};
