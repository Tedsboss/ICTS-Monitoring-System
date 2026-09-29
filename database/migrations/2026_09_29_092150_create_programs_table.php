<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_header_id')
                ->constrained('program_sub_headers')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->string('program', 150);
            $table->timestamps();

            $table->unique(
                ['sub_header_id', 'program'],
                'programs_sub_header_name_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};