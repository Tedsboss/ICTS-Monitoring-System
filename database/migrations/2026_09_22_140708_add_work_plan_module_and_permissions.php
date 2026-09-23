<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $module = DB::table('modules')
            ->where('name', 'Work Plan')
            ->first();

        if (!$module) {
            $moduleId = DB::table('modules')->insertGetId([
                'name' => 'Work Plan',
                'description' => 'Manage annual Work Plans and monthly target outputs.',
                'category' => 'Financial Management',
                'administrator' => 'N',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $moduleId = $module->id;
        }

        $permissions = [
            'view' => 'View Work Plans',
            'add' => 'Create Work Plans',
            'edit' => 'Edit Work Plans',
            'delete' => 'Delete Work Plans',
            'submit' => 'Submit Work Plans',
            'approve' => 'Approve Work Plans',
            'return' => 'Return Work Plans for revision',
            'finalize' => 'Finalize Work Plans',
            'reopen' => 'Reopen finalized Work Plans',
        ];

        $order = 1;

        foreach ($permissions as $name => $description) {
            $exists = DB::table('permissions')
                ->where('module_id', $moduleId)
                ->where('name', $name)
                ->exists();

            if (!$exists) {
                DB::table('permissions')->insert([
                    'name' => $name,
                    'description' => $description,
                    'module_id' => $moduleId,
                    'order' => $order,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $order++;
        }
    }

    public function down(): void
    {
        $module = DB::table('modules')
            ->where('name', 'Work Plan')
            ->first();

        if (!$module) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->where('module_id', $module->id)
            ->pluck('id');

        if ($permissionIds->isNotEmpty()) {
            DB::table('permission_role')
                ->whereIn('permission_id', $permissionIds)
                ->delete();

            DB::table('permissions')
                ->whereIn('id', $permissionIds)
                ->delete();
        }

        DB::table('modules')
            ->where('id', $module->id)
            ->delete();
    }
};
