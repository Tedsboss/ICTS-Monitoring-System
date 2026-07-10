<?php

namespace App\Policies;

use App\Models\FormSubmission;
use App\Models\User;

class FormSubmissionPolicy
{
  private const GLOBAL_VIEW_ROLE = 29;
  private const APPROVE_PERMISSION = 132;
  private const RETURN_PERMISSION = 133;
  private const REJECT_PERMISSION = 134;

  public function viewAny(User $user): bool
  {
    return $user->isSuperAdmin()
      || $user->role_id === self::GLOBAL_VIEW_ROLE
      || $user->isDepDevStaff()
      || $user->role->permissions->whereIn('id', [113, 114, 115, 116])->count() > 0;
  }

  public function view(User $user, FormSubmission $formSubmission): bool
  {
    return ($this->canAccessSubmission($user, $formSubmission) || $this->canAccessAssignedSector($user, $formSubmission))
      && ($user->isSuperAdmin()
        || $user->role_id === self::GLOBAL_VIEW_ROLE
        || $this->canAccessAssignedSector($user, $formSubmission)
        || $user->role->permissions->whereIn('id', [113, 114, 115, 116])->count() > 0);
  }

  public function create(User $user): bool
  {
    return $user->isSuperAdmin() || $user->role->permissions->whereIn('id', [113])->count() > 0;
  }

  public function update(User $user, FormSubmission $formSubmission): bool
  {
    return $formSubmission->isEditableStatus()
      && $this->canAccessSubmission($user, $formSubmission)
      && ($user->isSuperAdmin() || $user->role->permissions->whereIn('id', [114])->count() > 0);
  }

  public function delete(User $user, FormSubmission $formSubmission): bool
  {
    return $this->canAccessSubmission($user, $formSubmission)
      && ($user->isSuperAdmin() || $user->role->permissions->whereIn('id', [115])->count() > 0);
  }

  private function canAccessSubmission(User $user, FormSubmission $formSubmission): bool
  {
    return $user->isSuperAdmin()
      || $user->role_id === self::GLOBAL_VIEW_ROLE
      || $formSubmission->agency_id == $user->agency_id;
  }

  public function approve(User $user, FormSubmission $formSubmission): bool
  {
    return $this->canApprove($user, $formSubmission, self::APPROVE_PERMISSION);
  }

  public function return(User $user, FormSubmission $formSubmission): bool
  {
    return $this->canApprove($user, $formSubmission, self::RETURN_PERMISSION);
  }

  public function reject(User $user, FormSubmission $formSubmission): bool
  {
    return $this->canApprove($user, $formSubmission, self::REJECT_PERMISSION);
  }

  private function canApprove(User $user, FormSubmission $formSubmission, int $permissionId): bool
  {
    if (!$formSubmission->isSubmitted()) {
      return false;
    }

    if ($user->isSuperAdmin()) {
      return true;
    }

    $formSubmission->loadMissing('form');

    return $this->canAccessAssignedSector($user, $formSubmission)
      && $user->role->permissions->where('id', $permissionId)->count() > 0;
  }

  private function canAccessAssignedSector(User $user, FormSubmission $formSubmission): bool
  {
    $formSubmission->loadMissing('form');

    return $user->isDepDevStaff()
      && !empty(optional($formSubmission->form)->assigned_sector_id)
      && (int) $user->staff_id === (int) $formSubmission->form->assigned_sector_id;
  }
}
