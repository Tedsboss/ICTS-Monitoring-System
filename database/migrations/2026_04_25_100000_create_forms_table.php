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
        if (!Schema::hasTable('forms')) {
            Schema::create('forms', function (Blueprint $table) {
                $table->id();
                $table->integer('agency_id')->index();
                $table->string('title');
                $table->tinyInteger('status')->default(1);
                $table->timestamps();

                $table->foreign('agency_id')->references('id')->on('agencies')->cascadeOnDelete();
                $table->unique(['agency_id', 'title'], 'forms_agency_title_unique');
            });
        }

        if (!DB::table('agencies')->where('id', 450)->exists()) {
            return;
        }

        DB::table('forms')->updateOrInsert(
            ['agency_id' => 450, 'title' => 'Savings Lives'],
            [
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
