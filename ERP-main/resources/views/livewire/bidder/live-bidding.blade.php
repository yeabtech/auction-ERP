<div>
    <style>
        .lot-header { background:linear-gradient(135deg,#13132b,#1e1b4b); border-bottom:1px solid var(--border); padding:1.5rem 2rem; }
        .card-dark { background:var(--card); border:1px solid var(--border); border-radius:20px; }
        .card-dark .card-header { background:transparent; border-bottom:1px solid var(--border); padding:1rem 1.5rem; font-weight:700; }
        .card-dark .card-body { padding:1.5rem; }

        .price-display { font-size:3rem; font-weight:900; color:#10b981; line-height:1; }
        .price-sub { font-size:.85rem; color:#64748b; margin-top:.4rem; }
        .timer-display { font-size:1.6rem; font-weight:800; color:#f59e0b; font-variant-numeric:tabular-nums; }

        .bid-input-group input { background:rgba(255,255,255,.05); border:1px solid rgba(99,102,241,.3); border-right:none; color:#e2e8f0; font-size:1.2rem; font-weight:700; border-radius:12px 0 0 12px; padding:.85rem 1.25rem; }
        .bid-input-group input:focus { background:rgba(255,255,255,.08); border-color:rgba(99,102,241,.6); color:#e2e8f0; box-shadow:none; }
        .btn-bid { background:linear-gradient(135deg,#6366f1,#a855f7); border:none; color:#fff; font-weight:800; font-size:1rem; border-radius:0 12px 12px 0; padding:.85rem 2rem; transition:opacity .2s; }
        .btn-bid:hover { opacity:.9; color:#fff; }
        .btn-bid:disabled { opacity:.5; }

        .bid-row { display:flex; align-items:center; justify-content:space-between; padding:.75rem 0; border-bottom:1px solid rgba(99,102,241,.06); }
        .bid-row:last-child { border-bottom:none; }
        .bidder-avatar { width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#6366f1,#a855f7); display:flex; align-items:center; justify-content:center; font-size:.75rem; font-weight:700; flex-shrink:0; }
        .bid-winner { background:rgba(16,185,129,.06); border-radius:10px; padding:.75rem 1rem; margin-bottom:.5rem; }
        .badge-winning { background:rgba(16,185,129,.15); color:#10b981; font-size:.72rem; padding:.3rem .7rem; border-radius:20px; }
        .badge-outbid  { background:rgba(239,68,68,.12); color:#f87171; font-size:.72rem; padding:.3rem .7rem; border-radius:20px; }
        .badge-proxy   { background:rgba(99,102,241,.12); color:#818cf8; font-size:.65rem; padding:.2rem .55rem; border-radius:20px; }
        .alert-live { background:rgba(99,102,241,.1); border:1px solid rgba(99,102,241,.25); border-radius:12px; color:#c7d2fe; padding:.85rem 1.25rem; font-size:.88rem; }
    </style>

    <div class="lot-header d-flex align-items-center justify-content-between">
        <div>
            <a href="{{ route('bidder.browse') }}" class="text-decoration-none" style="color:#64748b;font-size:.85rem;"><i class="bi bi-arrow-left me-1"></i> Back to Auctions</a>
            <h4 class="mt-1 mb-0 fw-800" style="color:#fff;">{{ $lot->asset->title ?? 'Unknown Asset' }}</h4>
            <div style="font-size:.8rem;color:#64748b;">Lot #{{ $lot->lot_number }} &bull; {{ $lot->auction->title }}</div>
        </div>
        <div class="text-end">
            <div class="timer-display" id="auctionTimer">--:--:--</div>
            <div style="font-size:.75rem;color:#64748b;">Ends {{ $lot->auction->end_time?->format('d M Y, H:i') }}</div>
        </div>
    </div>

    <div class="container-fluid py-4 px-4">
        <div class="row g-4">

            <!-- Bid Panel -->
            <div class="col-lg-5">
                <div class="card-dark mb-3">
                    <div class="card-header d-flex align-items-center gap-2" style="color:#fff;">
                        <span class="pulse-dot" style="width:8px;height:8px;border-radius:50%;background:#ef4444;display:inline-block;animation:pulse 1.5s infinite;"></span>
                        Live Bidding
                    </div>
                    <div class="card-body">
                        <div class="price-display">${{ number_format($lot->currentPrice(), 2) }}</div>
                        <div class="price-sub">
                            Current Price &bull; Reserve: ${{ number_format($lot->reserve_price, 2) }}
                            @if($lot->currentPrice() >= $lot->reserve_price)
                                <span class="badge" style="background:rgba(16,185,129,.15);color:#10b981;margin-left:.5rem;">✓ Reserve Met</span>
                            @endif
                        </div>

                        <div class="alert-live mt-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Minimum bid increment: <strong>${{ number_format($lot->auction->bid_increment_value, 2) }}</strong>
                        </div>

                        @if($message)
                        <div class="alert alert-{{ $messageType }} border-0 mt-3 rounded-3" style="font-size:.88rem;background:rgba({{ $messageType === 'success' ? '16,185,129' : '239,68,68' }},.12);color:{{ $messageType === 'success' ? '#10b981' : '#f87171' }};border:none;">
                            {{ $message }}
                        </div>
                        @endif

                        @auth
                        <div class="mt-3">
                            <label class="form-label text-muted" style="font-size:.8rem;font-weight:600;">YOUR BID AMOUNT</label>
                            <div class="input-group bid-input-group">
                                <input type="number" wire:model="bidAmount" step="1" min="{{ $lot->currentPrice() + $lot->auction->bid_increment_value }}" placeholder="Enter bid...">
                                <button wire:click="placeBid" wire:loading.attr="disabled" class="btn btn-bid">
                                    <span wire:loading.remove wire:target="placeBid"><i class="bi bi-hammer me-1"></i> Place Bid</span>
                                    <span wire:loading wire:target="placeBid"><i class="bi bi-hourglass-split me-1"></i> Placing...</span>
                                </button>
                            </div>

                            <!-- Proxy Bidding -->
                            <div class="mt-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model.live="useProxy" id="proxyToggle">
                                    <label class="form-check-label" for="proxyToggle" style="font-size:.85rem;color:#94a3b8;">
                                        Enable Auto-Proxy Bidding
                                    </label>
                                </div>
                                @if($useProxy)
                                <div class="mt-2">
                                    <label class="form-label text-muted" style="font-size:.75rem;font-weight:600;">MAX PROXY LIMIT</label>
                                    <input type="number" wire:model="maxProxyAmount" class="form-control" placeholder="Max amount you'd bid automatically..." style="background:rgba(255,255,255,.05);border:1px solid var(--border);color:#e2e8f0;border-radius:10px;">
                                    <div style="font-size:.72rem;color:#64748b;margin-top:.35rem;">The system will auto-bid for you up to this limit.</div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @else
                        <div class="mt-3 text-center">
                            <a href="/login" class="btn btn-bid px-4"><i class="bi bi-box-arrow-in-right me-1"></i> Login to Bid</a>
                        </div>
                        @endauth

                        <!-- Stats mini grid -->
                        <div class="row g-2 mt-3">
                            <div class="col-6">
                                <div style="background:rgba(16,185,129,.06);border:1px solid rgba(16,185,129,.15);border-radius:12px;padding:.85rem;text-align:center;">
                                    <div style="font-size:1.1rem;font-weight:800;color:#10b981;">{{ $bidHistory->count() }}</div>
                                    <div style="font-size:.72rem;color:#64748b;">Total Bids</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div style="background:rgba(99,102,241,.06);border:1px solid rgba(99,102,241,.15);border-radius:12px;padding:.85rem;text-align:center;">
                                    <div style="font-size:1.1rem;font-weight:800;color:#818cf8;">{{ $bidHistory->pluck('bidder_id')->unique()->count() }}</div>
                                    <div style="font-size:.72rem;color:#64748b;">Active Bidders</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bid History -->
            <div class="col-lg-7">
                <div class="card-dark">
                    <div class="card-header d-flex justify-content-between align-items-center" style="color:#fff;">
                        <span><i class="bi bi-activity me-2" style="color:#6366f1;"></i>Bid History</span>
                        <button wire:poll.3000ms="render" class="btn btn-sm" style="background:rgba(99,102,241,.12);color:#818cf8;border:none;border-radius:8px;font-size:.75rem;">
                            <i class="bi bi-arrow-repeat me-1"></i> Auto-refresh 3s
                        </button>
                    </div>
                    <div class="card-body p-0" style="max-height:520px;overflow-y:auto;">
                        @forelse($bidHistory as $i => $bid)
                        <div class="bid-row px-4 {{ $i === 0 && $bid->status === 'winning' ? 'bid-winner' : '' }}">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bidder-avatar">{{ strtoupper(substr($bid->bidder->name, 0, 1)) }}</div>
                                <div>
                                    <div style="font-weight:600;font-size:.88rem;color:#fff;">{{ Str::limit($bid->bidder->name, 18) }}</div>
                                    <div style="font-size:.72rem;color:#64748b;">{{ $bid->created_at->diffForHumans() }}</div>
                                </div>
                                @if($bid->is_proxy)
                                <span class="badge-proxy">Auto-Proxy</span>
                                @endif
                            </div>
                            <div class="text-end">
                                <div style="font-size:1.1rem;font-weight:800;color:{{ $bid->status === 'winning' ? '#10b981' : '#e2e8f0' }};">${{ number_format($bid->bid_amount, 2) }}</div>
                                @if($bid->status === 'winning') <span class="badge-winning">● Winning</span>
                                @elseif($bid->status === 'won') <span class="badge badge-completed rounded-pill" style="font-size:.7rem;">Won</span>
                                @else <span class="badge-outbid">Outbid</span>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5" style="color:#64748b;">
                            <i class="bi bi-hammer" style="font-size:2.5rem;display:block;margin-bottom:.75rem;"></i>
                            Be the first to bid!
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Countdown timer
        (function() {
            const endTime = new Date("{{ $lot->auction->end_time?->toISOString() }}");
            const el = document.getElementById('auctionTimer');
            if (!el) return;
            function update() {
                const diff = endTime - new Date();
                if (diff <= 0) { el.textContent = 'ENDED'; el.style.color = '#ef4444'; return; }
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
