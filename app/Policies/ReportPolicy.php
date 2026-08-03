<?php

namespace App\Policies;

use App\Models\User;

class ReportPolicy
{
    /**
     * Determine whether the user can view reports.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('principal');
    }

    /**
     * Determine whether the user can generate official reports.
     */
    public function generateOfficial(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('principal');
    }
}
