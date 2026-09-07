<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create Allocation Type Management module
        $moduleId = DB::table('modules')
            ->where('name', 'Allocation Type Management')
            ->value('id');

        if (! $moduleId) {
            $moduleId = DB::table('modules')->insertGetId([
                'name' => 'Allocation Type Management',
                'description' => 'Manage Financial Plan allocation types',
                'category' => 'Financial Management',
                'administrator' => 'N',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create module permissions
        $permissions = [
            [
                'name' => 'view',
                'description' => 'Permission to view Financial Plan allocation types',
                'order' => 1,
            ],
            [
                'name' => 'add',
                'description' => 'Permission to create Financial Plan allocation types',
                'order' => 2,
            ],
            [
                'name' => 'edit',
                'description' => 'Permission to edit Financial Plan allocation types',
                'order' => 3,
            ],
            [
                'name' => 'delete',
                'description' => 'Permission to delete Financial Plan allocation types',
                'order' => 4,
            ],
        ];

        foreach ($permissions as $permission) {
            $exists = DB::table('permissions')
                ->where('module_id', $moduleId)
                ->where('name', $permission['name'])
                ->exists();

            if (! $exists) {
                DB::table('permissions')->insert([
                    'name' => $permission['name'],
                    'description' => $permission['description'],
                    'module_id' => $moduleId,
                    'order' => $permission['order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Find Allocation Type Management module
        $moduleId = DB::table('modules')
            ->where('name', 'Allocation Type Management')
            ->value('id');

        if (! $moduleId) {
            return;
        }

        // Remove module permissions
        DB::table('permissions')
            ->where('module_id', $moduleId)
            ->delete();

        // Remove module
        DB::table('modules')
            ->where('id', $moduleId)
            ->delete();
    }
};
