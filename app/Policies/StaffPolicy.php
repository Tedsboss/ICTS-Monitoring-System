<?php

namespace App\Policies;

use App\Models\Staff;
use App\Models\User;

class StaffPolicy
{
    /**
     * Determine whether the user can view any staff records.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view a staff record.
     */
    public function view(User $user, Staff $staff): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can create staff records.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update a staff record.
     */
    public function update(User $user, Staff $staff): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can delete a staff record.
     */
    public function delete(User $user, Staff $staff): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can restore a staff record.
     */
    public function restore(User $user, Staff $staff): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete a staff record.
     */
    public function forceDelete(User $user, Staff $staff): bool
    {
        return false;
    }
}
