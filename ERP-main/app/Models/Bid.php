<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bid extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'lot_id', 'bidder_id', 'bid_amount',
        'is_proxy', 'max_proxy_amount', 'status',
    ];

    protected $casts = [
        'is_proxy'       => 'boolean',
        'created_at'     => 'datetime',
    ];

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    public function bidder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bidder_id');
    }
}
