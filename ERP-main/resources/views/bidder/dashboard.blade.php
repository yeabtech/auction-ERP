<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AuctionERP &mdash; Bidder Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family:'Inter',sans-serif; background:#0a0a14; color:#e2e8f0; min-height:100vh; }
        .card-dark { background:#111126; border:1px solid rgba(99,102,241,.15); border-radius:16px; }
    </style>
</head>
<body class="p-4">
    <div class="container">
        <h2 class="fw-800 mb-1">Welcome, {{ auth()->user()->name }}!</h2>
        <p class="text-muted mb-4">Your bidder dashboard</p>

        <div class="row g-3 mb-4">
            @php
                $myBids = auth()->user()->bids()->count();
                $myWon  = auth()->user()->bids()->where('status','won')->count();
                $balance = number_format(auth()->user()->walletBalance(), 2);
                $kyc = auth()->user()->kycStatus();
            @endphp
            <div class="col-md-3"><div class="card-dark p-4 text-center"><div style="font-size:1.8rem;font-weight:800;color:#6366f1;">{{ $myBids }}</div><div class="text-muted small">Total Bids Placed</div></div></div>
            <div class="col-md-3"><div class="card-dark p-4 text-center"><div style="font-size:1.8rem;font-weight:800;color:#10b981;">{{ $myWon }}</div><div class="text-muted small">Lots Won</div></div></div>
            <div class="col-md-3"><div class="card-dark p-4 text-center"><div style="font-size:1.8rem;font-weight:800;color:#f59e0b;">${{ $balance }}</div><div class="text-muted small">Wallet Balance</div></div></div>
            <div class="col-md-3"><div class="card-dark p-4 text-center">
                <div style="font-size:1.1rem;font-weight:700;color:{{ $kyc === 'approved' ? '#10b981' : '#f59e0b' }};">
                    <i class="bi bi-shield-{{ $kyc === 'approved' ? 'check' : 'exclamation' }}"></i> {{ ucfirst($kyc) }}
                </div>
                <div class="text-muted small">KYC Status</div>
            </div></div>
        </div>

        <a href="{{ route('bidder.browse') }}" class="btn" style="background:linear-gradient(135deg,#6366f1,#a855f7);color:#fff;border-radius:10px;padding:.7rem 2rem;font-weight:700;text-decoration:none;">
            <i class="bi bi-hammer me-2"></i>Browse Live Auctions
        </a>
    </div>
</body>
</html>
