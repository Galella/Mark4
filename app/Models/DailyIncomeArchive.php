<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyIncomeArchive extends Model
{
    protected $table = 'daily_income_archives';

    protected $fillable = [
        'date',
        'moda_id',
        'colly',
        'weight',
        'income',
        'outlet_id',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
        'colly' => 'integer',
        'weight' => 'decimal:2',
        'income' => 'decimal:2',
    ];

    /**
     * Get the outlet for this daily income.
     */
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    /**
     * Get the user who recorded this daily income.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the moda for this daily income.
     */
    public function moda(): BelongsTo
    {
        return $this->belongsTo(Moda::class);
    }
}