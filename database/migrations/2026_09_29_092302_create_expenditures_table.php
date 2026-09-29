<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenditures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_header_id')
                ->constrained('program_sub_headers')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->string('expenditure', 200);
            $table->string('prexc', 50)->nullable();
            $table->boolean('is_ops')->default(false);
            $table->timestamps();

            $table->unique(
                ['sub_header_id', 'expenditure'],
                'expenditures_sub_header_name_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenditures');
    }
};