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
        if (!Schema::hasTable('form_fields') || Schema::hasColumn('form_fields', 'column_size')) {
            return;
        }

        Schema::table('form_fields', function (Blueprint $table) {
            $table->integer('column_size')->default(6)->after('value_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('form_fields') || !Schema::hasColumn('form_fields', 'column_size')) {
            return;
        }

        Schema::table('form_fields', function (Blueprint $table) {
            $table->dropColumn('column_size');
        });
    }
};
