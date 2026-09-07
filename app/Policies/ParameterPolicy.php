<?php

namespace App\Policies;

use App\Models\Parameter;
use App\Models\User;

class ParameterPolicy
{
    /**
     * Determine whether the user can view any parameters.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view a parameter.
     */
    public function view(User $user, Parameter $parameter): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can create parameters.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update a parameter.
     */
    public function update(User $user, Parameter $parameter): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can delete a parameter.
     */
    public function delete(User $user, Parameter $parameter): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can restore a parameter.
     */
    public function restore(User $user, Parameter $parameter): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete a parameter.
     */
    public function forceDelete(User $user, Parameter $parameter): bool
    {
        return false;
    }
}
