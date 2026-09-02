<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prexc_classifications', function (Blueprint $table) {
            $table->id();

            // High-level classification:
            // I. General Administration and Support
            // II. Support to Operations
            // III. Operations
            $table->string('classification_group', 255)->nullable();

            // Optional program heading such as:
            // Program 1: Socioeconomic Policy and Planning Program
            $table->string('program_name', 500)->nullable();

            // Actual selectable Program Classification / activity
            $table->text('classification_name');

            // Official PREXC code paired with the classification
            $table->string('prexc_code', 50);

            // Controls display order inside the dropdown
            $table->unsignedInteger('sort_order')->default(0);

            // Allows old classifications to remain for historical records
            // while preventing them from being selected for new entries.
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('prexc_code');
            $table->index('is_active');

            $table->unique(
                ['classification_name', 'prexc_code'],
                'prexc_classification_code_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prexc_classifications');
    }
};
