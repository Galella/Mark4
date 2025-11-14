<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OutletType extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the outlets for this outlet type.
     */
    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class);
    }
}
