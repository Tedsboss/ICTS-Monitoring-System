<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_personnel', function (Blueprint $table) {
            $table->id();

            // Staff/office this personnel belongs to
            $table->unsignedBigInteger('staff_id')->index();

            // Personnel information
            $table->string('name', 150);
            $table->string('position', 150)->nullable();

            // Allows personnel to be hidden from future selections
            // without deleting historical records.
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Prevent the exact same personnel name from being
            // duplicated under the same staff/office.
            $table->unique(
                ['staff_id', 'name'],
                'staff_personnel_staff_name_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_personnel');
    }
};
