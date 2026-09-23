<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_plan_items', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')
                ->nullable()
                ->after('work_plan_id');

            $table->enum('row_type', [
                'header',
                'subheader',
                'item',
            ])
                ->default('item')
                ->after('parent_id');

            $table->text('title')
                ->nullable()
                ->after('row_type');

            $table->index('parent_id');
            $table->index('row_type');

            $table->foreign('parent_id')
                ->references('id')
                ->on('work_plan_items')
                ->nullOnDelete();
        });

        Schema::table('work_plan_items', function (Blueprint $table) {
            $table->unsignedBigInteger('classification_id')
                ->nullable()
                ->change();

            $table->text('specific_activity')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('work_plan_items', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['parent_id']);
            $table->dropIndex(['row_type']);

            $table->dropColumn([
                'parent_id',
                'row_type',
                'title',
            ]);
        });

        Schema::table('work_plan_items', function (Blueprint $table) {
            $table->unsignedBigInteger('classification_id')
                ->nullable(false)
                ->change();

            $table->text('specific_activity')
                ->nullable(false)
                ->change();
        });
    }
};