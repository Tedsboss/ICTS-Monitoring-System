<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('modules')->updateOrInsert(
            ['id' => 31],
            [
                'name' => 'Form Management',
                'description' => 'Form Management module',
                'category' => 'Form Management',
                'administrator' => 'Y',
            ]
        );

        DB::table('permissions')->updateOrInsert(
            ['id' => 117],
            [
                'name' => 'add',
                'description' => 'Permission to create forms and form fields',
                'module_id' => 31,
            ]
        );

        DB::table('permissions')->updateOrInsert(
            ['id' => 118],
            [
                'name' => 'edit',
                'description' => 'Permission to edit forms and form fields',
                'module_id' => 31,
            ]
        );

        DB::table('permissions')->updateOrInsert(
            ['id' => 119],
            [
                'name' => 'delete',
                'description' => 'Permission to delete form fields',
                'module_id' => 31,
            ]
        );

        DB::table('permissions')->updateOrInsert(
            ['id' => 120],
            [
                'name' => 'view',
                'description' => 'Permission to view form management',
                'module_id' => 31,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')
            ->whereIn('id', [117, 118, 119, 120])
            ->delete();

        DB::table('modules')
            ->where('id', 31)
            ->delete();
    }
};
