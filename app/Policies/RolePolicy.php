<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
  /**
   * Determine whether the user can view any models.
   */
  public function viewAny(User $user): bool
  {
    return $user->isSuperAdmin() || $user->role->permissions->whereIn('id', [4, 5, 6])->count() > 0;
  }

  /**
   * Determine whether the user can view the model.
   */
  public function view(User $user, Role $role): bool
  {
    return $user->isSuperAdmin() || $user->role->permissions->whereIn('id', [4, 5, 6])->count() > 0;
  }

  /**
   * Determine whether the user can create models.
   */
  public function create(User $user): bool
  {
    return $user->isSuperAdmin() || $user->role->permissions->whereIn('id', [4])->count() > 0;
  }

  /**
   * Determine whether the user can edit the model.
   */
  public function edit(User $user, Role $role): bool
  {
    return !in_array($role->id, [1]) && $user->role_id != $role->id && ($user->isSuperAdmin() || $user->role->permissions->whereIn('id', [5])->count() > 0);
  }

  /**
   * Determine whether the user can update the model.
   */
  public function update(User $user, Role $role): bool
  {
    return !in_array($role->id, [1]) && $user->role_id != $role->id && ($user->isSuperAdmin() || $user->role->permissions->whereIn('id', [5])->count() > 0);
  }

  /**
   * Determine whether the user can delete the model.
   */
  public function delete(User $user, Role $role): bool
  {
    return !in_array($role->id, [1]) && $user->role_id != $role->id && ($user->isSuperAdmin() || $user->role->permissions->whereIn('id', [6])->count() > 0);
  }

  /**
   * Determine whether the user can restore the model.
   */
  public function restore(User $user, Role $role): bool
  {
    //
  }

  /**
   * Determine whether the user can permanently delete the model.
   */
  public function forceDelete(User $user, Role $role): bool
  {
    //
  }
}
