<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Financial Plan module
        $financialPlanModuleId = DB::table('modules')
            ->where('name', 'Financial Plan')
            ->where('category', 'Financial Management')
            ->value('id');

        if (!$financialPlanModuleId) {
            $financialPlanModuleId = DB::table('modules')->insertGetId([
                'name' => 'Financial Plan',
                'description' => 'Manage Work and Financial Plan records',
                'category' => 'Financial Management',
                'administrator' => 'N',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->addPermission(
            $financialPlanModuleId,
            'view',
            'Permission to view financial plans',
            1
        );

        $this->addPermission(
            $financialPlanModuleId,
            'add',
            'Permission to create financial plans',
            2
        );

        $this->addPermission(
            $financialPlanModuleId,
            'edit',
            'Permission to edit financial plans',
            3
        );

        $this->addPermission(
            $financialPlanModuleId,
            'submit',
            'Permission to submit financial plans',
            4
        );

        // Procurement module
        $procurementModuleId = DB::table('modules')
            ->where('name', 'Procurement')
            ->where('category', 'Financial Management')
            ->value('id');

        if (!$procurementModuleId) {
            $procurementModuleId = DB::table('modules')->insertGetId([
                'name' => 'Procurement',
                'description' => 'Manage procurement records',
                'category' => 'Financial Management',
                'administrator' => 'N',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->addPermission(
            $procurementModuleId,
            'view',
            'Permission to view procurement records',
            1
        );

        $this->addPermission(
            $procurementModuleId,
            'add',
            'Permission to create procurement records',
            2
        );

        $this->addPermission(
            $procurementModuleId,
            'edit',
            'Permission to edit procurement records',
            3
        );

        // SAEB module
        $saebModuleId = DB::table('modules')
            ->where('name', 'SAEB')
            ->where('category', 'Financial Management')
            ->value('id');

        if (!$saebModuleId) {
            $saebModuleId = DB::table('modules')->insertGetId([
                'name' => 'SAEB',
                'description' => 'Manage Statement of Allotment, Expenditures and Balances records',
                'category' => 'Financial Management',
                'administrator' => 'N',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->addPermission(
            $saebModuleId,
            'view',
            'Permission to view SAEB records',
            1
        );

        $this->addPermission(
            $saebModuleId,
            'add',
            'Permission to create SAEB records',
            2
        );

        $this->addPermission(
            $saebModuleId,
            'edit',
            'Permission to edit SAEB records',
            3
        );
    }

    public function down(): void
    {
        $moduleIds = DB::table('modules')
            ->where('category', 'Financial Management')
            ->whereIn('name', [
                'Financial Plan',
                'Procurement',
                'SAEB',
            ])
            ->pluck('id');

        if ($moduleIds->isEmpty()) {
            return;
        }

        // Remove role-permission assignments first
        DB::table('permission_role')
            ->whereIn(
                'permission_id',
                DB::table('permissions')
                    ->whereIn('module_id', $moduleIds)
                    ->pluck('id')
            )
            ->delete();

        // Remove permissions
        DB::table('permissions')
            ->whereIn('module_id', $moduleIds)
            ->delete();

        // Remove modules
        DB::table('modules')
            ->whereIn('id', $moduleIds)
            ->delete();
    }

    private function addPermission(
        int $moduleId,
        string $name,
        string $description,
        int $order
    ): void {
        $exists = DB::table('permissions')
            ->where('module_id', $moduleId)
            ->where('name', $name)
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('permissions')->insert([
            'name' => $name,
            'description' => $description,
            'module_id' => $moduleId,
            'order' => $order,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
