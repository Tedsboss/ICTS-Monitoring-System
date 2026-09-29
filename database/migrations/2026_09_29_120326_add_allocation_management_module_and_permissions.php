<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $moduleId = DB::table('modules')
            ->where('name', 'Allocation Management')
            ->value('id');

        if (!$moduleId) {
            $moduleId = DB::table('modules')->insertGetId([
                'name' => 'Allocation Management',
                'description' => 'Manage fiscal years, levels, allocations, expense types, and allocation expenses.',
                'category' => 'Financial Management',
                'administrator' => 'Y',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $permissions = [
            [
                'name' => 'view',
                'description' => 'View Allocation Management records',
                'order' => 1,
            ],
            [
                'name' => 'add',
                'description' => 'Create Allocation Management records',
                'order' => 2,
            ],
            [
                'name' => 'edit',
                'description' => 'Edit Allocation Management records',
                'order' => 3,
            ],
            [
                'name' => 'delete',
                'description' => 'Delete Allocation Management records',
                'order' => 4,
            ],
        ];

        foreach ($permissions as $permission) {
            $exists = DB::table('permissions')
                ->where('module_id', $moduleId)
                ->where('name', $permission['name'])
                ->exists();

            if (!$exists) {
                DB::table('permissions')->insert([
                    'name' => $permission['name'],
                    'description' => $permission['description'],
                    'module_id' => $moduleId,
                    'order' => $permission['order'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $moduleId = DB::table('modules')
            ->where('name', 'Allocation Management')
            ->value('id');

        if (!$moduleId) {
            return;
        }

        DB::table('permissions')
            ->where('module_id', $moduleId)
            ->delete();

        DB::table('modules')
            ->where('id', $moduleId)
            ->delete();
    }
};