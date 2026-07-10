<?php

namespace App\Policies;

use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InquiryPolicy
{
  /**
   * Determine whether the user can view any models.
   */
  public function viewAny(User $user): bool
  {
    return $user->isSuperAdmin() || $user->role->permissions->whereIn('id', [14, 15])->count() > 0;
  }

  /**
   * Determine whether the user can view the model.
   */
  public function view(User $user, Inquiry $inquiry): bool
  {
    return $user->isSuperAdmin() || $user->role->permissions->whereIn('id', [14, 15])->count() > 0;
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
  public function update(User $user, Inquiry $inquiry): bool
  {
    //
  }

  /**
   * Determine whether the user can delete the model.
   */
  public function delete(User $user, Inquiry $inquiry): bool
  {
    //
  }

  /**
   * Determine whether the user can restore the model.
   */
  public function restore(User $user, Inquiry $inquiry): bool
  {
    //
  }

  /**
   * Determine whether the user can permanently delete the model.
   */
  public function forceDelete(User $user, Inquiry $inquiry): bool
  {
    //
  }

  public function reply(User $user, Inquiry $inquiry): bool
  {
    return ($user->isSuperAdmin() || $user->role->permissions->whereIn('id', [14])->count() > 0) && ($inquiry->status == 1 || $inquiry->status == 2);
  }

  public function block(User $user, Inquiry $inquiry): bool
  {
    return ($user->isSuperAdmin() || $user->role->permissions->whereIn('id', [15])->count() > 0) && ($inquiry->status == 1 || $inquiry->status == 2);
  }
}
