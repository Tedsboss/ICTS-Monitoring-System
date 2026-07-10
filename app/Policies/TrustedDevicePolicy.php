<?php

namespace App\Policies;

use App\Models\TrustedDevice;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TrustedDevicePolicy
{
  /**
   * Determine whether the user can view any models.
   */
  public function viewAny(User $user): bool
  {
    return true;
  }

  /**
   * Determine whether the user can view the model.
   */
  public function view(User $user, TrustedDevice $trustedDevice): bool
  {
    return true;
  }

  /**
   * Determine whether the user can create models.
   */
  public function create(User $user): bool
  {
    //
  }

  /**
   * Determine whether the user can update the model.
   */
  public function update(User $user, TrustedDevice $trustedDevice): bool
  {
    //
  }

  /**
   * Determine whether the user can delete the model.
   */
  public function delete(User $user, TrustedDevice $trustedDevice): bool
  {
    //
  }

  /**
   * Determine whether the user can restore the model.
   */
  public function restore(User $user, TrustedDevice $trustedDevice): bool
  {
    //
  }

  /**
   * Determine whether the user can permanently delete the model.
   */
  public function forceDelete(User $user, TrustedDevice $trustedDevice): bool
  {
    //
  }

  public function revokeUserSession(User $user, TrustedDevice $trustedDevice): bool
  {
    if ($user->isSuperAdmin() || $user->role->permissions->whereIn('id', [43])->count() > 0 || $user->id == $trustedDevice->user_id) {
      if ($trustedDevice->status == 'Active') {
        if (session()->has('two_factor_current') && session('two_factor_current') == $trustedDevice->id) {
          return false;
        } else {
          return true;
        }
      }
    }
    return false;
  }
}
