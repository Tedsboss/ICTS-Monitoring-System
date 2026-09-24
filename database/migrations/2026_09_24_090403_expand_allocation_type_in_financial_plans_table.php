<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_plans', function (Blueprint $table) {
            $table->string('allocation_type', 50)
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('financial_plans', function (Blueprint $table) {
            $table->string('allocation_type', 20)
                ->nullable()
                ->change();
        });
    }
};