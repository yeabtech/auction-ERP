<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-800 text-light mb-0"><i class="bi bi-hammer me-2 text-primary"></i>My Auctions & Lots</h3>
        <button wire:click="toggleCreate" class="btn btn-accent px-4 py-2 fw-semibold">
            @if($isCreating)
                <i class="bi bi-x-circle me-1"></i> Cancel Setup
            @else
                <i class="bi bi-plus-circle me-1"></i> Host New Auction
            @endif
        </button>
    </div>

    @if($isCreating)
        <!-- Auction Creation Form -->
        <div class="card border-0 mb-4" style="background: var(--card-bg); border: 1px solid var(--card-border) !important; border-radius: 16px;">
            <div class="card-header border-0 bg-transparent text-light fw-bold px-4 pt-4">
                🔨 CREATE NEW AUCTION & LOT
            </div>
            <div class="card-body px-4 pb-4">
                <form wire:submit.prevent="saveAuction">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">AUCTION TITLE</label>
                            <input type="text" wire:model="title" class="form-control" placeholder="e.g. June Premium Equipment Sale" style="background: rgba(255,255,255,.05); border: 1px solid rgba(139, 92, 246, 0.2); color: #fff; border-radius: 10px;">
                            @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">CATEGORY</label>
                            <input type="text" wire:model="category" class="form-control" placeholder="e.g. Heavy Machinery" style="background: rgba(255,255,255,.05); border: 1px solid rgba(139, 92, 246, 0.2); color: #fff; border-radius: 10px;">
                            @error('category') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-semibold">AUCTION TYPE</label>
                            <select wire:model="type" class="form-select" style="background: #101026; border: 1px solid rgba(139, 92, 246, 0.2); color: #fff; border-radius: 10px;">
                                <option value="english">English (Ascending)</option>
                                <option value="dutch">Dutch (Descending)</option>
                                <option value="reverse">Reverse</option>
                                <option value="sealed">Sealed</option>
                                <option value="timed">Timed</option>
                            </select>
                            @error('type') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-semibold">START TIME</label>
                            <input type="datetime-local" wire:model="startTime" class="form-control" style="background: rgba(255,255,255,.05); border: 1px solid rgba(139, 92, 246, 0.2); color: #fff; border-radius: 10px;">
                            @error('startTime') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-semibold">END TIME</label>
                            <input type="datetime-local" wire:model="endTime" class="form-control" style="background: rgba(255,255,255,.05); border: 1px solid rgba(139, 92, 246, 0.2); color: #fff; border-radius: 10px;">
                            @error('endTime') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-semibold">STARTING PRICE ($)</label>
                            <input type="number" wire:model="startingPrice" class="form-control" style="background: rgba(255,255,255,.05); border: 1px solid rgba(139, 92, 246, 0.2); color: #fff; border-radius: 10px;">
                            @error('startingPrice') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-semibold">RESERVE PRICE ($)</label>
                            <input type="number" wire:model="reservePrice" class="form-control" style="background: rgba(255,255,255,.05); border: 1px solid rgba(139, 92, 246, 0.2); color: #fff; border-radius: 10px;">
                            @error('reservePrice') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-semibold">INCREMENT TYPE</label>
                            <select wire:model="bidIncrementType" class="form-select" style="background: #101026; border: 1px solid rgba(139, 92, 246, 0.2); color: #fff; border-radius: 10px;">
                                <option value="flat">Flat Increment</option>
                                <option value="percentage">Percentage</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-semibold">INCREMENT VALUE ($ or %)</label>
                            <input type="number" wire:model="bidIncrementValue" class="form-control" style="background: rgba(255,255,255,.05); border: 1px solid rgba(139, 92, 246, 0.2); color: #fff; border-radius: 10px;">
                            @error('bidIncrementValue') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <!-- Lot Item Selector -->
                        <div class="col-12">
                            <label class="form-label text-muted small fw-semibold">SELECT ITEM TO LIST IN THIS AUCTION</label>
                            <select wire:model="selectedAssetId" class="form-select" style="background: #101026; border: 1px solid rgba(139, 92, 246, 0.2); color: #fff; border-radius: 10px;">
                                <option value="">-- Choose registered & approved item --</option>
                                @foreach($myAssets as $asset)
                                    <option value="{{ $asset->id }}">{{ $asset->barcode }} - {{ $asset->title }} (Valued at: ${{ number_format($asset->valuation_amount) }})</option>
                                @endforeach
                            </select>
                            @error('selectedAssetId') <span class="text-danger small">{{ $message }}</span> @enderror
                            <div class="text-muted small mt-1">If your item isn't listed, ensure it has been registered in your inventory first.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label text-muted small fw-semibold">AUCTION TERMS & CONDITIONS</label>
                            <textarea wire:model="description" class="form-control" rows="3" placeholder="Define terms, bidding schedule, rules..." style="background: rgba(255,255,255,.05); border: 1px solid rgba(139, 92, 246, 0.2); color: #fff; border-radius: 10px;"></textarea>
                            @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-accent px-4 fw-semibold">Launch Auction</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Auctions List -->
    <div class="card border-0" style="background: var(--card-bg); border: 1px solid var(--card-border) !important; border-radius: 16px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
                <thead>
                    <tr style="border-bottom: 2px solid rgba(139, 92, 246, 0.2);">
                        <th class="px-4 py-3 text-muted small text-uppercase">Auction Info</th>
                        <th class="py-3 text-muted small text-uppercase">Type</th>
                        <th class="py-3 text-muted small text-uppercase">Duration</th>
                        <th class="py-3 text-muted small text-uppercase">Reserve</th>
                        <th class="py-3 text-muted small text-uppercase">Status</th>
                        <th class="px-4 py-3 text-muted small text-uppercase text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auctions as $auc)
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                            <td class="px-4">
                                <div class="fw-bold text-light">{{ $auc->title }}</div>
                                <div class="text-muted small">Category: {{ $auc->category }} &bull; {{ $auc->lots->count() }} lot(s) listed</div>
                            </td>
                            <td><span class="badge" style="background: rgba(139, 92, 246, 0.15); color: #c084fc;">{{ strtoupper($auc->type) }}</span></td>
                            <td class="small">
                                <div><strong class="text-light">Start:</strong> {{ \Carbon\Carbon::parse($auc->start_time)->format('d M, H:i') }}</div>
                                <div><strong class="text-light">End:</strong> {{ \Carbon\Carbon::parse($auc->end_time)->format('d M, H:i') }}</div>
                            </td>
                            <td class="fw-bold text-light">${{ number_format($auc->reserve_price, 2) }}</td>
                            <td>
                                @if($auc->status === 'active')
                                    <span class="badge text-bg-success text-uppercase">Active</span>
                                @elseif($auc->status === 'paused')
                                    <span class="badge text-bg-warning text-uppercase">Paused</span>
                                @else
                                    <span class="badge text-bg-secondary text-uppercase">{{ $auc->status }}</span>
                                @endif
                            </td>
                            <td class="px-4 text-end">
                                @if($auc->lots()->first())
                                    <a href="{{ route('bidder.lot', $auc->lots()->first()) }}" class="btn btn-sm btn-outline-primary py-1.5 px-3" style="border-color: rgba(139,92,246,0.4); color: #c084fc;">
                                        <i class="bi bi-eye-fill me-1"></i> View Live Bids
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-hammer" style="font-size: 2.5rem; display: block; margin-bottom: 0.75rem;"></i>
                                No auctions hosted by you.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $auctions->links() }}
    </div>
</div>
