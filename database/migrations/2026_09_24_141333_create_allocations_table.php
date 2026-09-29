<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allocations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('year_id')
                ->constrained('fiscal_years')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('level_id')
                ->constrained('levels')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->timestamps();

            $table->unique(
                ['year_id', 'level_id'],
                'allocations_year_level_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allocations');
    }
};