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
        if (!Schema::hasTable('uplift_pillars')) {
            Schema::create('uplift_pillars', function (Blueprint $table) {
                $table->id();
                $table->string('title')->unique();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('uplift_measures')) {
            Schema::create('uplift_measures', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('uplift_pillar_id')->index();
                $table->string('title');
                $table->text('brief_description')->nullable();
                $table->integer('lead_agency_id')->nullable()->index();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();

                $table->foreign('uplift_pillar_id')->references('id')->on('uplift_pillars')->cascadeOnDelete();
                $table->foreign('lead_agency_id')->references('id')->on('agencies')->nullOnDelete();
                $table->unique(['uplift_pillar_id', 'title'], 'uplift_measures_pillar_title_unique');
            });
        }

        if (!Schema::hasTable('uplift_measure_supporting_agencies')) {
            Schema::create('uplift_measure_supporting_agencies', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('uplift_measure_id')->index();
                $table->integer('agency_id')->index();
                $table->timestamps();

                $table->foreign('uplift_measure_id')->references('id')->on('uplift_measures')->cascadeOnDelete();
                $table->foreign('agency_id')->references('id')->on('agencies')->cascadeOnDelete();
                $table->unique(['uplift_measure_id', 'agency_id'], 'uplift_measure_support_unique');
            });
        }

        if (!Schema::hasTable('uplift_pillar_fields')) {
            Schema::create('uplift_pillar_fields', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('uplift_measure_id')->index();
                $table->unsignedBigInteger('parent_id')->nullable()->index();
                $table->string('section')->nullable();
                $table->string('label');
                $table->text('guide')->nullable();
                $table->string('value_type')->default('text');
                $table->integer('column_size')->default(12);
                $table->integer('row_number')->default(1);
                $table->integer('order')->default(1);
                $table->tinyInteger('is_required')->default(0);
                $table->tinyInteger('has_remarks')->default(0);
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('uplift_measure_id')->references('id')->on('uplift_measures')->cascadeOnDelete();
                $table->foreign('parent_id')->references('id')->on('uplift_pillar_fields')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('uplift_indicators')) {
            Schema::create('uplift_indicators', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('uplift_pillar_field_id')->index();
                $table->string('label');
                $table->string('unit')->nullable();
                $table->string('value_type')->default('decimal');
                $table->integer('order')->default(1);
                $table->tinyInteger('is_required')->default(0);
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('uplift_pillar_field_id')->references('id')->on('uplift_pillar_fields')->cascadeOnDelete();
            });
        }

        DB::table('modules')->updateOrInsert(
            ['id' => 32],
            [
                'name' => 'UPLIFT Form Builder',
                'description' => 'UPLIFT pillar, measure, field, and indicator management',
                'category' => 'Form Management',
                'administrator' => 'Y',
            ]
        );

        foreach ([
            121 => ['add', 'Permission to create UPLIFT pillars, measures, fields, and indicators'],
            122 => ['edit', 'Permission to edit UPLIFT pillars, measures, fields, and indicators'],
            123 => ['delete', 'Permission to remove UPLIFT fields and indicators'],
            124 => ['view', 'Permission to view UPLIFT form builder'],
        ] as $id => $permission) {
            DB::table('permissions')->updateOrInsert(
                ['id' => $id],
                [
                    'name' => $permission[0],
                    'description' => $permission[1],
                    'module_id' => 32,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->whereIn('id', [121, 122, 123, 124])->delete();
        DB::table('modules')->where('id', 32)->delete();

        Schema::dropIfExists('uplift_indicators');
        Schema::dropIfExists('uplift_pillar_fields');
        Schema::dropIfExists('uplift_measure_supporting_agencies');
        Schema::dropIfExists('uplift_measures');
        Schema::dropIfExists('uplift_pillars');
    }
};
