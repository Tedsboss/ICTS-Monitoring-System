<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('form_fields') || Schema::hasColumn('form_fields', 'row_number')) {
            return;
        }

        Schema::table('form_fields', function ($table) {
            $table->integer('row_number')->default(1)->after('column_size');
        });

        $fields = DB::table('form_fields')->orderBy('form_id')->orderBy('order')->orderBy('id')->get();
        foreach ($fields as $field) {
            DB::table('form_fields')
                ->where('id', $field->id)
                ->update([
                    'row_number' => (int) ceil($field->order / 2),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('form_fields') || !Schema::hasColumn('form_fields', 'row_number')) {
            return;
        }

        Schema::table('form_fields', function ($table) {
            $table->dropColumn('row_number');
        });
    }
};
