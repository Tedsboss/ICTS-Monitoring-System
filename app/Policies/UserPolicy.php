<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function edit(User $user, User $model): bool
    {
        if (! $user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->id === (int) $model->id) {
            return false;
        }

        return true;
    }

    public function update(User $user, User $model): bool
    {
        return $this->edit($user, $model);
    }

    public function delete(User $user, User $model): bool
    {
        if (! $user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->id === (int) $model->id) {
            return false;
        }

        if ($model->isSuperAdmin()) {
            return false;
        }

        return true;
    }

    public function restore(User $user, User $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }

    public function manageItems(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function manageUsers(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function updatecategory(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function enableMyEmailNotification(User $user, User $model): bool
    {
        $permissionIds = [
            25,
            29,
            30,
        ];

        if ($model->isSuperAdmin()) {
            return true;
        }

        if (! $model->role) {
            return false;
        }

        return ! empty(
            array_intersect(
                $permissionIds,
                $model->role->permissions->pluck('id')->toArray()
            )
        );
    }

    public function showAllDashboard(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->role) {
            return false;
        }

        return $user->role
            ->permissions
            ->whereIn('id', [25])
            ->count() > 0;
    }
}
