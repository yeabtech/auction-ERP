<?php

namespace App\Livewire\Auctioneer;

use App\Models\Auction;
use App\Models\Lot;
use App\Services\AuditService;
use Livewire\Component;

class AuctionControl extends Component
{
    public Auction $auction;

    public function mount(Auction $auction): void
    {
        $this->auction = $auction;
    }

    public function pauseAuction(): void
    {
        $this->auction->update(['status' => 'paused']);
        AuditService::log('auction_paused', Auction::class, $this->auction->id);
        $this->dispatch('notify', ['type' => 'warning', 'message' => 'Auction paused.']);
    }

    public function resumeAuction(): void
    {
        $this->auction->update(['status' => 'active']);
        AuditService::log('auction_resumed', Auction::class, $this->auction->id);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Auction resumed.']);
    }

    public function cancelAuction(): void
    {
        $this->auction->update(['status' => 'cancelled']);
        AuditService::log('auction_cancelled', Auction::class, $this->auction->id);
        $this->dispatch('notify', ['type' => 'danger', 'message' => 'Auction cancelled.']);
    }

    public function extendAuction(int $minutes = 15): void
    {
        $this->auction->end_time = $this->auction->end_time->addMinutes($minutes);
        $this->auction->save();
        AuditService::log('auction_extended', Auction::class, $this->auction->id, null, ['extended_by_minutes' => $minutes]);
        $this->dispatch('notify', ['type' => 'info', 'message' => "Auction extended by {$minutes} minutes."]);
    }

    public function closeLot(int $lotId): void
    {
        $lot = Lot::findOrFail($lotId);
        $winning = $lot->bids()->where('status', 'winning')->first();

        if ($winning) {
            $winning->update(['status' => 'won']);
            $lot->update(['status' => 'sold']);
        } else {
            $lot->update(['status' => 'unsold']);
        }

        AuditService::log('lot_closed', Lot::class, $lot->id);
        $this->dispatch('notify', ['type' => 'success', 'message' => "Lot #{$lot->lot_number} closed."]);
    }

    public function render()
    {
        $this->auction->refresh();

        $lots = Lot::with(['asset', 'bids.bidder'])
            ->where('auction_id', $this->auction->id)
            ->orderBy('sequence_order')
            ->get();

        return view('livewire.auctioneer.auction-control', compact('lots'))
            ->layout('layouts.auctioneer');
    }
}
