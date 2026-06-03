<div class="container-fluid px-4" wire:poll.3000ms="render">
    <style>
        .control-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .lot-row-active {
            background: rgba(251, 191, 36, 0.03);
            border-left: 4px solid #f59e0b !important;
        }
        .lot-table {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
        }
        .btn-control-amber {
            background: linear-gradient(135deg, #fbbf24, #d97706);
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 10px;
        }
        .btn-control-amber:hover {
            opacity: 0.9;
            color: #fff;
        }
    </style>

    <div class="row">
        <!-- Auction Summary & Global Controls -->
        <div class="col-12">
            <div class="control-card">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <span class="badge text-uppercase mb-2 {{ $auction->status === 'active' ? 'text-bg-success' : ($auction->status === 'paused' ? 'text-bg-warning' : 'text-bg-secondary') }}">
                            {{ $auction->status }}
                        </span>
                        <h2 class="fw-800 mb-1" style="color: #fff;">{{ $auction->title }}</h2>
                        <p class="text-muted mb-0">{{ $auction->description }} &bull; Category: <strong class="text-light">{{ $auction->category }}</strong></p>
                    </div>
                    
                    <!-- Control Actions -->
                    <div class="d-flex flex-wrap gap-2">
                        @if($auction->status === 'active')
                            <button wire:click="pauseAuction" class="btn btn-warning px-4 py-2 fw-semibold">
                                <i class="bi bi-pause-fill me-1"></i> Pause Auction
                            </button>
                        @elseif($auction->status === 'paused')
                            <button wire:click="resumeAuction" class="btn btn-success px-4 py-2 fw-semibold">
                                <i class="bi bi-play-fill me-1"></i> Resume Auction
                            </button>
                        @endif

                        <button wire:click="extendAuction(15)" class="btn btn-outline-warning px-3 py-2 fw-semibold">
                            <i class="bi bi-clock-history me-1"></i> +15 Mins
                        </button>
                        <button wire:click="extendAuction(60)" class="btn btn-outline-warning px-3 py-2 fw-semibold">
                            <i class="bi bi-clock-history me-1"></i> +1 Hour
                        </button>
                        
                        @if($auction->status !== 'cancelled' && $auction->status !== 'completed')
                            <button wire:click="cancelAuction" onclick="confirm('Are you sure you want to CANCEL this auction? This is irreversible.') || event.stopImmediatePropagation()" class="btn btn-danger px-3 py-2 fw-semibold">
                                <i class="bi bi-x-circle me-1"></i> Cancel Auction
                            </button>
                        @endif
                    </div>
                </div>
                
                <hr style="border-color: rgba(255, 255, 255, 0.1);">
                
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="text-muted" style="font-size: 0.75rem; text-transform: uppercase;">Start Time</div>
                        <div class="fw-semibold text-light">{{ $auction->start_time->format('d M Y, H:i:s') }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted" style="font-size: 0.75rem; text-transform: uppercase;">End Time</div>
                        <div class="fw-semibold text-light">{{ $auction->end_time->format('d M Y, H:i:s') }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted" style="font-size: 0.75rem; text-transform: uppercase;">Auto Extend</div>
                        <div class="fw-semibold text-light">{{ $auction->auto_extend ? 'Yes (' . $auction->anti_sniping_minutes . ' mins)' : 'No' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted" style="font-size: 0.75rem; text-transform: uppercase;">Time Left</div>
                        <div class="fw-bold text-warning" id="countdownTimer" style="font-size: 1.1rem;">Calculating...</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Lots Control Table -->
        <div class="col-12">
            <h4 class="fw-700 mb-3" style="color: #fff;"><i class="bi bi-collection me-2 text-warning"></i>Manage Auction Lots</h4>
            
            <div class="lot-table">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
                        <thead>
                            <tr style="border-bottom: 2px solid rgba(217, 119, 6, 0.2);">
                                <th class="px-4 py-3" style="color: #fbbf24; font-size: 0.8rem; text-transform: uppercase;">Order</th>
                                <th class="py-3" style="color: #fbbf24; font-size: 0.8rem; text-transform: uppercase;">Lot Info</th>
                                <th class="py-3" style="color: #fbbf24; font-size: 0.8rem; text-transform: uppercase;">Reserve Price</th>
                                <th class="py-3" style="color: #fbbf24; font-size: 0.8rem; text-transform: uppercase;">Current Bid</th>
                                <th class="py-3" style="color: #fbbf24; font-size: 0.8rem; text-transform: uppercase;">Highest Bidder</th>
                                <th class="py-3" style="color: #fbbf24; font-size: 0.8rem; text-transform: uppercase;">Status</th>
                                <th class="px-4 py-3 text-end" style="color: #fbbf24; font-size: 0.8rem; text-transform: uppercase;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lots as $lot)
                                @php
                                    $winningBid = $lot->bids()->where('status', 'winning')->first();
                                    $isActive = $lot->status === 'active';
                                @endphp
                                <tr class="{{ $isActive ? 'lot-row-active' : '' }}" style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                    <td class="px-4 fw-bold text-muted">#{{ $lot->sequence_order }}</td>
                                    <td>
                                        <div class="fw-bold text-light">{{ $lot->asset->title }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">Number: {{ $lot->lot_number }} &bull; Barcode: {{ $lot->asset->barcode }}</div>
                                    </td>
                                    <td class="text-light">${{ number_format($lot->reserve_price, 2) }}</td>
                                    <td class="fw-bold {{ $winningBid ? 'text-success' : 'text-muted' }}">
                                        ${{ number_format($lot->currentPrice(), 2) }}
                                    </td>
                                    <td>
                                        @if($winningBid)
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 24px; height: 24px; background: rgba(99, 102, 241, 0.15); color: #818cf8; font-size: 0.75rem;">
                                                    {{ strtoupper(substr($winningBid->bidder->name, 0, 1)) }}
                                                </div>
                                                <span class="text-light">{{ $winningBid->bidder->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-muted" style="font-style: italic;">No bids yet</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($lot->status === 'active')
                                            <span class="badge bg-success text-uppercase">Active</span>
                                        @elseif($lot->status === 'sold')
                                            <span class="badge bg-primary text-uppercase">Sold</span>
                                        @elseif($lot->status === 'unsold')
                                            <span class="badge bg-danger text-uppercase">Unsold</span>
                                        @else
                                            <span class="badge bg-secondary text-uppercase">{{ $lot->status }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            @if($lot->status === 'active')
                                                <button wire:click="closeLot({{ $lot->id }})" class="btn btn-sm btn-control-amber py-1.5 px-3">
                                                    <i class="bi bi-lock-fill me-1"></i> Close & Settle Lot
                                                </button>
                                            @endif
                                            <a href="{{ route('bidder.lot', $lot) }}" target="_blank" class="btn btn-sm btn-outline-info py-1.5 px-3">
                                                <i class="bi bi-eye-fill"></i> View Box
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-collection-fill style-icon" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                                        No lots registered for this auction.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const endTime = new Date("{{ $auction->end_time->toISOString() }}");
            const el = document.getElementById('countdownTimer');
            if (!el) return;
            function update() {
                const diff = endTime - new Date();
                if (diff <= 0) { el.textContent = 'AUCTION CLOSED'; el.style.color = '#ef4444'; return; }
                const h = Math.floor(diff / 3600000);
                const m = Math.floor((diff % 3600000) / 60000);
                const s = Math.floor((diff % 60000) / 1000);
                el.textContent = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
                if (diff < 300000) el.style.color = '#ef4444';
            }
            update(); setInterval(update, 1000);
        })();
    </script>
</div>
