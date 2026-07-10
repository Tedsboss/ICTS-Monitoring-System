<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop legacy tables from the previous submission/request system.
     *
     * These tables are not used by the current Indicator/Form submissions,
     * UPLIFT submissions, approval history, or approved REST APIs.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ($this->legacyTables() as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Intentionally not reversible. The legacy schemas came from an older
        // system and are no longer represented by the current migrations.
    }

    private function legacyTables(): array
    {
        return [
            'request_solution_pivot',
            'request_attachments',
            'request_solutions',
            'requests',
            'submission_import_logs',
            'submission_headline_indicator_updates',
            'submission_measure_updates',
            'weekly_submissions',
            'headline_indicators',
            'measures',
            'user_systems',
            'systems',
        ];
    }
};
