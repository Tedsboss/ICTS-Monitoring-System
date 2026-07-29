<?php
// app/Policies/ProcurementPolicy.php

namespace App\Policies;

use App\Models\Procurement;
use App\Models\User;

class ProcurementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->role->permissions->contains('module_id', $this->moduleId());
    }

    public function view(User $user, Procurement $procurement): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Procurement $procurement): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Procurement $procurement): bool
    {
        return $this->viewAny($user);
    }

    private function moduleId(): ?int
    {
        return \App\Models\Module::where('name', 'procurements')->value('id');
    }
}
