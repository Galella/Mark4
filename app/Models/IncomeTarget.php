<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IncomeTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'moda_id',
        'target_year',
        'target_month',
        'target_amount',
        'description',
        'assigned_by',
        'assigned_at',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'target_year' => 'integer',
        'target_month' => 'integer',
        'assigned_at' => 'datetime',
    ];

    /**
     * Get the outlet for this target.
     */
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    /**
     * Get the moda for this target.
     */
    public function moda(): BelongsTo
    {
        return $this->belongsTo(Moda::class);
    }

    /**
     * Get the user who assigned this target.
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope to get targets for a specific year and month
     */
    public function scopeForYearMonth($query, int $year, int $month)
    {
        return $query->where('target_year', $year)->where('target_month', $month);
    }

    /**
     * Scope to get targets for a specific outlet
     */
    public function scopeForOutlet($query, int $outletId)
    {
        return $query->where('outlet_id', $outletId);
    }

    /**
     * Scope to get targets for a specific moda
     */
    public function scopeForModa($query, int $modaId)
    {
        return $query->where('moda_id', $modaId);
    }

    /**
     * Scope to get targets for a specific outlet, moda, year and month
     */
    public function scopeForOutletModaYearMonth($query, int $outletId, int $modaId, int $year, int $month)
    {
        return $query->where('outlet_id', $outletId)
                    ->where('moda_id', $modaId)
                    ->where('target_year', $year)
                    ->where('target_month', $month);
    }
}
