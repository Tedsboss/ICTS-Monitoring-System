<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $moduleId = DB::table('modules')
            ->where('name', 'Financial Plan')
            ->value('id');

        if (! $moduleId) {
            throw new \RuntimeException(
                'Financial Plan module was not found. Workflow permissions were not created.'
            );
        }

        $maxOrder = (int) DB::table('permissions')
            ->where('module_id', $moduleId)
            ->max('order');

        $permissions = [
            [
                'name' => 'approve',
                'description' => 'Approve submitted Financial Plan',
            ],
            [
                'name' => 'return',
                'description' => 'Return submitted Financial Plan for revision',
            ],
            [
                'name' => 'finalize',
                'description' => 'Finalize and lock approved Financial Plan',
            ],
            [
                'name' => 'reopen',
                'description' => 'Reopen finalized Financial Plan',
            ],
        ];

        foreach ($permissions as $index => $permission) {
            $exists = DB::table('permissions')
                ->where('module_id', $moduleId)
                ->where('name', $permission['name'])
                ->exists();

            // Preserve existing permission if already present.
            if ($exists) {
                continue;
            }

            DB::table('permissions')->insert([
                'name' => $permission['name'],
                'description' => $permission['description'],
                'module_id' => $moduleId,
                'order' => $maxOrder + $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $moduleId = DB::table('modules')
            ->where('name', 'Financial Plan')
            ->value('id');

        if (! $moduleId) {
            return;
        }

        $permissions = [
            [
                'name' => 'approve',
                'description' => 'Approve submitted Financial Plan',
            ],
            [
                'name' => 'return',
                'description' => 'Return submitted Financial Plan for revision',
            ],
            [
                'name' => 'finalize',
                'description' => 'Finalize and lock approved Financial Plan',
            ],
            [
                'name' => 'reopen',
                'description' => 'Reopen finalized Financial Plan',
            ],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')
                ->where('module_id', $moduleId)
                ->where('name', $permission['name'])
                ->where('description', $permission['description'])
                ->delete();
        }
    }
};
