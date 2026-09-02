<?php

namespace App\Policies;

use App\Models\FinancialPlan;
use App\Models\User;

class FinancialPlanPolicy
{
    private const MODULE_NAME = 'Financial Plan';

    // Check if user can open the WFP module
    public function viewAny(User $user): bool
    {
        if ($this->isAdministrator($user)) {
            return true;
        }

        return $this->hasPermission($user, 'view');
    }

    // Check if user can create a WFP
    public function create(User $user): bool
    {
        if ($this->isAdministrator($user)) {
            return true;
        }

        return $user->staff_id !== null
            && $this->hasPermission($user, 'add');
    }

    // Check if user can view a WFP
    public function view(User $user, FinancialPlan $plan): bool
    {
        if ($this->isAdministrator($user)) {
            return true;
        }

        return $this->ownsStaff($user, $plan)
            && $this->hasPermission($user, 'view');
    }

    // Check if user can update a WFP
    public function update(User $user, FinancialPlan $plan): bool
    {
        if ($this->isAdministrator($user)) {
            return true;
        }

        return $this->ownsStaff($user, $plan)
            && $this->hasPermission($user, 'edit');
    }

    // Only administrators can delete a WFP
    public function delete(User $user, FinancialPlan $plan): bool
    {
        return $this->isAdministrator($user);
    }

    // Check if user can submit a WFP
    public function submit(User $user, FinancialPlan $plan): bool
    {
        if ($this->isAdministrator($user)) {
            return true;
        }

        return $this->ownsStaff($user, $plan)
            && $this->hasPermission($user, 'submit');
    }

    // Only administrators can approve a WFP
    public function approve(User $user): bool
    {
        return $this->isAdministrator($user);
    }

    // Only administrators can return a WFP for revision
    public function return(User $user): bool
    {
        return $this->isAdministrator($user);
    }

    // Only administrators can finalize a WFP
    public function finalize(User $user): bool
    {
        return $this->isAdministrator($user);
    }

    // Only administrators can reopen a finalized WFP
    public function reopen(User $user): bool
    {
        return $this->isAdministrator($user);
    }

    // Super Admin and System Admin
    private function isAdministrator(User $user): bool
    {
        return in_array((int) $user->role_id, [1, 29], true);
    }

    // Check if the WFP belongs to the user's staff
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
        if (! $user->role) {
            return false;
        }

        return $user->role->permissions->contains(function ($permission) use ($permissionName) {
            return $permission->name === $permissionName
                && $permission->module
                && $permission->module->name === self::MODULE_NAME;
        });
    }
}
