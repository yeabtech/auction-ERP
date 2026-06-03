<!-- Admin Dashboard View -->
<div x-data="{ chartReady: false }" x-init="chartReady = true">

    <!-- KPI Cards Row -->
    <div class="row g-3 mb-4">
        @php
            $kpiCards = [
                ['label'=>'Total Revenue', 'value'=>'$'.number_format($stats['total_revenue'],2), 'icon'=>'bi-currency-dollar', 'color'=>'#6366f1', 'gradient'=>'rgba(99,102,241,.2)', 'change'=>'+12.4%', 'dir'=>'up'],
                ['label'=>'Active Auctions', 'value'=>$stats['active_auctions'], 'icon'=>'bi-hammer', 'color'=>'#10b981', 'gradient'=>'rgba(16,185,129,.2)', 'change'=>'+3 this week', 'dir'=>'up'],
                ['label'=>'Total Bids', 'value'=>number_format($stats['total_bids']), 'icon'=>'bi-bar-chart-fill', 'color'=>'#f59e0b', 'gradient'=>'rgba(245,158,11,.2)', 'change'=>'today', 'dir'=>'up'],
                ['label'=>'Total Users', 'value'=>number_format($stats['total_users']), 'icon'=>'bi-people-fill', 'color'=>'#a855f7', 'gradient'=>'rgba(168,85,247,.2)', 'change'=>'+5 new', 'dir'=>'up'],
                ['label'=>'Highest Bid', 'value'=>'$'.number_format($stats['highest_bid'],2), 'icon'=>'bi-trophy-fill', 'color'=>'#ef4444', 'gradient'=>'rgba(239,68,68,.2)', 'change'=>'all time', 'dir'=>'up'],
                ['label'=>'Pending KYC', 'value'=>$stats['pending_kyc'], 'icon'=>'bi-shield-exclamation', 'color'=>'#f97316', 'gradient'=>'rgba(249,115,22,.2)', 'change'=>'needs review', 'dir'=>'dn'],
                ['label'=>'Pending Payments', 'value'=>$stats['pending_payments'], 'icon'=>'bi-clock-fill', 'color'=>'#06b6d4', 'gradient'=>'rgba(6,182,212,.2)', 'change'=>'awaiting', 'dir'=>'dn'],
                ['label'=>'Completed Auctions', 'value'=>$stats['completed_auctions'], 'icon'=>'bi-check-circle-fill', 'color'=>'#84cc16', 'gradient'=>'rgba(132,204,22,.2)', 'change'=>'total', 'dir'=>'up'],
            ];
        @endphp

        @foreach($kpiCards as $card)
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:{{ $card['gradient'] }}; color:{{ $card['color'] }};">
                    <i class="bi {{ $card['icon'] }}"></i>
                </div>
                <div class="stat-value" style="color:{{ $card['color'] }};">{{ $card['value'] }}</div>
                <div class="stat-label">{{ $card['label'] }}</div>
                <div class="stat-change {{ $card['dir'] }}"><i class="bi bi-arrow-{{ $card['dir'] === 'up' ? 'up' : 'down' }}-right"></i> {{ $card['change'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card-dark h-100">
                <div class="card-header">
                    <span><i class="bi bi-graph-up-arrow me-2" style="color:#6366f1;"></i>Monthly Revenue</span>
                    <span class="badge" style="background:rgba(99,102,241,.15);color:#818cf8;font-size:.75rem;">2026</span>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="80"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card-dark h-100">
                <div class="card-header">
                    <span><i class="bi bi-pie-chart-fill me-2" style="color:#a855f7;"></i>Auction Status</span>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="statusChart" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="row g-3">

        <!-- Recent Bids -->
        <div class="col-lg-6">
            <div class="card-dark">
                <div class="card-header">
                    <span><i class="bi bi-activity me-2" style="color:#f59e0b;"></i>Live Bid Feed</span>
                    <span class="badge badge-active px-2 py-1" style="font-size:.7rem;">● LIVE</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-dark-custom table-hover mb-0">
                        <thead><tr>
                            <th>Bidder</th><th>Lot / Asset</th><th>Amount</th><th>Status</th>
                        </tr></thead>
                        <tbody>
                        @forelse($recentBids as $bid)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#a855f7);display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;">
                                        {{ strtoupper(substr($bid->bidder->name, 0, 1)) }}
                                    </div>
                                    <span>{{ Str::limit($bid->bidder->name, 14) }}</span>
                                </div>
                            </td>
                            <td>{{ Str::limit($bid->lot->asset->title ?? 'N/A', 20) }}</td>
                            <td><strong style="color:#10b981;">${{ number_format($bid->bid_amount, 2) }}</strong></td>
                            <td>
                                @if($bid->status === 'winning')
                                    <span class="badge badge-active rounded-pill px-2">Winning</span>
                                @elseif($bid->status === 'won')
                                    <span class="badge badge-completed rounded-pill px-2">Won</span>
                                @else
                                    <span class="badge badge-cancelled rounded-pill px-2">Outbid</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-4" style="color:#64748b;">No bids yet</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- KYC Queue -->
        <div class="col-lg-6">
            <div class="card-dark">
                <div class="card-header">
                    <span><i class="bi bi-shield-check me-2" style="color:#f97316;"></i>KYC Review Queue</span>
                    @if($pendingKycDocs->count())
                    <span class="badge" style="background:rgba(239,68,68,.15);color:#f87171;">{{ $pendingKycDocs->count() }} pending</span>
                    @endif
                </div>
                <div class="card-body p-0">
                    <table class="table table-dark-custom mb-0">
                        <thead><tr>
                            <th>User</th><th>Type</th><th>Doc #</th><th>Actions</th>
                        </tr></thead>
                        <tbody>
                        @forelse($pendingKycDocs as $doc)
                        <tr wire:key="kyc-{{ $doc->id }}">
                            <td>{{ Str::limit($doc->user->name, 16) }}</td>
                            <td><span class="badge badge-pending px-2">{{ str_replace('_', ' ', ucfirst($doc->document_type)) }}</span></td>
                            <td style="font-size:.8rem;font-family:monospace;">{{ $doc->document_number }}</td>
                            <td>
                                <button wire:click="approveKyc({{ $doc->id }})" class="btn btn-sm" style="background:rgba(16,185,129,.15);color:#10b981;border:none;border-radius:8px;">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                <button wire:click="rejectKyc({{ $doc->id }})" class="btn btn-sm ms-1" style="background:rgba(239,68,68,.15);color:#ef4444;border:none;border-radius:8px;">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-4" style="color:#64748b;">✅ All KYC verified</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Revenue Chart
    const rCtx = document.getElementById('revenueChart');
    if (rCtx) {
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const data   = @json(array_values($monthlyRevenue->toArray()));
        new Chart(rCtx, {
            type: 'line',
            data: {
                labels: months.slice(0, data.length),
                datasets: [{
                    label: 'Commission Revenue ($)',
                    data,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,.08)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#6366f1',
                    pointRadius: 4,
                    fill: true,
                    tension: .4,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { labels: { color: '#94a3b8' }}},
                scales: {
                    x: { grid: { color: 'rgba(99,102,241,.08)' }, ticks: { color: '#64748b' }},
                    y: { grid: { color: 'rgba(99,102,241,.08)' }, ticks: { color: '#64748b', callback: v => '$' + v.toLocaleString() }},
                }
            }
        });
    }

    // Doughnut Status
    const sCtx = document.getElementById('statusChart');
    if (sCtx) {
        new Chart(sCtx, {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Completed', 'Upcoming', 'Cancelled'],
                datasets: [{
                    data: [{{ $stats['active_auctions'] }}, {{ $stats['completed_auctions'] }}, {{ $stats['upcoming_auctions'] }}, 0],
                    backgroundColor: ['#10b981','#6366f1','#f59e0b','#ef4444'],
                    borderWidth: 0,
                    hoverOffset: 6,
                }]
            },
            options: {
                cutout: '72%',
                plugins: { legend: { labels: { color: '#94a3b8', boxWidth: 12 }}}
            }
        });
    }
});
</script>
