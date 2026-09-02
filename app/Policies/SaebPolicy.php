<?php

namespace App\Policies;

use App\Models\Saeb;
use App\Models\User;

class SaebPolicy
{
    private const MODULE_NAME = 'SAEB';

    // Check if user can open the SAEB module
    public function viewAny(User $user): bool
    {
        if ($this->isAdministrator($user)) {
            return true;
        }

        return $this->hasPermission($user, 'view');
    }

    // Check if user can view a SAEB record
    public function view(User $user, Saeb $saeb): bool
    {
        if ($this->isAdministrator($user)) {
            return true;
        }

        return $this->hasPermission($user, 'view');
    }

    // Check if user can create a SAEB record
    public function create(User $user): bool
    {
        if ($this->isAdministrator($user)) {
            return true;
        }

        return $this->hasPermission($user, 'add');
    }

    // Check if user can update a SAEB record
    public function update(User $user, Saeb $saeb): bool
    {
        if ($this->isAdministrator($user)) {
            return true;
        }

        return $this->hasPermission($user, 'edit');
    }

    // Only administrators can delete SAEB records
    public function delete(User $user, Saeb $saeb): bool
    {
        return $this->isAdministrator($user);
    }

    // Super Admin and System Admin
    private function isAdministrator(User $user): bool
    {
        return in_array((int) $user->role_id, [1, 29], true);
    }

    // Check if the user's role has the required SAEB permission
    private function hasPermission(User $user, string $permissionName): bool
    {
        if (!$user->role) {
            return false;
        }

        return $user->role->permissions->contains(function ($permission) use ($permissionName) {
            return $permission->name === $permissionName
                && $permission->module
                && $permission->module->name === self::MODULE_NAME;
        });
    }
}
