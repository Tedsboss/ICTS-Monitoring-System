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
        if (!Schema::hasTable('forms') || Schema::hasColumn('forms', 'template_source_form_id')) {
            return;
        }

        Schema::table('forms', function (Blueprint $table) {
            $table->unsignedBigInteger('template_source_form_id')->nullable()->after('agency_id')->index();

            $table->foreign('template_source_form_id')
                ->references('id')
                ->on('forms')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('forms') || !Schema::hasColumn('forms', 'template_source_form_id')) {
            return;
        }

        Schema::table('forms', function (Blueprint $table) {
            $table->dropForeign(['template_source_form_id']);
            $table->dropColumn('template_source_form_id');
        });
    }
};
