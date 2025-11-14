<?php

namespace App\Policies;

use App\Models\OutletType;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OutletTypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() ||
               $user->isAdminWilayah() ||
               $user->isAdminArea();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OutletType $outletType): bool
    {
        return $user->isSuperAdmin() ||
               $user->isAdminWilayah() ||
               $user->isAdminArea();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() ||
               $user->isAdminWilayah() ||
               $user->isAdminArea();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OutletType $outletType): bool
    {
        return $user->isSuperAdmin() ||
               $user->isAdminWilayah() ||
               $user->isAdminArea();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OutletType $outletType): bool
    {
        return $user->isSuperAdmin() ||
               $user->isAdminWilayah() ||
               $user->isAdminArea();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OutletType $outletType): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OutletType $outletType): bool
    {
        return false;
    }
}