<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutletPerformance extends Model
{
    protected $fillable = [
        'outlet_id',
        'date',
        'income',
        'colly',
        'weight',
        'achievement_rate',
        'target_income',
        'target_colly',
        'performance_score',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'income' => 'decimal:2',
        'colly' => 'integer',
        'weight' => 'decimal:2',
        'achievement_rate' => 'decimal:2',
        'target_income' => 'decimal:2',
        'target_colly' => 'integer',
        'performance_score' => 'decimal:2',
    ];

    /**
     * Get the outlet for this performance record.
     */
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }
}