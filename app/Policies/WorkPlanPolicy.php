<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkPlan;

class WorkPlanPolicy
{
    private const MODULE_NAME = 'Work Plan';

    public function viewAny(User $user): bool
    {
        return $this->isAdministrator($user)
            || $this->hasPermission($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->isAdministrator($user)
            || ($user->staff_id !== null && $this->hasPermission($user, 'add'));
    }

    public function view(User $user, WorkPlan $plan): bool
    {
        return $this->isAdministrator($user)
            || ($this->ownsStaff($user, $plan) && $this->hasPermission($user, 'view'));
    }

    public function update(User $user, WorkPlan $plan): bool
    {
        return $this->isAdministrator($user)
            || ($this->ownsStaff($user, $plan) && $this->hasPermission($user, 'edit'));
    }

    public function delete(User $user, WorkPlan $plan): bool
    {
        return $this->isAdministrator($user)
            && $plan->isEditable();
    }

    public function submit(User $user, WorkPlan $plan): bool
    {
        return $this->isAdministrator($user)
            || ($this->ownsStaff($user, $plan) && $this->hasPermission($user, 'submit'));
    }

    public function approve(User $user, WorkPlan $plan): bool
    {
        return $this->isAdministrator($user)
            || ($this->ownsStaff($user, $plan) && $this->hasPermission($user, 'approve'));
    }

    public function return(User $user, WorkPlan $plan): bool
    {
        return $this->isAdministrator($user)
            || ($this->ownsStaff($user, $plan) && $this->hasPermission($user, 'return'));
    }

    public function finalize(User $user, WorkPlan $plan): bool
    {
        return $this->isAdministrator($user)
            || ($this->ownsStaff($user, $plan) && $this->hasPermission($user, 'finalize'));
    }

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