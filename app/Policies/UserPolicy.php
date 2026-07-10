<?php

namespace App\Policies;

use App\Models\Module;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
  /**
   * Determine whether the user can view any models.
   */
  public function viewAny(User $user): bool
  {
    return $user->isSuperAdmin() || $user->role->permissions->whereIn('id', [1, 2, 3])->count() > 0;
  }

  /**
   * Determine whether the user can view the model.
   */
  public function view(User $user, User $model): bool
  {
    return $user->isSuperAdmin() || $user->role->permissions->whereIn('id', [1, 2, 3])->count() > 0;
  }

  /**
   * Determine whether the user can create models.
   */
  public function create(User $user): bool
  {
    return $user->isSuperAdmin() || $user->role->permissions->whereIn('id', [1])->count() > 0;
  }

  /**
   * Determine whether the user can edit the model.
   */
  public function edit(User $user, User $model): bool
  {
    return $user->id != $model->id && ($user->isSuperAdmin() || $user->role->permissions->whereIn('id', [2])->count() > 0);
  }

  /**
   * Determine whether the user can update the model.
   */
  public function update(User $user, User $model): bool
  {
    return $user->id != $model->id && ($user->isSuperAdmin() || $user->role->permissions->whereIn('id', [2])->count() > 0);
  }

  /**
   * Determine whether the user can delete the model.
   */
  public function delete(User $user, User $model): bool
  {
    return $user->id != $model->id && ($user->isSuperAdmin() || $user->role->permissions->whereIn('id', [3])->count() > 0);
  }

  /**
   * Determine whether the user can restore the model.
   */
  public function restore(User $user, User $model): bool
  {
    //
  }

  /**
   * Determine whether the user can permanently delete the model.
   */
  public function forceDelete(User $user, User $model): bool
  {
    //
  }

  public function enableMyEmailNotification(User $user, User $model): bool
  {
    $permission_ids = [
      25, // View all existing paps - system admin
      29, // Completeness check - validator
      30  // Compliance check - validator
    ];
    return $model->isSuperAdmin() || !empty(array_intersect($permission_ids, $model->role->permissions->pluck('id')->toArray()));
  }

  public function showAllDashboard(User $user): bool
  {
    return $user->isSuperAdmin() || $user->role->permissions->whereIn('id', [25])->count() > 0;
  }
}
