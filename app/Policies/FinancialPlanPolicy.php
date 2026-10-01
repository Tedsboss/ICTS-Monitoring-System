<?php

namespace App\Policies;

use App\Models\FinancialPlan;
use App\Models\User;

class FinancialPlanPolicy
{
    private const MODULE_NAME = 'Financial Plan';

    public function viewAny(User $user): bool
    {
        return $user->isAdministrator()
            || $this->hasPermission($user, 'view');
    }

    public function create(User $user): bool
    {
        return $user->isAdministrator()
            || (
                $user->staff_id !== null
                && $this->hasPermission($user, 'add')
            );
    }

    public function view(User $user, FinancialPlan $plan): bool
    {
        return $user->isAdministrator()
            || (
                $this->ownsStaff($user, $plan)
                && $this->hasPermission($user, 'view')
            );
    }

    public function update(User $user, FinancialPlan $plan): bool
    {
        return $user->isAdministrator()
            || (
                $this->ownsStaff($user, $plan)
                && $this->hasPermission($user, 'edit')
            );
    }

    public function delete(User $user, FinancialPlan $plan): bool
    {
        return $user->isAdministrator();
    }

    public function submit(User $user, FinancialPlan $plan): bool
    {
        return $user->isAdministrator()
            || (
                $this->ownsStaff($user, $plan)
                && $this->hasPermission($user, 'submit')
            );
    }

    public function approve(User $user, FinancialPlan $plan): bool
    {
        return $user->isAdministrator()
            || (
                $this->ownsStaff($user, $plan)
                && $this->hasPermission($user, 'approve')
            );
    }

    public function return(User $user, FinancialPlan $plan): bool
    {
        return $user->isAdministrator()
            || (
                $this->ownsStaff($user, $plan)
                && $this->hasPermission($user, 'return')
            );
    }

    public function finalize(User $user, FinancialPlan $plan): bool
    {
        return $user->isAdministrator()
            || (
                $this->ownsStaff($user, $plan)
                && $this->hasPermission($user, 'finalize')
            );
    }

    public function reopen(User $user, FinancialPlan $plan): bool
    {
        return $user->isAdministrator()
            || (
                $this->ownsStaff($user, $plan)
                && $this->hasPermission($user, 'reopen')
            );
    }

    private function ownsStaff(
        User $user,
        FinancialPlan $plan
    ): bool {
        if ($user->staff_id === null || $plan->staff_id === null) {
            return false;
        }

        return (int) $user->staff_id === (int) $plan->staff_id;
    }

    private function hasPermission(
        User $user,
        string $permissionName
    ): bool {
        $role = $user->role;

        if (! $role) {
            return false;
        }

        $permissionName = strtolower(trim($permissionName));
        $moduleName = strtolower(self::MODULE_NAME);

        return $role->permissions->contains(
            function ($permission) use ($permissionName, $moduleName) {
                $module = $permission->module;

                if (! $module) {
                    return false;
                }

                return strtolower(trim((string) $permission->name)) === $permissionName
                    && strtolower(trim((string) $module->name)) === $moduleName;
            }
        );
    }
}
