<?php

namespace App\Policies;

use App\Models\FinancialPlan;
use App\Models\User;

class FinancialPlanPolicy
{
    private const MODULE_NAME = 'Financial Plan';

    // Check if user can open the Financial Plan module
    public function viewAny(User $user): bool
    {
        return $this->isAdministrator($user)
            || $this->hasPermission($user, 'view');
    }

    // Check if user can create a Financial Plan
    public function create(User $user): bool
    {
        return $this->isAdministrator($user)
            || ($user->staff_id !== null && $this->hasPermission($user, 'add'));
    }

    // Check if user can view a Financial Plan
    public function view(User $user, FinancialPlan $plan): bool
    {
        return $this->isAdministrator($user)
            || ($this->ownsStaff($user, $plan) && $this->hasPermission($user, 'view'));
    }

    // Check if user can update a Financial Plan
    public function update(User $user, FinancialPlan $plan): bool
    {
        return $this->isAdministrator($user)
            || ($this->ownsStaff($user, $plan) && $this->hasPermission($user, 'edit'));
    }

    // Only administrators can delete a Financial Plan
    public function delete(User $user, FinancialPlan $plan): bool
    {
        return $this->isAdministrator($user);
    }

    // Check if user can submit a Financial Plan
    public function submit(User $user, FinancialPlan $plan): bool
    {
        return $this->isAdministrator($user)
            || ($this->ownsStaff($user, $plan) && $this->hasPermission($user, 'submit'));
    }

    // Check if user can approve a Financial Plan
    public function approve(User $user, FinancialPlan $plan): bool
    {
        return $this->isAdministrator($user)
            || ($this->ownsStaff($user, $plan) && $this->hasPermission($user, 'approve'));
    }

    // Check if user can return a Financial Plan for revision
    public function return(User $user, FinancialPlan $plan): bool
    {
        return $this->isAdministrator($user)
            || ($this->ownsStaff($user, $plan) && $this->hasPermission($user, 'return'));
    }

    // Check if user can finalize a Financial Plan
    public function finalize(User $user, FinancialPlan $plan): bool
    {
        return $this->isAdministrator($user)
            || ($this->ownsStaff($user, $plan) && $this->hasPermission($user, 'finalize'));
    }

    // Check if user can reopen a finalized Financial Plan
    public function reopen(User $user, FinancialPlan $plan): bool
    {
        return $this->isAdministrator($user)
            || ($this->ownsStaff($user, $plan) && $this->hasPermission($user, 'reopen'));
    }

    // Temporary administrator bypass while legacy System Admin role still exists
    private function isAdministrator(User $user): bool
    {
        return in_array((int) $user->role_id, [1, 29], true);
    }

    // Check if the Financial Plan belongs to the user's staff/office
    private function ownsStaff(User $user, FinancialPlan $plan): bool
    {
        if ($user->staff_id === null || $plan->staff_id === null) {
            return false;
        }

        return (int) $user->staff_id === (int) $plan->staff_id;
    }

    // Check if the user's role has the required Financial Plan permission
    private function hasPermission(User $user, string $permissionName): bool
    {
        $role = $user->role;

        if (! $role) {
            return false;
        }

        $permissionName = strtolower(trim($permissionName));
        $moduleName = strtolower(self::MODULE_NAME);

        return $role->permissions->contains(function ($permission) use ($permissionName, $moduleName) {
            $module = $permission->module;

            if (! $module) {
                return false;
            }

            return strtolower(trim((string) $permission->name)) === $permissionName
                && strtolower(trim((string) $module->name)) === $moduleName;
        });
    }
}
