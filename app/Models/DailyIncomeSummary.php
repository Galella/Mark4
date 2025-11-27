<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyIncomeSummary extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'outlet_id',
        'moda_id',
        'user_id',
        'total_colly',
        'total_weight',
        'total_income',
        'record_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_weight' => 'decimal:2',
            'total_income' => 'decimal:2',
            'total_colly' => 'integer',
            'record_count' => 'integer',
        ];
    }

    /**
     * Get the outlet for this summary.
     */
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    /**
     * Get the moda for this summary.
     */
    public function moda(): BelongsTo
    {
        return $this->belongsTo(Moda::class);
    }

    /**
     * Get the user who recorded this summary.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}