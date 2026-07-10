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
        if (!Schema::hasTable('form_fields') || !Schema::hasColumn('form_fields', 'column_size')) {
            return;
        }

        DB::statement('ALTER TABLE form_fields MODIFY column_size INT NOT NULL DEFAULT 6');
        DB::table('form_fields')->where('column_size', 12)->update(['column_size' => 6]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('form_fields') || !Schema::hasColumn('form_fields', 'column_size')) {
            return;
        }

        DB::statement('ALTER TABLE form_fields MODIFY column_size INT NOT NULL DEFAULT 12');
    }
};
