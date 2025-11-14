<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ActivityLogService
{
    /**
     * Log aktivitas user
     *
     * @param string $action Aksi yang dilakukan (create, update, delete, login, dll)
     * @param string|null $module Modul tempat aksi terjadi
     * @param string|null $description Deskripsi aksi
     * @param array|null $oldValues Nilai lama sebelum perubahan (untuk update)
     * @param array|null $newValues Nilai baru setelah perubahan (untuk update)
     */
    public function logActivity(string $action, string $module = null, string $description = null, array $oldValues = null, array $newValues = null)
    {
        // Jika tidak ada user yang login, tidak mencatat aktivitas
        if (!Auth::check()) {
            return;
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log error operation with context
     */
    public function logError(string $module, string $operation, string $message, array $context = []): void
    {
        // Log to application error log
        Log::error("{$module} - {$operation} Error: {$message}", array_merge($context, [
            'user_id' => Auth::id(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]));
        
        // Optionally log to activity log as well for audit purposes
        if (Auth::check()) {
            $this->logActivity(
                action: 'error',
                module: $module,
                description: $operation . ' failed: ' . $message
            );
        }
    }

    /**
     * Log pembuatan user
     */
    public function logUserCreated(array $data)
    {
        $this->logActivity(
            action: 'create',
            module: 'user',
            description: 'User created',
            newValues: $data
        );
    }

    /**
     * Log update user
     */
    public function logUserUpdated(array $oldData, array $newData)
    {
        $this->logActivity(
            action: 'update',
            module: 'user',
            description: 'User updated',
            oldValues: $oldData,
            newValues: $newData
        );
    }

    /**
     * Log penghapusan user
     */
    public function logUserDeleted(array $data)
    {
        $this->logActivity(
            action: 'delete',
            module: 'user',
            description: 'User deleted',
            oldValues: $data
        );
    }

    /**
     * Log pembuatan office
     */
    public function logOfficeCreated(array $data)
    {
        $this->logActivity(
            action: 'create',
            module: 'office',
            description: 'Office created',
            newValues: $data
        );
    }

    /**
     * Log update office
     */
    public function logOfficeUpdated(array $oldData, array $newData)
    {
        $this->logActivity(
            action: 'update',
            module: 'office',
            description: 'Office updated',
            oldValues: $oldData,
            newValues: $newData
        );
    }

    /**
     * Log penghapusan office
     */
    public function logOfficeDeleted(array $data)
    {
        $this->logActivity(
            action: 'delete',
            module: 'office',
            description: 'Office deleted',
            oldValues: $data
        );
    }

    /**
     * Log pembuatan outlet
     */
    public function logOutletCreated(array $data)
    {
        $this->logActivity(
            action: 'create',
            module: 'outlet',
            description: 'Outlet created',
            newValues: $data
        );
    }

    /**
     * Log update outlet
     */
    public function logOutletUpdated(array $oldData, array $newData)
    {
        $this->logActivity(
            action: 'update',
            module: 'outlet',
            description: 'Outlet updated',
            oldValues: $oldData,
            newValues: $newData
        );
    }

    /**
     * Log penghapusan outlet
     */
    public function logOutletDeleted(array $data)
    {
        $this->logActivity(
            action: 'delete',
            module: 'outlet',
            description: 'Outlet deleted',
            oldValues: $data
        );
    }

    /**
     * Log pembuatan outlet type
     */
    public function logOutletTypeCreated(array $data)
    {
        $this->logActivity(
            action: 'create',
            module: 'outlet_type',
            description: 'Outlet type created',
            newValues: $data
        );
    }

    /**
     * Log update outlet type
     */
    public function logOutletTypeUpdated(array $oldData, array $newData)
    {
        $this->logActivity(
            action: 'update',
            module: 'outlet_type',
            description: 'Outlet type updated',
            oldValues: $oldData,
            newValues: $newData
        );
    }

    /**
     * Log penghapusan outlet type
     */
    public function logOutletTypeDeleted(array $data)
    {
        $this->logActivity(
            action: 'delete',
            module: 'outlet_type',
            description: 'Outlet type deleted',
            oldValues: $data
        );
    }

    /**
     * Log login user
     */
    public function logUserLogin()
    {
        $this->logActivity(
            action: 'login',
            module: 'auth',
            description: 'User logged in'
        );
    }

    /**
     * Log logout user
     */
    public function logUserLogout()
    {
        $this->logActivity(
            action: 'logout',
            module: 'auth',
            description: 'User logged out'
        );
    }
}