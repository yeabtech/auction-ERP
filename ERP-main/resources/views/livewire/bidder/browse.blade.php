<div>
    <style>
        .hero { padding: 4rem 0 2rem; text-align: center; }
        .hero h1 { font-size: clamp(2rem, 5vw, 3.5rem); font-weight: 800; line-height: 1.1; }
        .hero h1 span { background: linear-gradient(135deg, #818cf8, #c084fc, #f472b6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero p { color: #94a3b8; font-size: 1.1rem; max-width: 540px; margin: 1rem auto 2rem; }

        .auction-card {
            background: var(--card); border: 1px solid var(--border); border-radius: 20px; overflow: hidden;
            transition: all .3s; cursor: pointer;
        }
        .auction-card:hover { transform: translateY(-4px); box-shadow: 0 20px 48px rgba(99,102,241,.2); border-color: rgba(99,102,241,.4); }
        .auction-img { height: 180px; background: linear-gradient(135deg, #1e1b4b, #312e81, #1e1b4b); display: flex; align-items: center; justify-content: center; font-size: 3rem; position: relative; }
        .auction-type-badge { position: absolute; top: .75rem; left: .75rem; background: rgba(99,102,241,.2); color: #818cf8; border: 1px solid rgba(99,102,241,.3); font-size: .7rem; font-weight: 700; padding: .25rem .7rem; border-radius: 20px; text-transform: uppercase; letter-spacing: .05em; }
        .auction-live-badge { position: absolute; top: .75rem; right: .75rem; background: rgba(239,68,68,.2); color: #f87171; border: 1px solid rgba(239,68,68,.3); font-size: .7rem; font-weight: 700; padding: .25rem .7rem; border-radius: 20px; display: flex; align-items: center; gap: .3rem; }
        .pulse { width: 6px; height: 6px; border-radius: 50%; background: #f87171; animation: pulse 1.5s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; transform: scale(1) } 50% { opacity: .5; transform: scale(.8) } }
        .auction-body { padding: 1.25rem; }
        .auction-title { font-weight: 700; font-size: .95rem; margin-bottom: .5rem; color: #fff; }
        .auction-meta { font-size: .78rem; color: #64748b; display: flex; align-items: center; gap: .3rem; }
        .auction-price { font-size: 1.3rem; font-weight: 800; color: #10b981; margin: .75rem 0 .25rem; }
        .auction-countdown { font-size: .78rem; color: #f59e0b; }
        .lots-count { font-size: .75rem; color: #94a3b8; }

        .filter-bar { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 1.25rem; margin-bottom: 2rem; }
        .form-control-dark { background: rgba(255,255,255,.04); border: 1px solid var(--border); color: #e2e8f0; border-radius: 10px; }
        .form-control-dark:focus { background: rgba(255,255,255,.06); border-color: rgba(99,102,241,.5); color: #e2e8f0; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
        .form-control-dark::placeholder { color: #4b5563; }
    </style>

    <div class="container">
        <div class="hero">
            <h1>Discover <span>Live Auctions</span></h1>
            <p>Bid on vehicles, property, equipment and more — real-time, secure, enterprise-grade.</p>
        </div>

        <!-- Filters -->
        <div class="filter-bar">
            <div class="row g-2 align-items-center">
                <div class="col-md-5">
                    <input type="text" wire:model.live="search" placeholder="🔍 Search auctions..." class="form-control form-control-dark">
                </div>
                <div class="col-md-3">
                    <select wire:model.live="category" class="form-select form-control-dark">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="type" class="form-select form-control-dark">
                        <option value="">All Types</option>
                        @foreach($types as $t)
                        <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button wire:click="$set('search','')" class="btn form-control-dark w-100" style="color:#64748b;"><i class="bi bi-x-lg"></i></button>
                </div>
            </div>
        </div>

        <!-- Auction Grid -->
        <div class="row g-3 pb-5">
            @forelse($auctions as $auction)
            @php $emoji = match($auction->category) { 'Vehicle'=>'🚗','Property'=>'🏢','Equipment'=>'⚙️','Electronics'=>'💻',default=>'📦' }; @endphp
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('bidder.auction', $auction) }}" class="text-decoration-none">
                    <div class="auction-card">
                        <div class="auction-img">
                            <span>{{ $emoji }}</span>
                            <span class="auction-type-badge">{{ ucfirst($auction->type) }}</span>
                            @if($auction->isLive())
                            <span class="auction-live-badge"><span class="pulse"></span>Live</span>
                            @endif
                        </div>
                        <div class="auction-body">
                            <div class="auction-title">{{ $auction->title }}</div>
                            <div class="auction-meta"><i class="bi bi-collection"></i> {{ $auction->lots->count() }} lots &bull; by {{ $auction->auctioneer->name ?? 'N/A' }}</div>
                            <div class="auction-price">from ${{ number_format($auction->starting_price, 2) }}</div>
                            @if($auction->end_time)
                            <div class="auction-countdown">
                                <i class="bi bi-clock"></i>
                                Ends {{ $auction->end_time->diffForHumans() }}
                            </div>
                            @endif
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="lots-count"><i class="bi bi-tag me-1"></i>{{ $auction->category }}</span>
                                <span class="btn btn-sm btn-accent px-3">Bid Now →</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @empty
            <div class="col-12 text-center py-5" style="color:#64748b;">
                <i class="bi bi-hammer" style="font-size:3rem;display:block;margin-bottom:1rem;"></i>
                No auctions match your search.
            </div>
            @endforelse
        </div>

        {{ $auctions->links() }}
    </div>
</div>
