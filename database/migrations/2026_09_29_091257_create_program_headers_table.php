<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_headers', function (Blueprint $table) {
            $table->id();
            $table->string('header', 150);
            $table->timestamps();

            $table->unique('header');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_headers');
    }
};