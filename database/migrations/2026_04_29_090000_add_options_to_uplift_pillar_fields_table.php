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
        Schema::table('uplift_pillar_fields', function (Blueprint $table) {
            if (!Schema::hasColumn('uplift_pillar_fields', 'options')) {
                $table->json('options')->nullable()->after('value_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uplift_pillar_fields', function (Blueprint $table) {
            if (Schema::hasColumn('uplift_pillar_fields', 'options')) {
                $table->dropColumn('options');
            }
        });
    }
};
