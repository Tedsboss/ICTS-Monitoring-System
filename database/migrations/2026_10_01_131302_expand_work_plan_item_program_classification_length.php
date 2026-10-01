<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('work_plan_items')
            && Schema::hasColumn('work_plan_items', 'program_classification')
        ) {
            Schema::table('work_plan_items', function (Blueprint $table) {
                $table->string('program_classification', 500)
                    ->nullable()
                    ->change();
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('work_plan_items')
            && Schema::hasColumn('work_plan_items', 'program_classification')
        ) {
            Schema::table('work_plan_items', function (Blueprint $table) {
                $table->string('program_classification', 255)
                    ->nullable()
                    ->change();
            });
        }
    }
};
