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
        Schema::create('saeb', function (Blueprint $table) {
            $table->id();
            $table->date('as_of_date');
            $table->string('funding_source', 100);
            $table->string('allotment_class', 20);
            $table->string('expense_class', 150);
            $table->decimal('allotment', 15, 2)->default(0);
            $table->decimal('obligated', 15, 2)->default(0);
            $table->decimal('aa', 15, 2)->default(0);
            $table->decimal('balances', 15, 2)->default(0);
            $table->timestamps();

            $table->index('funding_source');
            $table->index('allotment_class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saeb');
    }
};
