<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lot extends Model
{
    protected $fillable = [
        'auction_id', 'asset_id', 'lot_number',
        'reserve_price', 'status', 'sequence_order',
    ];

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function leadingBid(): ?Bid
    {
        return $this->bids()
            ->orderByDesc('bid_amount')
            ->first();
    }

    public function currentPrice(): float
    {
        $leading = $this->leadingBid();
        return $leading ? (float) $leading->bid_amount : (float) $this->auction->starting_price;
    }
}
