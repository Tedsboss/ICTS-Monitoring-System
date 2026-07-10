<?php

namespace App\Policies;

use App\Models\RestrictedIp;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RestrictedIpPolicy
{
  /**
   * Determine whether the user can view any models.
   */
  public function viewAny(User $user): bool
  {
    return $user->isSuperAdmin() || $user->role->permissions->whereIn('id', [8, 9, 10])->count() > 0;
  }

  /**
   * Determine whether the user can view the model.
   */
  public function view(User $user, RestrictedIp $restrictedIp): bool
  {
    return $user->isSuperAdmin() || $user->role->permissions->whereIn('id', [8, 9, 10])->count() > 0;
  }

  /**
   * Determine whether the user can create models.
   */
  public function create(User $user): bool
  {
    return $user->isSuperAdmin() || $user->role->permissions->whereIn('id', [8])->count() > 0;
  }

  /**
   * Determine whether the user can update the model.
   */
  public function update(User $user, RestrictedIp $restrictedIp): bool
  {
    return $user->isSuperAdmin() || $user->role->permissions->whereIn('id', [9])->count() > 0;
  }

  /**
   * Determine whether the user can delete the model.
   */
  public function delete(User $user, RestrictedIp $restrictedIp): bool
  {
    return $user->isSuperAdmin() || $user->role->permissions->whereIn('id', [10])->count() > 0;
  }

  /**
   * Determine whether the user can restore the model.
   */
  public function restore(User $user, RestrictedIp $restrictedIp): bool
  {
    //
  }

  /**
   * Determine whether the user can permanently delete the model.
   */
  public function forceDelete(User $user, RestrictedIp $restrictedIp): bool
  {
    //
  }
}
