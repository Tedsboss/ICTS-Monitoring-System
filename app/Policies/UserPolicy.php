<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view the user list.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view another user.
     */
    public function view(User $user, User $model): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can create users.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can edit another user.
     */
    public function edit(User $user, User $model): bool
    {
        // User Management is reserved for the Superadmin.
        if (! $user->isSuperAdmin()) {
            return false;
        }

        // Prevent editing the currently logged-in account
        // through the User Management screen.
        if ((int) $user->id === (int) $model->id) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can update another user.
     */
    public function update(User $user, User $model): bool
    {
        return $this->edit($user, $model);
    }

    /**
     * Determine whether the user can delete another user.
     */
    public function delete(User $user, User $model): bool
    {
        // User Management is reserved for the Superadmin.
        if (! $user->isSuperAdmin()) {
            return false;
        }

        // Prevent deleting the currently logged-in account.
        if ((int) $user->id === (int) $model->id) {
            return false;
        }

        // Protect other Superadmin accounts.
        if ($model->isSuperAdmin()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore a user.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete a user.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Legacy email-notification authorization.
     *
     * Keep this behavior for now because older parts of DIREK/UPLIFT
     * may still call this policy method.
     */
    public function enableMyEmailNotification(User $user, User $model): bool
    {
        $permissionIds = [
            25, // View all existing PAPs - system admin
            29, // Completeness check - validator
            30, // Compliance check - validator
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

    /**
     * Legacy dashboard authorization.
     *
     * Preserve this until the old dashboard dependencies are reviewed.
     */
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
