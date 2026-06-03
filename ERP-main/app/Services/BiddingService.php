<?php

namespace App\Services;

use App\Models\Bid;
use App\Models\Lot;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BiddingService
{
    /**
     * Place a bid on a lot.
     *
     * @throws \RuntimeException
     */
    public function placeBid(
        Lot $lot,
        User $bidder,
        float $amount,
        bool $isProxy = false,
        ?float $maxProxy = null
    ): Bid {
        return DB::transaction(function () use ($lot, $bidder, $amount, $isProxy, $maxProxy) {
            $this->validateBid($lot, $bidder, $amount);

            // Mark all existing winning bids as outbid
            Bid::where('lot_id', $lot->id)
               ->where('status', 'winning')
               ->update(['status' => 'outbid']);

            $bid = Bid::create([
                'lot_id'           => $lot->id,
                'bidder_id'        => $bidder->id,
                'bid_amount'       => $amount,
                'is_proxy'         => $isProxy,
                'max_proxy_amount' => $maxProxy,
                'status'           => 'winning',
                'created_at'       => now(),
            ]);

            // Run auto-extend if anti-sniping is configured
            $this->maybeExtendAuction($lot);

            // Run proxy bidding for competing proxies
            $this->runProxyBidding($lot, $bid);

            AuditService::log('bid_placed', Bid::class, $bid->id, null, [
                'lot_id' => $lot->id,
                'bidder_id' => $bidder->id,
                'amount' => $amount,
            ]);

            return $bid->fresh();
        });
    }

    private function validateBid(Lot $lot, User $bidder, float $amount): void
    {
        if ($lot->status !== 'active') {
            throw new \RuntimeException('This lot is not currently active for bidding.');
        }

        if (! $lot->auction->isLive()) {
            throw new \RuntimeException('The auction is not currently live.');
        }

        if (! $bidder->isKycApproved()) {
            throw new \RuntimeException('Your KYC verification must be approved before bidding.');
        }

        $currentPrice = $lot->currentPrice();
        $auction = $lot->auction;
        $increment = $auction->bid_increment_type === 'percentage'
            ? $currentPrice * ($auction->bid_increment_value / 100)
            : $auction->bid_increment_value;

        $minimumBid = $currentPrice + $increment;

        if ($amount < $minimumBid) {
            throw new \RuntimeException(
                "Bid must be at least " . number_format($minimumBid, 2) . ". Current price: " . number_format($currentPrice, 2)
            );
        }

        if ($amount < $lot->reserve_price && $amount < $lot->auction->starting_price) {
            throw new \RuntimeException('Bid does not meet the starting price.');
        }

        // Check the bidder isn't bidding on their own lot
        if ($lot->asset->owner_id === $bidder->id) {
            throw new \RuntimeException('You cannot bid on your own asset.');
        }
    }

    private function maybeExtendAuction(Lot $lot): void
    {
        $auction = $lot->auction;
        if (! $auction->auto_extend || $auction->anti_sniping_minutes <= 0) {
            return;
        }

        $cutoff = now()->addMinutes($auction->anti_sniping_minutes);
        if ($auction->end_time <= $cutoff) {
            $auction->end_time = $cutoff;
            $auction->save();
        }
    }

    private function runProxyBidding(Lot $lot, Bid $newBid): void
    {
        // Find the best competing proxy bid (not from the same bidder)
        $competingProxy = Bid::where('lot_id', $lot->id)
            ->where('is_proxy', true)
            ->where('bidder_id', '!=', $newBid->bidder_id)
            ->where('status', 'outbid')
            ->orderByDesc('max_proxy_amount')
            ->first();

        if (! $competingProxy || $competingProxy->max_proxy_amount <= $newBid->bid_amount) {
            return;
        }

        $auction = $lot->auction;
        $increment = $auction->bid_increment_type === 'percentage'
            ? $newBid->bid_amount * ($auction->bid_increment_value / 100)
            : $auction->bid_increment_value;

        $counterAmount = min(
            $newBid->bid_amount + $increment,
            $competingProxy->max_proxy_amount
        );

        if ($counterAmount > $newBid->bid_amount) {
            // Mark new bid as outbid
            $newBid->update(['status' => 'outbid']);

            // Re-activate proxy as winning
            $competingProxy->update(['status' => 'winning', 'bid_amount' => $counterAmount]);
        }
    }
}
