<?php

namespace App\Livewire\Bidder;

use App\Models\Bid;
use App\Models\Lot;
use App\Services\BiddingService;
use Livewire\Component;

class LiveBidding extends Component
{
    public Lot $lot;
    public float $bidAmount = 0;
    public bool $useProxy = false;
    public float $maxProxyAmount = 0;
    public string $message = '';
    public string $messageType = '';

    public function mount(Lot $lot): void
    {
        $this->lot = $lot;
        $this->bidAmount = $lot->currentPrice() + $lot->auction->bid_increment_value;
    }

    public function placeBid(): void
    {
        $this->validate([
            'bidAmount'      => ['required', 'numeric', 'min:1'],
            'maxProxyAmount' => ['nullable', 'numeric'],
        ]);

        $service = app(BiddingService::class);

        try {
            $service->placeBid(
                $this->lot,
                auth()->user(),
                $this->bidAmount,
                $this->useProxy,
                $this->useProxy ? $this->maxProxyAmount : null,
            );

            $this->message     = '✅ Bid placed successfully! You are the current leader.';
            $this->messageType = 'success';
        } catch (\RuntimeException $e) {
            $this->message     = '❌ ' . $e->getMessage();
            $this->messageType = 'danger';
        }

        $this->lot->refresh();
        $this->bidAmount = $this->lot->currentPrice() + $this->lot->auction->bid_increment_value;
    }

    public function render()
    {
        $this->lot->refresh();

        $bidHistory = Bid::with('bidder')
            ->where('lot_id', $this->lot->id)
            ->orderByDesc('created_at')
            ->take(15)
            ->get();

        return view('livewire.bidder.live-bidding', compact('bidHistory'))
            ->layout('layouts.bidder');
    }
}
