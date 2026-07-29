<?php
// app/Policies/FinancialPlanPolicy.php

namespace App\Policies;

use App\Models\FinancialPlan;
use App\Models\User;

class FinancialPlanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->role->permissions->contains('module_id', $this->moduleId());
    }

    public function view(User $user, FinancialPlan $financialPlan): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, FinancialPlan $financialPlan): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, FinancialPlan $financialPlan): bool
    {
        return $this->viewAny($user);
    }

    private function moduleId(): ?int
    {
        return \App\Models\Module::where('name', 'financial_plans')->value('id');
    }
}
