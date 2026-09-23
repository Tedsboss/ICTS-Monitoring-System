<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkPlan;

class WorkPlanPolicy
{
    private const MODULE_NAME = 'Work Plan';

    // Check if user can open the Work Plan module
    public function viewAny(User $user): bool
    {
        return $this->isAdministrator($user)
            || $this->hasPermission($user, 'view');
    }

    // Check if user can create a Work Plan
    public function create(User $user): bool
    {
        return $this->isAdministrator($user)
            || ($user->staff_id !== null && $this->hasPermission($user, 'add'));
    }

    // Check if user can view a Work Plan
    public function view(User $user, WorkPlan $plan): bool
    {
        return $this->isAdministrator($user)
            || ($this->ownsStaff($user, $plan) && $this->hasPermission($user, 'view'));
    }

    // Check if user can update a Work Plan
    public function update(User $user, WorkPlan $plan): bool
    {
        return $this->isAdministrator($user)
            || ($this->ownsStaff($user, $plan) && $this->hasPermission($user, 'edit'));
    }

    // Only administrators can delete a Work Plan
    public function delete(User $user, WorkPlan $plan): bool
    {
        return $this->isAdministrator($user);
    }

    // Check if user can submit a Work Plan
    public function submit(User $user, WorkPlan $plan): bool
    {
        return $this->isAdministrator($user)
            || ($this->ownsStaff($user, $plan) && $this->hasPermission($user, 'submit'));
    }

    // Check if user can approve a Work Plan
    public function approve(User $user, WorkPlan $plan): bool
    {
        return $this->isAdministrator($user)
            || ($this->ownsStaff($user, $plan) && $this->hasPermission($user, 'approve'));
    }

    // Check if user can return a Work Plan for revision
    public function return(User $user, WorkPlan $plan): bool
    {
        return $this->isAdministrator($user)
            || ($this->ownsStaff($user, $plan) && $this->hasPermission($user, 'return'));
    }

    // Check if user can finalize a Work Plan
    public function finalize(User $user, WorkPlan $plan): bool
    {
        return $this->isAdministrator($user)
            || ($this->ownsStaff($user, $plan) && $this->hasPermission($user, 'finalize'));
    }

    // Check if user can reopen a finalized Work Plan
    public function reopen(User $user, WorkPlan $plan): bool
    {
        return $this->isAdministrator($user)
            || ($this->ownsStaff($user, $plan) && $this->hasPermission($user, 'reopen'));
    }

    private function isAdministrator(User $user): bool
    {
        return in_array((int) $user->role_id, [1, 29], true);
    }

    private function ownsStaff(User $user, WorkPlan $plan): bool
    {
        if ($user->staff_id === null || $plan->staff_id === null) {
            return false;
        }

        return (int) $user->staff_id === (int) $plan->staff_id;
    }

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
