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
        if (!Schema::hasTable('users') || Schema::hasColumn('users', 'agency_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->integer('agency_id')->nullable()->after('email');
            $table->index('agency_id', 'users_agency_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'agency_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            try {
                $table->dropForeign('users_agency_id_foreign');
            } catch (\Throwable $e) {
            }

            try {
                $table->dropIndex('users_agency_id_index');
            } catch (\Throwable $e) {
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('agency_id');
        });
    }
};
