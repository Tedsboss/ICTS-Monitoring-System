<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('form_fields') || Schema::hasColumn('form_fields', 'has_remarks')) {
            return;
        }

        Schema::table('form_fields', function (Blueprint $table) {
            $table->tinyInteger('has_remarks')->default(1)->after('is_required');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('form_fields') || !Schema::hasColumn('form_fields', 'has_remarks')) {
            return;
        }

        Schema::table('form_fields', function (Blueprint $table) {
            $table->dropColumn('has_remarks');
        });
    }
};
