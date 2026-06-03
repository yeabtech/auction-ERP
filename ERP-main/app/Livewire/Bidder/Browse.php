<?php

namespace App\Livewire\Bidder;

use App\Models\Auction;
use Livewire\Component;
use Livewire\WithPagination;

class Browse extends Component
{
    use WithPagination;

    public string $search   = '';
    public string $category = '';
    public string $type     = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $auctions = Auction::with(['lots', 'auctioneer'])
            ->where('status', 'active')
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->category, fn($q) => $q->where('category', $this->category))
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->orderByDesc('end_time')
            ->paginate(9);

        $categories = Auction::distinct()->pluck('category');
        $types      = ['english', 'dutch', 'reverse', 'sealed', 'timed'];

        return view('livewire.bidder.browse', compact('auctions', 'categories', 'types'))
            ->layout('layouts.bidder');
    }
}
