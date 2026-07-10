<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const UPLIFT_APPROVAL_PERMISSION_IDS = [136, 137, 138, 139];

    public function up(): void
    {
        foreach ([
            136 => ['approve', 'Permission to approve assigned UPLIFT submissions'],
            137 => ['return', 'Permission to return assigned UPLIFT submissions'],
            138 => ['reject', 'Permission to reject assigned UPLIFT submissions'],
            139 => ['view-approval-history', 'Permission to view UPLIFT submission approval history'],
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

        $this->copyUpliftViewRolesToApprovalPermissions();
    }

    public function down(): void
    {
        if (Schema::hasTable('permission_role')) {
            DB::table('permission_role')
                ->whereIn('permission_id', self::UPLIFT_APPROVAL_PERMISSION_IDS)
                ->delete();
        }

        DB::table('permissions')
            ->whereIn('id', self::UPLIFT_APPROVAL_PERMISSION_IDS)
            ->delete();
    }

    private function copyUpliftViewRolesToApprovalPermissions(): void
    {
        if (!Schema::hasTable('permission_role')) {
            return;
        }

        $roleIds = DB::table('permission_role')
            ->where('permission_id', 128)
            ->pluck('role_id');

        foreach ($roleIds as $roleId) {
            foreach (self::UPLIFT_APPROVAL_PERMISSION_IDS as $permissionId) {
                DB::table('permission_role')->updateOrInsert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }
};
