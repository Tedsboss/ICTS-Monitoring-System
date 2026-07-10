<?php

namespace App\Policies;

use App\Models\UpliftPillar;
use App\Models\User;

class UpliftPillarPolicy
{
  public function viewAny(User $user): bool
  {
    return $user->isSuperAdmin() || $user->role->permissions->whereIn('id', [121, 122, 123, 124])->count() > 0;
  }

  public function view(User $user, UpliftPillar $upliftPillar): bool
  {
    return $this->viewAny($user);
  }

  public function create(User $user): bool
  {
    return $user->isSuperAdmin() || $user->role->permissions->whereIn('id', [121])->count() > 0;
  }

  public function update(User $user, UpliftPillar $upliftPillar): bool
  {
    return $user->isSuperAdmin() || $user->role->permissions->whereIn('id', [122])->count() > 0;
  }

  public function delete(User $user, UpliftPillar $upliftPillar): bool
  {
    return $user->isSuperAdmin() || $user->role->permissions->whereIn('id', [123])->count() > 0;
  }
}
