<?php

namespace App\Policies;

use App\Models\UpliftSubmission;
use App\Models\User;

class UpliftSubmissionPolicy
{
  private const GLOBAL_VIEW_ROLE = 29;
  private const CREATE_PERMISSION = 125;
  private const UPDATE_PERMISSION = 126;
  private const DELETE_PERMISSION = 127;
  private const VIEW_PERMISSION = 128;
  private const APPROVE_PERMISSION = 136;
  private const RETURN_PERMISSION = 137;
  private const REJECT_PERMISSION = 138;

  public function viewAny(User $user): bool
  {
    return $user->isSuperAdmin() || $user->role->permissions->whereIn('id', [
      self::CREATE_PERMISSION,
      self::UPDATE_PERMISSION,
      self::DELETE_PERMISSION,
      self::VIEW_PERMISSION,
    ])->count() > 0
      || $user->isDepDevStaff()
      || $user->role_id === self::GLOBAL_VIEW_ROLE;
  }

  public function view(User $user, UpliftSubmission $upliftSubmission): bool
  {
    return ($this->canAccessSubmission($user, $upliftSubmission) || $this->canAccessAssignedSector($user, $upliftSubmission))
      && ($user->isSuperAdmin() || $user->role->permissions->whereIn('id', [
        self::CREATE_PERMISSION,
        self::UPDATE_PERMISSION,
        self::DELETE_PERMISSION,
        self::VIEW_PERMISSION,
      ])->count() > 0
        || $this->canAccessAssignedSector($user, $upliftSubmission)
        || $user->role_id === self::GLOBAL_VIEW_ROLE);
  }

  public function create(User $user): bool
  {
    return $user->isSuperAdmin() || $user->role->permissions->whereIn('id', [self::CREATE_PERMISSION])->count() > 0;
  }

  public function update(User $user, UpliftSubmission $upliftSubmission): bool
  {
    return $upliftSubmission->isEditableStatus()
      && $this->canAccessSubmission($user, $upliftSubmission)
      && ($user->isSuperAdmin() || $user->role->permissions->whereIn('id', [self::UPDATE_PERMISSION])->count() > 0);
  }

  public function delete(User $user, UpliftSubmission $upliftSubmission): bool
  {
    return $this->canAccessSubmission($user, $upliftSubmission)
      && ($user->isSuperAdmin() || $user->role->permissions->whereIn('id', [self::DELETE_PERMISSION])->count() > 0);
  }

  private function canAccessSubmission(User $user, UpliftSubmission $upliftSubmission): bool
  {
    return $user->isSuperAdmin()
      || $user->role_id === self::GLOBAL_VIEW_ROLE
      || $upliftSubmission->agency_id == $user->agency_id;
  }

  public function approve(User $user, UpliftSubmission $upliftSubmission): bool
  {
    return $this->canApprove($user, $upliftSubmission, self::APPROVE_PERMISSION);
  }

  public function return(User $user, UpliftSubmission $upliftSubmission): bool
  {
    return $this->canApprove($user, $upliftSubmission, self::RETURN_PERMISSION);
  }

  public function reject(User $user, UpliftSubmission $upliftSubmission): bool
  {
    return $this->canApprove($user, $upliftSubmission, self::REJECT_PERMISSION);
  }

  private function canApprove(User $user, UpliftSubmission $upliftSubmission, int $permissionId): bool
  {
    if (!$upliftSubmission->isSubmitted()) {
      return false;
    }

    if ($user->isSuperAdmin()) {
      return true;
    }

    $upliftSubmission->loadMissing('measure');

    return $this->canAccessAssignedSector($user, $upliftSubmission)
      && $user->role->permissions->where('id', $permissionId)->count() > 0;
  }

  private function canAccessAssignedSector(User $user, UpliftSubmission $upliftSubmission): bool
  {
    $upliftSubmission->loadMissing('measure');

    return $user->isDepDevStaff()
      && !empty(optional($upliftSubmission->measure)->assigned_sector_id)
      && (int) $user->staff_id === (int) $upliftSubmission->measure->assigned_sector_id;
  }
}
