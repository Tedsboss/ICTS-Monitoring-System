<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('allocations', function (Blueprint $table) {
            $table->dropIndex(
                'allocations_year_id_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('allocations', function (Blueprint $table) {
            $table->index(
                'year_id',
                'allocations_year_id_index'
            );
        });
    }
};
