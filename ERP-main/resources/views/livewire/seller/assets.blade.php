<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-800 text-light mb-0"><i class="bi bi-box-seam-fill me-2 text-primary"></i>My Asset Inventory</h3>
        <button wire:click="toggleCreate" class="btn btn-accent px-4 py-2 fw-semibold">
            @if($isCreating)
                <i class="bi bi-x-circle me-1"></i> Cancel Registry
            @else
                <i class="bi bi-plus-circle me-1"></i> Register New Asset
            @endif
        </button>
    </div>

    @if($isCreating)
        <!-- Registration Form -->
        <div class="card border-0 mb-4" style="background: var(--card-bg); border: 1px solid var(--card-border) !important; border-radius: 16px;">
            <div class="card-header border-0 bg-transparent text-light fw-bold px-4 pt-4">
                📋 REGISTER NEW ITEM
            </div>
            <div class="card-body px-4 pb-4">
                <form wire:submit.prevent="saveAsset">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">ASSET TITLE</label>
                            <input type="text" wire:model="title" class="form-control" placeholder="e.g. 2023 Generator Set" style="background: rgba(255,255,255,.05); border: 1px solid rgba(139, 92, 246, 0.2); color: #fff; border-radius: 10px;">
                            @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">CATEGORY</label>
                            <select wire:model="category" class="form-select" style="background: #101026; border: 1px solid rgba(139, 92, 246, 0.2); color: #fff; border-radius: 10px;">
                                <option value="Vehicle">Vehicle</option>
                                <option value="Property">Property</option>
                                <option value="Equipment">Equipment</option>
                                <option value="Electronics">Electronics</option>
                                <option value="Other">Other</option>
                            </select>
                            @error('category') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">VALUATION AMOUNT ($)</label>
                            <input type="number" wire:model="valuationAmount" class="form-control" placeholder="e.g. 15000" style="background: rgba(255,255,255,.05); border: 1px solid rgba(139, 92, 246, 0.2); color: #fff; border-radius: 10px;">
                            @error('valuationAmount') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">WAREHOUSE / STORAGE LOCATION</label>
                            <input type="text" wire:model="warehouseLocation" class="form-control" placeholder="e.g. Warehouse B, Shelf 4" style="background: rgba(255,255,255,.05); border: 1px solid rgba(139, 92, 246, 0.2); color: #fff; border-radius: 10px;">
                            @error('warehouseLocation') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small fw-semibold">ITEM DESCRIPTION</label>
                            <textarea wire:model="description" class="form-control" rows="3" placeholder="Provide details, state, features..." style="background: rgba(255,255,255,.05); border: 1px solid rgba(139, 92, 246, 0.2); color: #fff; border-radius: 10px;"></textarea>
                            @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-accent px-4 fw-semibold">Submit Registry</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Inventory Table -->
    <div class="card border-0" style="background: var(--card-bg); border: 1px solid var(--card-border) !important; border-radius: 16px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
                <thead>
                    <tr style="border-bottom: 2px solid rgba(139, 92, 246, 0.2);">
                        <th class="px-4 py-3 text-muted small text-uppercase">Barcode</th>
                        <th class="py-3 text-muted small text-uppercase">Title</th>
                        <th class="py-3 text-muted small text-uppercase">Category</th>
                        <th class="py-3 text-muted small text-uppercase">Valuation</th>
                        <th class="py-3 text-muted small text-uppercase">Location</th>
                        <th class="py-3 text-muted small text-uppercase">Status</th>
                        <th class="px-4 py-3 text-muted small text-uppercase text-end">Registered</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                            <td class="px-4 fw-bold text-accent" style="color: #a78bfa;">{{ $asset->barcode }}</td>
                            <td>
                                <div class="fw-semibold text-light">{{ $asset->title }}</div>
                                <div class="text-muted small">{{ Str::limit($asset->description, 60) }}</div>
                            </td>
                            <td>{{ $asset->category }}</td>
                            <td class="fw-bold text-success">${{ number_format($asset->valuation_amount, 2) }}</td>
                            <td class="text-light">{{ $asset->warehouse_location }}</td>
                            <td>
                                @if($asset->status === 'approved')
                                    <span class="badge text-bg-success px-2 py-1">Approved</span>
                                @elseif($asset->status === 'sold')
                                    <span class="badge text-bg-primary px-2 py-1">Sold</span>
                                @else
                                    <span class="badge text-bg-warning px-2 py-1 text-uppercase">{{ $asset->status }}</span>
                                @endif
                            </td>
                            <td class="px-4 text-end text-muted small">{{ $asset->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-box-seam" style="font-size: 2.5rem; display: block; margin-bottom: 0.75rem;"></i>
                                No assets registered in your warehouse.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="mt-3">
        {{ $assets->links() }}
    </div>
</div>
