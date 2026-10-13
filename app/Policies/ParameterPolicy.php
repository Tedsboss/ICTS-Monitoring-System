<?php

namespace App\Policies;

use App\Models\Parameter;
use App\Models\User;

class ParameterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Parameter $parameter): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Parameter $parameter): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, Parameter $parameter): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, Parameter $parameter): bool
    {
        return false;
    }

    public function forceDelete(User $user, Parameter $parameter): bool
    {
        return false;
    }
}
