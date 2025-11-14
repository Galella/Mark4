<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Moda extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the daily incomes for this moda.
     */
    public function dailyIncomes(): HasMany
    {
        return $this->hasMany(DailyIncome::class);
    }
}