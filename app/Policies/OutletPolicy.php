<?php

namespace App\Policies;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OutletPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All roles except admin outlet can view outlets
        return !$user->isAdminOutlet();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Outlet $outlet): bool
    {
        // All roles except admin outlet can view outlets, but they must have access to that specific outlet
        if ($user->isAdminOutlet()) {
            return false;
        }

        // Super admin can view any outlet
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Other roles can view outlets they have access to
        return $user->hasOutletAccess($outlet);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // All roles except admin outlet can create outlets
        return !$user->isAdminOutlet();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Outlet $outlet): bool
    {
        // All roles except admin outlet can update outlets, but they must have access to that specific outlet
        if ($user->isAdminOutlet()) {
            return false;
        }

        // Super admin can update any outlet
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Other roles can update outlets they have access to
        return $user->hasOutletAccess($outlet);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Outlet $outlet): bool
    {
        // All roles except admin outlet can delete outlets, but they must have access to that specific outlet
        if ($user->isAdminOutlet()) {
            return false;
        }

        // Super admin can delete any outlet
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Other roles can delete outlets they have access to
        return $user->hasOutletAccess($outlet);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Outlet $outlet): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Outlet $outlet): bool
    {
        return false;
    }
}