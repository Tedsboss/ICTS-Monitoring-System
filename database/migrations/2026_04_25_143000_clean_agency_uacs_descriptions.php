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
        if (!Schema::hasTable('agencies') || !Schema::hasColumn('agencies', 'UACS_AGY_DSC')) {
            return;
        }

        DB::statement("
            UPDATE agencies
            SET UACS_AGY_DSC = TRIM(SUBSTRING_INDEX(UACS_AGY_DSC, '(', 1))
            WHERE UACS_AGY_DSC LIKE '%(%'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This cleanup cannot be reversed because the removed parenthesized text is not stored elsewhere.
    }
};
