<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const API_MODULE_ID = 34;
    private const API_PERMISSION_IDS = [129, 130, 131];
    private const INDICATOR_APPROVAL_PERMISSION_IDS = [132, 133, 134, 135];

    public function up(): void
    {
        DB::table('modules')->updateOrInsert(
            ['id' => self::API_MODULE_ID],
            [
                'name' => 'API Clients',
                'description' => 'Manage approved submission API clients and tokens',
                'category' => 'Security',
                'administrator' => 'Y',
            ]
        );

        foreach ([
            129 => ['view', 'Permission to view API clients'],
            130 => ['add', 'Permission to create API clients'],
            131 => ['revoke', 'Permission to revoke API clients'],
        ] as $id => $permission) {
            DB::table('permissions')->updateOrInsert(
                ['id' => $id],
                [
                    'name' => $permission[0],
                    'description' => $permission[1],
                    'module_id' => self::API_MODULE_ID,
                ]
            );
        }

        foreach ([
            132 => ['approve', 'Permission to approve assigned indicator submissions'],
            133 => ['return', 'Permission to return assigned indicator submissions'],
            134 => ['reject', 'Permission to reject assigned indicator submissions'],
            135 => ['view-approval-history', 'Permission to view indicator submission approval history'],
        ] as $id => $permission) {
            DB::table('permissions')->updateOrInsert(
                ['id' => $id],
                [
                    'name' => $permission[0],
                    'description' => $permission[1],
                    'module_id' => 30,
                ]
            );
        }

        $this->copyIndicatorViewRolesToApprovalPermissions();
    }

    public function down(): void
    {
        if (Schema::hasTable('permission_role')) {
            DB::table('permission_role')
                ->whereIn('permission_id', array_merge(self::API_PERMISSION_IDS, self::INDICATOR_APPROVAL_PERMISSION_IDS))
                ->delete();
        }

        DB::table('permissions')
            ->whereIn('id', array_merge(self::API_PERMISSION_IDS, self::INDICATOR_APPROVAL_PERMISSION_IDS))
            ->delete();

        DB::table('modules')
            ->where('id', self::API_MODULE_ID)
            ->delete();
    }

    private function copyIndicatorViewRolesToApprovalPermissions(): void
    {
        if (!Schema::hasTable('permission_role')) {
            return;
        }

        $roleIds = DB::table('permission_role')
            ->where('permission_id', 116)
            ->pluck('role_id');

        foreach ($roleIds as $roleId) {
            foreach (self::INDICATOR_APPROVAL_PERMISSION_IDS as $permissionId) {
                DB::table('permission_role')->updateOrInsert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }
};
