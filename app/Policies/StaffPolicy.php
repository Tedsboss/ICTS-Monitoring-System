<?php

namespace App\Policies;

use App\Models\Staff;
use App\Models\User;

class StaffPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Staff $staff): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Staff $staff): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, Staff $staff): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, Staff $staff): bool
    {
        return false;
    }

    public function forceDelete(User $user, Staff $staff): bool
    {
        return false;
    }
}
