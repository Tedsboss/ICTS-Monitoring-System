<?php
// app/Policies/SaebPolicy.php

namespace App\Policies;

use App\Models\Saeb;
use App\Models\User;

class SaebPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->role->permissions->contains('module_id', $this->moduleId());
    }

    public function view(User $user, Saeb $saeb): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Saeb $saeb): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Saeb $saeb): bool
    {
        return $this->viewAny($user);
    }

    private function moduleId(): ?int
    {
        return \App\Models\Module::where('name', 'saebs')->value('id');
    }
}
