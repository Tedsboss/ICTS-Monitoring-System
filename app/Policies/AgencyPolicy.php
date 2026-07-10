<?php

namespace App\Policies;

use App\Models\Agency;
use App\Models\User;

class AgencyPolicy
{
  public function viewAny(User $user): bool
  {
    return $user->isSuperAdmin();
  }

  public function view(User $user, Agency $agency): bool
  {
    return $user->isSuperAdmin();
  }

  public function update(User $user, Agency $agency): bool
  {
    return $user->isSuperAdmin();
  }
}
