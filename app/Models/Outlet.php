<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Outlet extends Model
{
    protected $fillable = [
        'name',
        'code',
        'office_id',
        'outlet_type_id',
        'description',
        'address',
        'phone',
        'email',
        'pic_name',
        'pic_phone',
        'is_active',
    ];

    /**
     * Get the office for this outlet.
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Get the outlet type for this outlet.
     */
    public function outletType(): BelongsTo
    {
        return $this->belongsTo(OutletType::class);
    }

    /**
     * Get the users associated with this outlet.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'outlet_id');
    }

    /**
     * Get the daily incomes for this outlet.
     */
    public function dailyIncomes(): HasMany
    {
        return $this->hasMany(DailyIncome::class);
    }
}
