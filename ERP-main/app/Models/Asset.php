<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    protected $fillable = [
        'owner_id',
        'title',
        'description',
        'category',
        'barcode',
        'valuation_amount',
        'warehouse_location',
        'status'
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function lots(): HasMany
    {
        return $this->hasMany(Lot::class);
    }
}
