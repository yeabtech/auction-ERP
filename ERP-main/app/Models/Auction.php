<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Auction extends Model
{
    protected $fillable = [
        'title', 'description', 'category', 'type', 'status',
        'start_time', 'end_time', 'starting_price', 'reserve_price',
        'bid_increment_type', 'bid_increment_value', 'auto_extend',
        'anti_sniping_minutes', 'created_by', 'auctioneer_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
        'auto_extend' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function auctioneer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auctioneer_id');
    }

    public function lots(): HasMany
    {
        return $this->hasMany(Lot::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isLive(): bool
    {
        return $this->status === 'active'
            && $this->start_time <= now()
            && $this->end_time >= now();
    }
}
