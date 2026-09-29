<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('allocations', function (Blueprint $table) {
            $table->unique(
                ['year_id', 'level_id', 'staff_id'],
                'allocations_year_level_staff_unique'
            );
        });

        Schema::table('allocations', function (Blueprint $table) {
            $table->dropUnique('allocations_year_level_unique');
        });

        Schema::table('allocations', function (Blueprint $table) {
            $table->foreign('staff_id')
                ->references('id')
                ->on('staffs')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('allocations', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
        });

        Schema::table('allocations', function (Blueprint $table) {
            $table->unique(
                ['year_id', 'level_id'],
                'allocations_year_level_unique'
            );
        });

        Schema::table('allocations', function (Blueprint $table) {
            $table->dropUnique('allocations_year_level_staff_unique');
        });
    }
};