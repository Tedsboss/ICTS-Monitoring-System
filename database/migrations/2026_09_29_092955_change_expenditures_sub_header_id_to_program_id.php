<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenditures', function (Blueprint $table) {
            $table->dropForeign(['sub_header_id']);
            $table->dropUnique('expenditures_sub_header_name_unique');
            $table->renameColumn('sub_header_id', 'program_id');
        });

        Schema::table('expenditures', function (Blueprint $table) {
            $table->foreign('program_id')
                ->references('id')
                ->on('programs')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->unique(
                ['program_id', 'expenditure'],
                'expenditures_program_name_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('expenditures', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropUnique('expenditures_program_name_unique');
            $table->renameColumn('program_id', 'sub_header_id');
        });

        Schema::table('expenditures', function (Blueprint $table) {
            $table->foreign('sub_header_id')
                ->references('id')
                ->on('program_sub_headers')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->unique(
                ['sub_header_id', 'expenditure'],
                'expenditures_sub_header_name_unique'
            );
        });
    }
};