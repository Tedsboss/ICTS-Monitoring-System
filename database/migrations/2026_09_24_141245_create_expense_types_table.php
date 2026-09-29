<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_types', function (Blueprint $table) {
            $table->id();

            $table->string('type', 50);
            $table->string('expense_description', 150);

            $table->timestamps();

            $table->unique(
                ['type', 'expense_description'],
                'expense_types_type_description_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_types');
    }
};