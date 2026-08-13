<?php

namespace App\Policies;

use App\Models\FinancialPlan;
use App\Models\User;

class FinancialPlanPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FinancialPlan $plan): bool
    {
        return $this->ownsDivision($user, $plan);
    }

    public function update(User $user, FinancialPlan $plan): bool
    {
        return $this->ownsDivision($user, $plan);
    }

    private function ownsDivision(User $user, FinancialPlan $plan): bool
    {
        // Super Admin / System Admin get cross-division visibility for
        // oversight — everyone else is locked to their own division.
        if (in_array($user->role_id, [1, 29], true)) {
            return true;
        }

        return $plan->division_id !== null
            && $user->division_id === $plan->division_id;
    }
}