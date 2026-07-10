<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('form_fields')) {
            return;
        }

        if (!$this->indexExists('form_fields_form_label_unique')) {
            return;
        }

        Schema::table('form_fields', function (Blueprint $table) {
            $table->dropUnique('form_fields_form_label_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('form_fields')) {
            return;
        }

        if ($this->indexExists('form_fields_form_label_unique') || $this->hasDuplicateLabels()) {
            return;
        }

        Schema::table('form_fields', function (Blueprint $table) {
            $table->unique(['form_id', 'label'], 'form_fields_form_label_unique');
        });
    }

    private function indexExists(string $indexName): bool
    {
        return count(DB::select('SHOW INDEX FROM form_fields WHERE Key_name = ?', [$indexName])) > 0;
    }

    private function hasDuplicateLabels(): bool
    {
        return DB::table('form_fields')
            ->select('form_id', 'label')
            ->groupBy('form_id', 'label')
            ->havingRaw('COUNT(*) > 1')
            ->exists();
    }
};
