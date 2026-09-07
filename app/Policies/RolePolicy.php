<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    /**
     * Determine whether the user can view the list of roles.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view a role.
     */
    public function view(User $user, Role $role): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can create roles.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can edit a role.
     */
    public function edit(User $user, Role $role): bool
    {
        // Only the Superadmin can manage roles.
        if (! $user->isSuperAdmin()) {
            return false;
        }

        // Protect the Superadmin role.
        if ((int) $role->id === 1) {
            return false;
        }

        // Do not allow the current user to edit their own role.
        if ((int) $user->role_id === (int) $role->id) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can update a role.
     */
    public function update(User $user, Role $role): bool
    {
        return $this->edit($user, $role);
    }

    /**
     * Determine whether the user can delete a role.
     */
    public function delete(User $user, Role $role): bool
    {
        // Only the Superadmin can delete roles.
        if (! $user->isSuperAdmin()) {
            return false;
        }

        // Never allow deletion of the Superadmin role.
        if ((int) $role->id === 1) {
            return false;
        }

        // Do not allow the current user's own role to be deleted.
        if ((int) $user->role_id === (int) $role->id) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore a role.
     */
    public function restore(User $user, Role $role): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete a role.
     */
    public function forceDelete(User $user, Role $role): bool
    {
        return false;
    }
}
