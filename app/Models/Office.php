<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Office extends Model
{
    protected $fillable = [
        'name',
        'code',
        'type',
        'parent_id',
        'description',
        'address',
        'phone',
        'email',
        'pic_name',
        'pic_phone',
        'is_active',
    ];

    /**
     * Get the parent office for this office.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'parent_id');
    }

    /**
     * Get the child offices for this office.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Office::class, 'parent_id');
    }

    /**
     * Get the outlets for this office.
     */
    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class);
    }

    /**
     * Get the users associated with this office.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'office_id');
    }
}
