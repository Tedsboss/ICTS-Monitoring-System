<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fiscal_years', function (Blueprint $table) {
            $table->integer('staff_id')
                ->nullable()
                ->after('year');
        });

        Schema::table('levels', function (Blueprint $table) {
            $table->integer('staff_id')
                ->nullable()
                ->after('level_description');
        });

        Schema::table('expense_types', function (Blueprint $table) {
            $table->integer('staff_id')
                ->nullable()
                ->after('expense_description');
        });

        Schema::table('fiscal_years', function (Blueprint $table) {
            $table->unique(
                ['year', 'staff_id'],
                'fiscal_years_year_staff_unique'
            );

            $table->foreign('staff_id')
                ->references('id')
                ->on('staffs')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->dropUnique('fiscal_years_year_unique');
        });

        Schema::table('levels', function (Blueprint $table) {
            $table->unique(
                ['level_code', 'staff_id'],
                'levels_code_staff_unique'
            );

            $table->foreign('staff_id')
                ->references('id')
                ->on('staffs')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->dropUnique('levels_level_code_unique');
        });

        Schema::table('expense_types', function (Blueprint $table) {
            $table->unique(
                ['type', 'expense_description', 'staff_id'],
                'expense_types_type_description_staff_unique'
            );

            $table->foreign('staff_id')
                ->references('id')
                ->on('staffs')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->dropUnique(
                'expense_types_type_description_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('expense_types', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);

            $table->unique(
                ['type', 'expense_description'],
                'expense_types_type_description_unique'
            );

            $table->dropUnique(
                'expense_types_type_description_staff_unique'
            );

            $table->dropColumn('staff_id');
        });

        Schema::table('levels', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);

            $table->unique(
                'level_code',
                'levels_level_code_unique'
            );

            $table->dropUnique('levels_code_staff_unique');

            $table->dropColumn('staff_id');
        });

        Schema::table('fiscal_years', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);

            $table->unique(
                'year',
                'fiscal_years_year_unique'
            );

            $table->dropUnique('fiscal_years_year_staff_unique');

            $table->dropColumn('staff_id');
        });
    }
};