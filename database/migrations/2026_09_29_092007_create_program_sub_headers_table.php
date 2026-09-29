<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_sub_headers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('header_id')
                ->constrained('program_headers')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->string('sub_header', 150);
            $table->string('sub_code', 50);
            $table->timestamps();

            $table->unique(
                ['header_id', 'sub_header'],
                'program_sub_headers_header_name_unique'
            );
            $table->unique(
                ['header_id', 'sub_code'],
                'program_sub_headers_header_code_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_sub_headers');
    }
};