<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('modules')->updateOrInsert(
            ['id' => 33],
            [
                'name' => 'UPLIFT Submissions',
                'description' => 'UPLIFT report submissions',
                'category' => 'Submission',
                'administrator' => 'N',
            ]
        );

        foreach ([
            125 => ['add', 'Permission to create UPLIFT submissions'],
            126 => ['edit', 'Permission to edit UPLIFT submissions'],
            127 => ['delete', 'Permission to delete UPLIFT submissions'],
            128 => ['view', 'Permission to view UPLIFT submissions'],
        ] as $id => $permission) {
            DB::table('permissions')->updateOrInsert(
                ['id' => $id],
                [
                    'name' => $permission[0],
                    'description' => $permission[1],
                    'module_id' => 33,
                ]
            );
        }

        if (Schema::hasTable('permission_role')) {
            foreach ([113 => 125, 114 => 126, 115 => 127, 116 => 128] as $oldPermissionId => $newPermissionId) {
                $roleIds = DB::table('permission_role')
                    ->where('permission_id', $oldPermissionId)
                    ->pluck('role_id');

                foreach ($roleIds as $roleId) {
                    DB::table('permission_role')->updateOrInsert([
                        'role_id' => $roleId,
                        'permission_id' => $newPermissionId,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('permission_role')) {
            DB::table('permission_role')->whereIn('permission_id', [125, 126, 127, 128])->delete();
        }

        DB::table('permissions')->whereIn('id', [125, 126, 127, 128])->delete();
        DB::table('modules')->where('id', 33)->delete();
    }
};
