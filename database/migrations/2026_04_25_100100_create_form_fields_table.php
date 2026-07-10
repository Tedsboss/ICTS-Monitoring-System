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
            Schema::create('form_fields', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('form_id')->index();
                $table->string('label');
                $table->string('value_type')->default('integer');
                $table->integer('column_size')->default(6);
                $table->integer('row_number')->default(1);
                $table->tinyInteger('is_required')->default(1);
                $table->tinyInteger('has_remarks')->default(1);
                $table->integer('order')->default(0);
                $table->tinyInteger('status')->default(1);
                $table->timestamps();

                $table->foreign('form_id')->references('id')->on('forms')->cascadeOnDelete();
                $table->unique(['form_id', 'label'], 'form_fields_form_label_unique');
            });
        }

        $form = DB::table('forms')
            ->where('agency_id', 450)
            ->where('title', 'Savings Lives')
            ->first();

        if ($form == null) {
            return;
        }

        $fields = [
            'Overseas Filipinos repatriated',
            'Flights Deployed',
            'Countries covered',
            'Assistance received by the repatriated overseas Filipinos',
        ];

        foreach ($fields as $index => $label) {
            DB::table('form_fields')->updateOrInsert(
                ['form_id' => $form->id, 'label' => $label],
                [
                    'value_type' => 'integer',
                    'column_size' => 6,
                    'row_number' => (int) ceil(($index + 1) / 2),
                    'is_required' => 1,
                    'has_remarks' => 1,
                    'order' => $index + 1,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
