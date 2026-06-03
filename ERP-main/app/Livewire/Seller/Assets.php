<?php

namespace App\Livewire\Seller;

use App\Models\Asset;
use App\Services\AuditService;
use Livewire\Component;
use Livewire\WithPagination;

class Assets extends Component
{
    use WithPagination;

    public string $title = '';
    public string $description = '';
    public string $category = 'Vehicle';
    public float $valuationAmount = 0;
    public string $warehouseLocation = '';

    public bool $isCreating = false;

    protected $rules = [
        'title' => 'required|string|min:3|max:100',
        'description' => 'required|string|min:10',
        'category' => 'required|string',
        'valuationAmount' => 'required|numeric|min:1',
        'warehouseLocation' => 'required|string',
    ];

    public function toggleCreate(): void
    {
        $this->isCreating = !$this->isCreating;
        $this->reset(['title', 'description', 'category', 'valuationAmount', 'warehouseLocation']);
    }

    public function saveAsset(): void
    {
        $this->validate();

        $assetCount = Asset::count();
        $barcode = 'AST-' . str_pad($assetCount + 1, 4, '0', STR_PAD_LEFT);

        $asset = Asset::create([
            'owner_id' => auth()->id(),
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'barcode' => $barcode,
            'valuation_amount' => $this->valuationAmount,
            'warehouse_location' => $this->warehouseLocation,
            'status' => 'approved', // Auto-approved for demo purposes
        ]);

        AuditService::log('asset_registered', Asset::class, $asset->id, null, $asset->toArray());

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Asset registered successfully!']);
        $this->toggleCreate();
        $this->resetPage();
    }

    public function render()
    {
        $assets = Asset::where('owner_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.seller.assets', compact('assets'))
            ->layout('layouts.seller');
    }
}
