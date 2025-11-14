<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'office_id',
        'outlet_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the office for this user (for admin wilayah/area).
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Get the outlet for this user (for admin outlet).
     */
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is admin wilayah.
     */
    public function isAdminWilayah(): bool
    {
        return $this->role === 'admin_wilayah';
    }

    /**
     * Check if user is admin area.
     */
    public function isAdminArea(): bool
    {
        return $this->role === 'admin_area';
    }

    /**
     * Check if user is admin outlet.
     */
    public function isAdminOutlet(): bool
    {
        return $this->role === 'admin_outlet';
    }

    /**
     * Check if user has access to a specific office.
     */
    public function hasOfficeAccess(Office $office): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isAdminWilayah()) {
            // Admin wilayah hanya bisa mengakses kantor di wilayahnya
            return $this->office_id === $office->id || $this->office->children()->where('id', $office->id)->exists();
        }

        if ($this->isAdminArea()) {
            // Admin area hanya bisa mengakses kantor area dan outlet di areanya
            return $this->office_id === $office->id;
        }

        return false;
    }

    /**
     * Check if user has access to a specific outlet.
     */
    public function hasOutletAccess(Outlet $outlet): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isAdminWilayah()) {
            // Admin wilayah bisa mengakses outlet di wilayahnya
            return $this->office->children()
                ->whereHas('outlets', function ($query) use ($outlet) {
                    $query->where('id', $outlet->id);
                })
                ->exists();
        }

        if ($this->isAdminArea()) {
            // Admin area bisa mengakses outlet di areanya
            return $this->office_id === $outlet->office_id;
        }

        if ($this->isAdminOutlet()) {
            // Admin outlet hanya bisa mengakses outletnya sendiri
            return $this->outlet_id === $outlet->id;
        }

        return false;
    }

    /**
     * Get the daily incomes recorded by this user.
     */
    public function dailyIncomes()
    {
        return $this->hasMany(DailyIncome::class);
    }
}
