<?php

namespace App\Policies;

use App\Models\IncomeTarget;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class IncomeTargetPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Super admin, admin wilayah, and admin area can view targets
        return $user->isSuperAdmin() ||
               $user->isAdminWilayah() ||
               $user->isAdminArea();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, IncomeTarget $incomeTarget): bool
    {
        // Super admin can view any target
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin wilayah can view targets for outlets in their wilayah
        if ($user->isAdminWilayah()) {
            return $user->hasOutletAccess($incomeTarget->outlet);
        }

        // Admin area can view targets for outlets in their area
        if ($user->isAdminArea()) {
            return $user->hasOutletAccess($incomeTarget->outlet);
        }

        // Admin outlet cannot view targets (not authorized based on requirements)
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // All roles except admin outlet can create targets
        return !$user->isAdminOutlet();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, IncomeTarget $incomeTarget): bool
    {
        // Super admin can update any target
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin wilayah can update targets for outlets in their wilayah
        if ($user->isAdminWilayah()) {
            return $user->hasOutletAccess($incomeTarget->outlet);
        }

        // Admin area can update targets for outlets in their area
        if ($user->isAdminArea()) {
            return $user->hasOutletAccess($incomeTarget->outlet);
        }

        // Admin outlet cannot update targets (not authorized based on requirements)
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, IncomeTarget $incomeTarget): bool
    {
        // Super admin can delete any target
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin wilayah can delete targets for outlets in their wilayah
        if ($user->isAdminWilayah()) {
            return $user->hasOutletAccess($incomeTarget->outlet);
        }

        // Admin area can delete targets for outlets in their area
        if ($user->isAdminArea()) {
            return $user->hasOutletAccess($incomeTarget->outlet);
        }

        // Admin outlet cannot delete targets (not authorized based on requirements)
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, IncomeTarget $incomeTarget): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, IncomeTarget $incomeTarget): bool
    {
        return false;
    }
}
