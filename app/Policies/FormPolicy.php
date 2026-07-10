<?php

namespace App\Policies;

use App\Models\Agency;
use App\Models\Form;
use App\Models\User;

class FormPolicy
{
  public function viewAny(User $user): bool
  {
    return $user->isSuperAdmin() || $user->role->permissions->whereIn('id', [117, 118, 119, 120])->count() > 0;
  }

  public function view(User $user, Form $form): bool
  {
    return ($this->canManageAgencyForm($user, $form) || $this->canViewTemplate($form))
      && ($user->isSuperAdmin() || $user->role->permissions->whereIn('id', [117, 118, 119, 120])->count() > 0);
  }

  public function create(User $user): bool
  {
    return $user->isSuperAdmin() || $user->role->permissions->whereIn('id', [117])->count() > 0;
  }

  public function update(User $user, Form $form): bool
  {
    return $this->canManageAgencyForm($user, $form)
      && ($user->isSuperAdmin() || $user->role->permissions->whereIn('id', [118])->count() > 0);
  }

  public function delete(User $user, Form $form): bool
  {
    return $this->canManageAgencyForm($user, $form)
      && ($user->isSuperAdmin() || $user->role->permissions->whereIn('id', [119])->count() > 0);
  }

  private function canManageAgencyForm(User $user, Form $form): bool
  {
    return $user->isSuperAdmin()
      || ($user->agency_id != null && (string) $form->agency_id === (string) $user->agency_id);
  }

  private function canViewTemplate(Form $form): bool
  {
    return (int) $form->status === 1 && Agency::isDepDevId($form->agency_id);
  }
}
