<?php
// database/migrations/2026_07_28_000000_add_subheader_to_financial_plans_row_type.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL ENUMs can't be altered with a normal column change on
        // some Laravel/DB driver combos, so this goes through raw SQL.
        DB::statement("
            ALTER TABLE `financial_plans`
            MODIFY COLUMN `row_type` ENUM('header', 'subheader', 'item')
            NOT NULL DEFAULT 'item'
        ");
    }

    public function down(): void
    {
        // Reverting: any existing 'subheader' rows must be resolved to
        // 'header' first, or MySQL will refuse to shrink the enum with
        // rows that use the value being removed.
        DB::statement("
            UPDATE `financial_plans` SET `row_type` = 'header' WHERE `row_type` = 'subheader'
        ");

        DB::statement("
            ALTER TABLE `financial_plans`
            MODIFY COLUMN `row_type` ENUM('header', 'item')
            NOT NULL DEFAULT 'item'
        ");
    }
};