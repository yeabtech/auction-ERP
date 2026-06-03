<?php

namespace App\Livewire\Seller;

use App\Models\Asset;
use App\Models\Auction;
use App\Models\Lot;
use App\Services\AuditService;
use Livewire\Component;
use Livewire\WithPagination;

class Auctions extends Component
{
    use WithPagination;

    // Auction Form Fields
    public string $title = '';
    public string $description = '';
    public string $category = '';
    public string $type = 'english';
    public string $startTime = '';
    public string $endTime = '';
    public float $startingPrice = 100;
    public float $reservePrice = 500;
    public string $bidIncrementType = 'flat';
    public float $bidIncrementValue = 50;

    // Lot Assignment Fields
    public ?int $selectedAssetId = null;

    public bool $isCreating = false;

    protected $rules = [
        'title' => 'required|string|min:5|max:100',
        'description' => 'required|string|min:10',
        'category' => 'required|string',
        'type' => 'required|string',
        'startTime' => 'required|date|after_or_equal:today',
        'endTime' => 'required|date|after:startTime',
        'startingPrice' => 'required|numeric|min:1',
        'reservePrice' => 'required|numeric|gte:startingPrice',
        'bidIncrementValue' => 'required|numeric|min:1',
        'selectedAssetId' => 'required|exists:assets,id',
    ];

    public function toggleCreate(): void
    {
        $this->isCreating = !$this->isCreating;
        $this->reset([
            'title', 'description', 'category', 'type', 'startTime', 'endTime',
            'startingPrice', 'reservePrice', 'bidIncrementValue', 'selectedAssetId'
        ]);
    }

    public function saveAuction(): void
    {
        $this->validate();

        $auction = Auction::create([
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'type' => $this->type,
            'status' => 'active', // Active immediately for demo
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'starting_price' => $this->startingPrice,
            'reserve_price' => $this->reservePrice,
            'bid_increment_type' => $this->bidIncrementType,
            'bid_increment_value' => $this->bidIncrementValue,
            'auto_extend' => true,
            'anti_sniping_minutes' => 5,
            'created_by' => auth()->id(),
        ]);

        // Auto-create first lot
        $lot = Lot::create([
            'auction_id' => $auction->id,
            'asset_id' => $this->selectedAssetId,
            'lot_number' => 'LOT-' . str_pad(Lot::count() + 1, 3, '0', STR_PAD_LEFT),
            'reserve_price' => $this->reservePrice,
            'status' => 'active',
            'sequence_order' => 1,
        ]);

        AuditService::log('auction_created', Auction::class, $auction->id, null, $auction->toArray());
        AuditService::log('lot_created', Lot::class, $lot->id, null, $lot->toArray());

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Auction & lot created successfully!']);
        $this->toggleCreate();
        $this->resetPage();
    }

    public function render()
    {
        $auctions = Auction::where('created_by', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Assets that are approved, owned by this seller, and not currently assigned to a lot
        $myAssets = Asset::where('owner_id', auth()->id())
            ->where('status', 'approved')
            ->whereNotExists(function ($query) {
                $query->selectRaw(1)
                      ->from('lots')
                      ->whereColumn('lots.asset_id', 'assets.id');
            })
            ->get();

        return view('livewire.seller.auctions', compact('auctions', 'myAssets'))
            ->layout('layouts.seller');
    }
}
