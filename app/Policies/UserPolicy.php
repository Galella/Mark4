<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
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
    public function view(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        // Admin wilayah hanya bisa melihat user di wilayahnya
        if ($user->isAdminWilayah()) {
            if ($model->isAdminWilayah() || $model->isAdminArea() || $model->isAdminOutlet()) {
                // Cek apakah user model terkait dengan wilayah yang sama
                if ($model->office) {
                    return $user->hasOfficeAccess($model->office);
                }
                if ($model->outlet) {
                    return $user->hasOutletAccess($model->outlet);
                }
            }
        }
        
        // Admin area hanya bisa melihat user outlet di areanya
        if ($user->isAdminArea() && $model->isAdminOutlet() && $model->outlet) {
            return $user->hasOutletAccess($model->outlet);
        }
        
        return $user->id === $model->id; // User hanya bisa melihat profilnya sendiri
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
    public function update(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        // Tidak boleh mengupdate role yang lebih tinggi atau selevel
        if ($user->role === $model->role && $user->id !== $model->id) {
            return false;
        }
        
        // Tidak boleh mengupdate super admin
        if ($model->isSuperAdmin()) {
            return false;
        }
        
        // Admin wilayah bisa update admin area dan admin outlet di wilayahnya
        if ($user->isAdminWilayah()) {
            if ($model->isAdminArea() && $model->office) {
                return $user->hasOfficeAccess($model->office);
            }
            if ($model->isAdminOutlet() && $model->outlet) {
                return $user->hasOutletAccess($model->outlet);
            }
        }
        
        // Admin area bisa update admin outlet di areanya
        if ($user->isAdminArea() && $model->isAdminOutlet() && $model->outlet) {
            return $user->hasOutletAccess($model->outlet);
        }
        
        return $user->id === $model->id; // User bisa update profilnya sendiri
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        // Tidak boleh menghapus super admin
        if ($model->isSuperAdmin()) {
            return false;
        }
        
        // Admin wilayah bisa hapus admin area dan admin outlet di wilayahnya
        if ($user->isAdminWilayah()) {
            if ($model->isAdminArea() && $model->office) {
                return $user->hasOfficeAccess($model->office);
            }
            if ($model->isAdminOutlet() && $model->outlet) {
                return $user->hasOutletAccess($model->outlet);
            }
        }
        
        // Admin area bisa hapus admin outlet di areanya
        if ($user->isAdminArea() && $model->isAdminOutlet() && $model->outlet) {
            return $user->hasOutletAccess($model->outlet);
        }
        
        return false; // User biasa tidak bisa hapus user lain
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
