<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Role $role): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function edit(User $user, Role $role): bool
    {
        return $user->isSuperAdmin()
            && (int) $role->id !== 1
            && (int) $user->role_id !== (int) $role->id;
    }

    public function update(User $user, Role $role): bool
    {
        return $this->edit($user, $role);
    }

    public function delete(User $user, Role $role): bool
    {
        return $this->edit($user, $role);
    }

    public function restore(User $user, Role $role): bool
    {
        return false;
    }

    public function forceDelete(User $user, Role $role): bool
    {
        return false;
    }
}
